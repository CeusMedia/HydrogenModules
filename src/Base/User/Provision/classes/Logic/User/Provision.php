<?php

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Logic;

class Logic_User_Provision extends Logic
{
	protected Logic_Authentication $logicAuth;
	protected Logic_Mail $logicMail;

	protected Model_Provision_Product $modelProduct;
	protected Model_Provision_Product_License $modelLicense;
	protected Model_Provision_User_License $modelUserLicense;
	protected Model_Provision_User_License_Key $modelUserKey;
	protected Model_User $modelUser;

	/**
	 *	Activate user license and send mails to user license key users.
	 *	@access		protected
	 *	@param		integer			$userLicenseId		User license ID
	 *	@param		boolean			$sendOwnerMail		Flag: send mail to user license owner about activation
	 *	@param		boolean			$sendUserMails		Flag: send mails to user license keys users about assigment
	 *	@return		boolean
	 */
	public function activateUserLicense( $userLicenseId, bool $sendOwnerMail = TRUE, bool $sendUserMails = TRUE )
	{
		$userLicense	= $this->modelUserLicense->get( $userLicenseId );
		if( !$userLicense )
			throw new RangeException( 'Invalid user license ID.' );
		if( $userLicense->status != Model_Provision_User_License::STATUS_NEW )
			throw new RuntimeException( 'User license cannot be activated.' );

		$duration	= $this->getDurationInSeconds( $userLicense->duration );
 		$result		= $this->modelUserLicense->edit( $userLicenseId, [
			'status'		=> Model_Provision_User_License::STATUS_ACTIVE,
			'modifiedAt'	=> time(),
			'startsAt'		=> time(),
			'endsAt'		=> time() + $duration,
		] );
		if( !$result )
			return FALSE;

		$userLicenseKeys	= $this->modelUserKey->getAll( [
			'productLicenseId'	=> $userLicense->productLicenseId,
			'status'			=> Model_Provision_User_License_Key::STATUS_ASSIGNED,
		] );
		if( $sendOwnerMail )
			$this->sendMailOnActivatedUserLicense( $userLicenseId );
		if( $sendUserMails )
			foreach( $userLicenseKeys as $key )
				$this->sendMailOnAssignUserLicenseKey( $key->userLicenseKeyId );
		return TRUE;
	}

	/**
	 *	Deactivate user license and send mails to user license key users.
	 *	@access		protected
	 *	@param		integer			$userLicenseId		User license ID
	 *	@param		boolean			$sendOwnerMail		Flag: send mail to user license owner about revokation
	 *	@param		boolean			$sendUserMails		Flag: send mails to user license keys users about revokation
	 *	@return		bool
	 */
	public function revokeUserLicense( $userLicenseId, bool $sendOwnerMail = TRUE, bool $sendUserMails = TRUE ): bool
	{
		$userLicense	= $this->modelUserLicense->get( $userLicenseId );
		if( !$userLicense )
			throw new RangeException( 'Invalid user license ID.' );
		if( $userLicense->status !== Model_Provision_User_License::STATUS_ACTIVE )
			throw new RuntimeException( 'User license cannot be revoked.' );

 		$result		= $this->modelUserLicense->edit( $userLicenseId, [
			'status'		=> Model_Provision_User_License::STATUS_REVOKED,
			'modifiedAt'	=> time(),
		] );
		if( $result )
			return FALSE;

		$userLicenseKeys	= $this->modelUserKey->getAll( [
			'productLicenseId'	=> $userLicense->productLicenseId,
			'status'			=> Model_Provision_User_License_Key::STATUS_ASSIGNED,
		] );
		if( $sendOwnerMail )
			$this->sendMailOnRevokedUserLicense( $userLicenseId );
		if( $sendUserMails )
			foreach( $userLicenseKeys as $key )
				$this->sendMailOnRevokeUserLicenseKey( $key->userLicenseKeyId, $key->userId );
		return TRUE;
	}

/*	public function expireUserLicense( $userLicenseId ): bool
	{
		$userLicense	= $this->modelUserLicense->get( $userLicenseId );
		if( !$userLicense )
			throw new RangeException( 'Invalid user license ID.' );
		if( $userLicense->status !== Model_Provision_User_License::STATUS_ACTIVE )
			throw new RuntimeException( 'User license cannot be revoked.' );
 		$result		= $this->modelUserLicense->edit( $userLicenseId, array(
			'status'		=> Model_Provision_User_License::STATUS_EXPIRED,
			'modifiedAt'	=> time(),
		) );
		if( $result )
			return FALSE;

		$userLicenseKeys	= $this->modelUserKey->getAll( [
			'productLicenseId'	=> $userLicense->productLicenseId,
			'status'			=> Model_Provision_User_License_Key::STATUS_ASSIGNED,
		] );
		foreach( $userLicenseKeys as $key )
			$this->sendMailOnRevokeUserLicenseKey( $key->userLicenseKeyId, $key->userId );
		return TRUE;
	}*/

	/**
	 *	@todo   		rework, send mails
	 */
	public function addUserLicense( $userId, $productLicenseId, bool $assignFirst = FALSE ): string
	{
		$productLicense	= $this->modelLicense->get( $productLicenseId );
		$data		= [
			'userId'			=> $userId,
			'productLicenseId'	=> $productLicenseId,
			'productId'			=> $productLicense->productId,
			'status'			=> 0,
			'uid'				=> substr( ID::uuid(), -12 ),
			'duration'			=> $productLicense->duration,
			'users'				=> $productLicense->users,
			'price'				=> $productLicense->price,
			'createdAt'			=> time(),
		];
		$userLicenseId	= $this->modelUserLicense->add( $data );
		$userLicense	= $this->modelUserLicense->get( $userLicenseId );
		for( $i=0; $i<$productLicense->users; $i++ ){
			$data		= [
				'userLicenseId'		=> $userLicenseId,
				'userId'			=> ( $assignFirst && $i === 0 ) ? $userId : 0,
				'productLicenseId'	=> $productLicenseId,
				'productId'			=> $productLicense->productId,
				'status'			=> ( $assignFirst && $i === 0 ) ? 1 : 0,
				'uid'				=> $userLicense->uid.'-'.str_pad( ( $i + 1), 4, '0', STR_PAD_LEFT ),
				'createdAt'			=> time(),
			];
			$userLicenseKeyId	= $this->modelUserKey->add( $data );
		}
		return $userLicenseId;
	}

	/**
	 *	@todo		doc
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function setUserOfUserLicenseKey( $userLicenseKeyId, $userId = 0 ): bool
	{
		if( !$this->env->getDatabase()->getOpenTransactions() )										//  only if not database transactions are open
			$this->getUserLicenseKey( $userLicenseKeyId );											//  check if user

		$userLicenseKey	= $this->getUserLicenseKey( $userLicenseKeyId );
		$userLicense	= $this->getUserLicense( $userLicenseKey->userLicenseId );
		if( $userId ){
			$this->modelUserKey->edit( $userLicenseKeyId, [
				'userId'	=> $userId,
				'status'	=> Model_Provision_User_License_Key::STATUS_ASSIGNED,
			] );
			if( $userLicense->status == Model_Provision_User_License::STATUS_ACTIVE )
				$this->sendMailOnAssignUserLicenseKey( $userLicenseKeyId );
		}
		else if( $userLicenseKey->userId ){
			$this->modelUserKey->edit( $userLicenseKeyId, [
				'userId'	=> 0,
				'status'	=> Model_Provision_User_License_Key::STATUS_NEW,
			] );
			if( $userLicense->status == Model_Provision_User_License::STATUS_ACTIVE )
				$this->sendMailOnRevokeUserLicenseKey( $userLicenseKeyId, $userLicenseKey->userId );
		}
		return TRUE;
	}

	/**
	 *	@todo   		doc
	 */
	public function countUserLicensesByProductLicense( $productLicenseId ): int
	{
		return $this->modelUserLicense->countByIndex( 'productLicenseId', $productLicenseId );
	}

	/**
	 *	Enables next user license key for a product if
	 *	- user is existing and active
	 *	(- project is existing and active)
	 *	- user has not currently active product key
	 *	- user license is active
	 *	- another user license key for product is prepared
	 *	@access		public
	 *	@param		integer		$userId			User ID
	 *	@param		integer		$productId		Product ID
	 *	@return		NULL|FALSE|integer			ID of next user license key if prepared and active license, FALSE if still having an active key, NULL otherwise
	 *	@todo		check project existence and activity
	 *	@todo		rework
	 */
	public function enableNextUserLicenseKeyForProduct( $userId, $productId )
	{
		$user	= $this->modelUser->get( $userId );
		if( !$user )
		 	throw new RangeException( 'Invalid user ID' );
		if( $user->status < 1 )
			throw new RuntimeException( 'User is not active' );

		$userLicenses	= $this->getUserLicensesFromUser( $userId, $productId );
		foreach( $userLicenses as $userLicense ){
			foreach( $userLicense->userLicenseKeys as $userLicenseKey ){
				if( $userLicenseKey->status == Model_Provision_User_License_Key::STATUS_ASSIGNED ){
					return FALSE;
				}
			}
		}
		$nextUserKeyId	= $this->getNextUserLicenseKeyIdForProduct( $userId, $productId );
		if( $nextUserKeyId ){
			$nextUserKey	= $this->modelUserKey->get( $nextUserKeyId );
			$userLicense	= $this->modelUserLicense->get( $nextUserKey->userLicenseId );
			$duration		= $this->getDurationInSeconds( $userLicense->duration );
			$this->modelUserKey->edit( $nextUserKeyId, [
				'status'	=> Model_Provision_User_License_Key::STATUS_ASSIGNED,
				'startsAt'	=> time(),
				'endsAt'	=> time() + $duration,
			] );
			return $nextUserKeyId;
		}
		return NULL;
	}

	/**
	 *	Reactions on user status changes.
	 *	Should:
	 *	- on deactivate user: revoke user license keys
	 *	- on active deactivated user: start outstanding prepared user license keys
	 *	@access		public
	 *	@todo		implement and document
	 *	@todo		add hook in module config
	 *	@todo		add hook call in module Resource:Users, better implement Logic_UserStatus before
	 */
	public function __onChangeUserStatus( Environment $env, object $context, object $module, array & $payload )
	{
		if( !isset( $payload['status'] ) )
			throw new InvalidArgumentException( 'Missing new status' );
		if( !isset( $payload['userId'] ) )
			throw new InvalidArgumentException( 'Missing user ID' );
		$user	= $this->modelUser->get( $payload['userId'] );
		if( !$user )
			throw new RangeException( 'Invalid user ID' );
		$oldStatus	= $user->status;
		$newStatus	= $payload['status'];
		if( $oldStatus > 0  && $newStatus == -2/*Model_User::STATUS_DEACTIVATED*/ ){
			// @todo revoke user license keys
		}
		if( $oldStatus == -2/*Model_User::STATUS_DEACTIVATED*/  && $newStatus == 1/*Model_User::STATUS_ACTIVE*/ ){
			// @todo start outstanding prepared user license keys
		}
	}

	public function handleOutdatedUserLicenses(): array
	{
		$results	= [];
		$outdatedUserLicenses	= $this->modelUserLicense->getAllByIndices( [
			'status'	=> Model_Provision_User_License::STATUS_ACTIVE,
			'endsAt'	=> '< '.time(),
		], ['endsAt' => 'ASC'] );
		foreach( $outdatedUserLicenses as $outdatedUserLicense )
			$results[]	= $this->handleOutdatedUserLicense( $outdatedUserLicense->userLicenseId );
		return $results;
	}

	/**
	 *	@deprecated  	use handleOutdatedUserLicenses instead
	 *	@todo   		remove, after job has been updated
	 */
	public function handleExpiredKeys(): array
	{
		return $this->handleOutdatedUserLicenses();
		$dbc		= $this->env->getDatabase();
		$language	= $this->env->getLanguage()->getLanguage();
		$logicMail	= Logic_Mail::getInstance( $this->env );
		$list		= [];
		foreach( $this->getOutdatedUserLicenseKeys() as $key ){
			$data	= [
				'key'		=> $key,
				'license'	=> $this->modelUserLicense->get( $key->userLicenseId ),
				'product'	=> $this->modelProduct->get( $key->productId ),
				'user'		=> $this->modelUser->get( $key->userId ),
			];
			$nextUserKeyId	= $this->getNextUserLicenseKeyIdForProduct( $key->userId, $key->productId );
			if( $nextUserKeyId ){
				try{
					$dbc->beginTransaction();
					$data['nextKey']	= $this->modelUserKey->get( $nextUserKeyId );
					$this->modelUserKey->edit( $key->userLicenseKeyId, [
						'status'	=> Model_Provision_User_License_Key::STATUS_EXPIRED,
					] );
					$this->enableNextUserLicenseKeyForProduct( $key->userId, $key->productId );
					$mail	= new Mail_Provision_Customer_Key_Continued( $this->env, $data );
					$logicMail->handleMail( $mail, $data['user'], $language, !TRUE );
					$dbc->commit();
					$list[]	= (object) $data;
				}
				catch( Exception $e ){
					$dbc->rollBack();
					throw new RuntimeException( 'Key renewal failed: '.$e->getMessage() );
				}
			}
			else{
				try{
					$dbc->beginTransaction();
					$this->modelUserKey->edit( $key->userLicenseKeyId, [
						'status'	=> Model_Provision_User_License_Key::STATUS_EXPIRED,
					] );
					$mail	= new Mail_Provision_Customer_Key_Expired( $this->env, $data );
					$logicMail->handleMail( $mail, $data['user'], $language, !TRUE );
					$dbc->commit();
					$list[]	= (object) $data;
				}
				catch( Exception $e ){
					$dbc->rollBack();
					throw new RuntimeException( 'Key expiration failed: '.$e->getMessage() );
				}
			}
		}
		return $list;
	}

	/**
	 *	@todo   		rework
	 */
	public function getDurationInSeconds( $duration )
	{
		$number	= (int) preg_replace( "/^([0-9]+)/", "\\1", $duration );
		$unit	= preg_replace( "/^([0-9]+)([a-z]+)$/", "\\2", $duration );
		$oneDay	= 24 * 60 * 60;

		switch( $unit ){
			case 'd':
				$factor		= $oneDay;
				break;
			case 'w':
				$factor		= 7 * $oneDay;
				break;
			case 'm':
				$factor		= 30 * $oneDay;
				break;
			case 'a':
				$factor		= 365 * $oneDay;
				break;
		}
		return $number * $factor;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$userId			User ID
	 *	@param		integer		$productId		Product ID
	 *	@return		integer						User license key ID
	 *	@throws		RangeException				if given user ID is invalid
	 *	@throws		RuntimeException			if given user is not activated
	 *	@todo		check project existence and activity
	 *	@todo		rework
	 */
	public function getNextUserLicenseKeyIdForProduct( $userId, $productId )
	{
		$user	= $this->modelUser->get( $userId );
		if( !$user )
		 	throw new RangeException( 'Invalid user ID' );
		if( $user->status < 1 )
			throw new RuntimeException( 'User is not active' );

		$userLicenses	= $this->getUserLicensesFromUser( $userId, $productId );
		foreach( $userLicenses as $userLicense ){
			foreach( $userLicense->userLicenseKeys as $userLicenseKey ){
				if( $userLicenseKey->status == Model_Provision_User_License_Key::STATUS_ASSIGNED ){
					return $userLicenseKey->userLicenseKeyId;
				}
			}
		}
		return 0;
	}

	/**
	 *	@todo   		rework
	 */
	public function getOutdatedUserLicenseKeys(): array
	{
		$indices	= [
			'status'	=> Model_Provision_User_License_Key::STATUS_ASSIGNED,
			'endsAt'	=> '< '.time(),
		];
		return $this->modelUserKey->getAllByIndices( $indices );
	}

	public function getProduct( $productId ): object
	{
		$product	= $this->modelProduct->get( $productId );
		if( !$product )
			throw new RangeException( 'Product ID '.$productId.' is not existing' );
		return $product;
	}

	public function getProductLicense( $productLicenseId = 0 )
	{
		$productLicense	= $this->modelLicense->get( $productLicenseId );
		if( !$productLicense )
			throw new RangeException( 'Product license ID '.$productLicenseId.' is not existing' );
		$productLicense->product	= $this->modelProduct->get( $productLicense->productId );
		return $productLicense;
	}

	public function getProductLicenses( $productId, $status = NULL )
	{
		$indices	= ['productId' => $productId];
		if( $status !== NULL )
			$indices['status']	= $status;
		$orders		= ['rank' => 'ASC', 'title' => 'ASC'];
		$productLicenses	= $this->modelLicense->getAll( $indices, $orders );
		foreach( $productLicenses as $nr => $productLicense )
			$productLicense->product	= $this->modelProduct->get( $productLicense->productId );
		return $productLicenses;
	}

	public function getProducts( $status = NULL ): array
	{
		$indices	= [];
		if( $status !== NULL )
			$indices['status']	= $status;
		$orders		= ['rank' => 'ASC', 'title' => 'ASC'];
		return $this->modelProduct->getAll( $indices, $orders );
	}

	public function getUser( $userId ): object
	{
		$user	= $this->modelUser->get( $userId );
		if( !$user )
			throw new OutOfRangeException( 'User ID '.$userId.' is not existing' );
		unset( $user->password );
		return $user;
	}

	public function getUserLicense( $userLicenseId ): object
	{
		$userLicense	= $this->modelUserLicense->get( $userLicenseId );
		if( !$userLicense )
			throw new RangeException( 'User license ID '.$userLicenseId.' is not existing' );
		$clone					= clone( $userLicense );
		$clone->user			= $this->modelUser->get( $userLicense->userId );
		$clone->product			= $this->modelProduct->get( $userLicense->productId );
		$clone->productLicense	= $this->modelLicense->get( $userLicense->productLicenseId );
		return $clone;
	}

	public function getUserLicenseOwner( $userLicenseId ): object
	{
		$userLicense	= $this->getUserLicense( $userLicenseId );
		$user			= $this->getUser( $userLicense->userId );
		if( !$user )
			throw new RangeException( 'Invalid user license ID: '.$userLicenseId );
		return $user;
	}

	public function getUserLicenseKey( $userLicenseKeyId ): object
	{
		$userLicenseKey	= $this->modelUserKey->get( $userLicenseKeyId );
		if( !$userLicenseKey )
			throw new RangeException( 'User license key ID '.$userLicenseKeyId.' is not existing' );
		return $userLicenseKey;
	}

	public function getUserLicenseKeyOwner( $userLicenseKeyId ): object
	{
		$userLicenseKey	= $this->getUserLicenseKey( $userLicenseKeyId );
		$user	= $this->getUser( $userLicenseKey->userId );
		if( !$user )
			throw new RangeException( 'User user licence key ID: '.$userLicenseKeyId );
		return $user;
	}

/*	public function getUserLicenses( $ )
	{
	}*/

	public function getUserLicensesFromUser( $userId, $productId = NULL ): array
	{
		$indices		= ['userId' => $userId];
		if( $productId )
			$indices['productId']	= $productId;
		$userLicenses	= $this->modelUserLicense->getAllByIndices( $indices );
		foreach( $userLicenses as $userLicense ){
			$userLicense->product	= $this->modelProduct->get( $userLicense->productId );
			$userLicense->productLicense	= $this->modelLicense->get( $userLicense->productLicenseId );
			$userLicense->userLicenseKeys	= $this->modelUserKey->getAllByIndex( 'userLicenseId', $userLicense->userLicenseId );
		}
		return $userLicenses;
	}

	public function getNotAssignedUserLicenseKeysFromUser( $userId, $projectId = NULL ): array
	{
		$list		= [];
		$licenses	= $this->getUserLicensesFromUser( $userId, $projectId );
		foreach( $licenses as $userLicense ){
			$userLicense->keys	= $this->getNotAssignedUserLicenseKeysFromUserLicense( $userLicense->userLicenseId );
			if( $userLicense->keys )
				$list[]	= $userLicense;
		}
		return $list;
	}

	public function getNotAssignedUserLicenseKeysFromUserLicense( $userLicenseId )
	{
		$license	= $this->getUserLicense( $userLicenseId );
		$indices	= ['userLicenseId' => $userLicenseId, 'userId' => 0];
		$orders		= ['userLicenseId' => 'ASC'] ;
		$keys		= $this->modelUserKey->getAll( $indices, $orders );
		return $keys;
	}

	public function getUserLicenseKeys( $userLicenseId, bool $assignedOnly = FALSE )
	{
		$indices	= ['userLicenseId' => $userLicenseId];
		if( $assignedOnly )
			$indices['status']	= Model_Provision_User_License_Key::STATUS_ASSIGNED;
		$orders		= ['userLicenseKeyId' => 'ASC'] ;
		$keys		= $this->modelUserKey->getAll( $indices, $orders );
		return $keys;
	}

	public function getUserLicenseKeysFromUser( $userId, bool $activeOnly = NULL, $productId = NULL )
	{
		$indices	= [
			'userId'	=> $userId,
		];
		if( $activeOnly ){
			$indices['status']		= Model_Provision_User_License_Key::STATUS_ASSIGNED;
			$indices['startsAt']	= '< '.time();
			$indices['endsAt']		= '> '.time();
		}
		if( $productId )
			$indices['productId']	= $productId;

		$orders		= ['userLicenseKeyId' => 'ASC'] ;
		$keys		= $this->modelUserKey->getAllByIndices( $indices, $orders );
		foreach( $keys as $key ){
			$key->productLicense	= $this->modelLicense->get( $key->productLicenseId );
			$key->product			= $this->modelProduct->get( $key->productLicense->productId );
			$key->userLicense		= $this->modelUserLicense->get( $key->userLicenseId );
		}
		return $keys;
	}

	public function sendMailOnActivatedUserLicense( $userLicenseId ): bool
	{
		return $this->sendMailOnUserLicenseChange( $userLicenseId, 'Activated' );
	}

	public function sendMailOnDeactivatedUserLicense( $userLicenseId ): bool
	{
		return $this->sendMailOnUserLicenseChange( $userLicenseId, 'Deactivated' );
	}

	public function sendMailOnExpiredUserLicense( $userLicenseId ): bool
	{
		return $this->sendMailOnUserLicenseChange( $userLicenseId, 'Expired' );
	}

	public function sendMailOnReplacedUserLicense( $userLicenseId ): bool
	{
		return $this->sendMailOnUserLicenseChange( $userLicenseId, 'Replaced' );
	}

	public function sendMailOnRevokedUserLicense( $userLicenseId ): bool
	{
		return $this->sendMailOnUserLicenseChange( $userLicenseId, 'Revoked' );
	}

	/**
	 *	Send mail to user of user license key about assigned key or activated license.
	 *	@access		protected
	 *	@param		integer			$userLicenseKeyId		User license key ID
	 *	@return		boolean
	 *	@todo   	user language
	 *	@throws		ReflectionException
	 */
	public function sendMailOnAssignUserLicenseKey( $userLicenseKeyId ): bool
	{
		$userLicenseKey	= $this->getUserLicenseKey( $userLicenseKeyId );
		$userLicense	= $this->getUserLicense( $userLicenseKey->userLicenseId );
		$user	= $this->getUser( $userLicenseKey->userId );
		$mail	= $this->logicMail->createMail( 'Provision_Customer_Key_Assigned', [
			'product'			=> $userLicense->product,
			'productLicense'	=> $userLicense->productLicense,
			'user'				=> $user,
			'userLicense'		=> $userLicense,
		] );
		$language	= $this->env->getLanguage()->getLanguage();
		return $this->logicMail->handleMail( $mail, $user, $language );
	}

	/**
	 *	Send mail to user of user license key about revoked key or deactivated/outdated license.
	 *	@access		protected
	 *	@param		integer			$userLicenseKeyId		User license key ID
	 *	@param		integer			$oldUserId				User ID before revokation
	 *	@return		boolean
	 *	@todo   	user language
	 *	@throws		ReflectionException
	 */
	public function sendMailOnRevokeUserLicenseKey( $userLicenseKeyId, $oldUserId ): bool
	{
		$userLicenseKey	= $this->getUserLicenseKey( $userLicenseKeyId );
		$userLicense	= $this->getUserLicense( $userLicenseKey->userLicenseId );
		$user	= $this->getUser( $oldUserId );
		$mail	= $this->logicMail->createMail( 'Provision_Customer_Key_Revoked', [
			'product'			=> $userLicense->product,
			'productLicense'	=> $userLicense->productLicense,
			'user'				=> $user,
			'userLicense'		=> $userLicense,
		] );
		$language	= $this->env->getLanguage()->getLanguage();
		return $this->logicMail->handleMail( $mail, $user, $language );
	}

	/**
	 *	@todo 		 finish implementation
	 */
	public function setUserLicenseStatus( $userLicenseId, $status )
	{
		$userLicense	= $this->getUserLicense( $userLicenseId );

		$data	= [
			'status'	=> $status,
		];
		$this->modelUserLicense->edit( $userLicenseId, $data );

	//  @todo react to license status within keys:
	//  		- activate -> activate user license keys if no other user license key is running
	//  		- deactivate -> deactivate user license keys

//		if( $status == 2 ){
//			$this->enableNextUserLicenseKeyForProduct( $userLicense->userId, $userLicense->productId );
//		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->logicMail		= Logic_Mail::getInstance( $this->env );
		$this->modelProduct		= new Model_Provision_Product( $this->env );
		$this->modelLicense		= new Model_Provision_Product_License( $this->env );
		$this->modelUserLicense	= new Model_Provision_User_License( $this->env );
		$this->modelUserKey		= new Model_Provision_User_License_Key( $this->env );
		$this->modelUser		= new Model_User( $this->env );
	}

	protected function handleOutdatedUserLicense( $userLicenseId ): object
	{
		$outdatedUserLicense		= $this->getUserLicense( $userLicenseId );
		$outdatedUserLicenseKeys	= $this->getUserLicenseKeys( $userLicenseId, TRUE );			//  get assigned keys of outdated user license
		$nextUserLicense			= $this->modelUserLicense->getByIndices( [						//  get next user license ...
			'productId'			=> $outdatedUserLicense->productId,									//  ... related to product
			'productLicenseId'	=> $outdatedUserLicense->productLicenseId,							//  ... related to product license
			'status'			=> Model_Provision_User_License::STATUS_NEW,									//  ... awaiting activation
		], ['userLicenseId' => 'ASC'] );															//  ... and order be creation date

		//  --  COLLECT USERS TO INFORM ABOUT REVOKATION  --  //
		$usersAssigned	= [];																		//  prepare list of users to inform about assignment
		$usersRevoked	= [];																		//  prepare list of users to inform about revokation
		foreach( $outdatedUserLicenseKeys as $outdatedUserLicenseKey )								//  iterate keys of outdated user license
			$usersRevoked[$outdatedUserLicenseKey->userId]	= $outdatedUserLicenseKey;				//  note user id of outdated user license key

		//  --  FOLLOW-UP LICENSE EXISTS  --  //
		if( $nextUserLicense ){																		//  there is a follow-up user license
			$nextUserLicenseId		= $nextUserLicense->userLicenseId;								//  shortcut ID of next user license
			$nextUserLicenseKeys	= $this->getUserLicenseKeys( $nextUserLicenseId, TRUE );		//  get assigned keys of next user license

			//  --  CARRY AS MANY USERS AS POSSIBLE TO NEXT USER LICENSE  --  //
			if( !count( $nextUserLicenseKeys ) ){													//  no users assigned to license keys
				$users	= min( count( $outdatedUserLicenseKeys ), $nextUserLicense->users );		//  count users to migrate
				for( $i=0; $i<$users; $i++ ){														//  iterate users
					$this->setUserOfUserLicenseKey(													//  assign user ...
						$nextUserLicenseKeys[$i]->userLicenseKeyId,									//  ... next user license key
						$outdatedUserLicenseKeys[$i]->userId										//  ... to user of outdated user license ley
					);
				}
				$nextUserLicenseKeys	= $this->getUserLicenseKeys( $nextUserLicenseId, TRUE );	//  reload assigned keys of next user license
			}

			//  --  REDUCE USERS TO INFORM TO CHANGES ONLY  --  //
			foreach( $nextUserLicenseKeys as $nextUserLicenseKey ){									//  iterate keys of next user license
				if( in_array( $nextUserLicenseKey->userId, $usersRevoked ) )						//  user of outdated key has next key
					unset( $usersRevoked[$nextUserLicenseKey->userId] );							//  remove user from list of revoked users
				else																				//  user did not have a license key before
					$usersAssigned[$outdatedUserLicenseKey->userId]	= $nextUserLicenseKey;			//  note user id of new user with assigment
			}
			$this->activateUserLicense( $userLicenseId, FALSE, FALSE );
			$this->sendMailOnReplacedUserLicense( $userLicenseId );
		}
		else{
			$this->revokeUserLicense( $userLicenseId, FALSE, FALSE );
			$this->sendMailOnExpiredUserLicense( $userLicenseId );
		}

		//  --  SEND MAILS  --  //
		foreach( $usersRevoked as $userId => $key )													//  iterate user to inform about revokation
			$this->sendMailOnRevokeUserLicenseKey( $key->userLicenseKeyId, $userId );				//  send mail to user of outdated user license key
		foreach( $usersAssigned as $userId => $key )												//  iterate user to inform about assignment
			$this->sendMailOnRevokeUserLicenseKey( $key->userLicenseKeyId, $userId );				//  send mail to user of next user license key

		return (object) [
			'outdatedUserLicense'	=> $outdatedUserLicense,
			'nextUserLicense'		=> $nextUserLicense,
			'usersRevoked'			=> $usersRevoked,
			'usersAssigned'			=> $usersAssigned,
		];
	}

	protected function sendMailOnUserLicenseChange( $userLicenseId, $change ): bool
	{
		$changes	= ['Activated', 'Deactivated', 'Expired', 'Replaced', 'Revoked'];
		if( !in_array( $change, $changes ) )
			throw new DomainException( 'Invalid user license customer mail change "'.$change.'"' );
		$userLicense	= $this->getUserLicense( $userLicenseId );
		$user			= $this->getUser( $userLicense->userId );
		$mail	= $this->logicMail->createMail( 'Provision_Customer_License_'.$change, [
			'product'			=> $userLicense->product,
			'productLicense'	=> $userLicense->productLicense,
			'user'				=> $user,
			'userLicense'		=> $userLicense,
		] );
		$language	= $this->env->getLanguage()->getLanguage();
		return $this->logicMail->handleMail( $mail, $user, $language );
	}
}
