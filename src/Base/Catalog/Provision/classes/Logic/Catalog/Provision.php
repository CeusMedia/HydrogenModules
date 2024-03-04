<?php

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Catalog_Provision extends Logic
{
	protected Logic_Authentication $logicAuth;
	protected Logic_Mail $logicMail;
	protected Model_Provision_Product $modelProduct;
	protected Model_Provision_Product_License $modelLicense;
	protected Model_Provision_User_License $modelUserLicense;
	protected Model_Provision_User_License_Key $modelUserKey;
	protected Model_User $modelUser;

	/**
	 *	@todo   		rework, send mails
	 */
	public function addUserLicense( int|string $userId, int|string $productLicenseId, bool $assignFirst = FALSE ): string
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
	 *	Enables next user license key for a product if
	 *	- user is existing and active
	 *	(- project is existing and active)
	 *	- user has not currently active product key
	 *	- user license is active
	 *	- another user license key for product is prepared
	 *	@access		public
	 *	@param		int|string		$userId			User ID
	 *	@param		int|string		$productId		Product ID
	 *	@return		NULL|FALSE|integer			ID of next user license key if prepared and active license, FALSE if still having an active key, NULL otherwise
	 *	@todo		check project existence and activity
	 *	@todo		rework
	 */
	public function enableNextUserLicenseKeyForProduct( int|string $userId, int|string $productId )
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
	 *	@todo   		doc
	 */
/*	public function countUserLicensesByProductLicense( $productLicenseId ){
		return $this->modelUserLicense->countByIndex( 'productLicenseId', $productLicenseId );
	}*/

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
	 *	@param		int|string		$userId			User ID
	 *	@param		int|string		$productId		Product ID
	 *	@return		integer						User license key ID
	 *	@throws		RangeException				if given user ID is invalid
	 *	@throws		RuntimeException			if given user is not activated
	 *	@todo		check project existence and activity
	 *	@todo		rework
	 */
	public function getNextUserLicenseKeyIdForProduct( int|string $userId, int|string $productId )
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

	public function getProduct( int|string $productId ): object
	{
		$product	= $this->modelProduct->get( $productId );
		if( !$product )
			throw new RangeException( 'Product ID '.$productId.' is not existing' );
		return $product;
	}

	public function getProductLicense( int|string $productLicenseId = 0 ): object
	{
		$productLicense	= $this->modelLicense->get( $productLicenseId );
		if( !$productLicense )
			throw new RangeException( 'Product license ID '.$productLicenseId.' is not existing' );
		$productLicense->product	= $this->modelProduct->get( $productLicense->productId );
		return $productLicense;
	}

	public function getProductLicenses( int|string $productId, $status = NULL ): array
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

	public function getProductUri( int|string $productOrId, bool $absolute = FALSE ): string
	{
		$product	= $productOrId;
		if( is_int( $productOrId ) )
			$product	= $this->getProductLicense( $productOrId );
		if( !is_object( $product ) )
			throw new InvalidArgumentException( 'Given product data is invalid (neither product object nor valid product ID)' );
		$uri	= vsprintf( 'catalog/provision/product/%1$d-%2$s', array(
			$product->productId,
			$this->getUriPart( $product->title ),
		) );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	public function getProductLicenseUri( int|string $productLicenseOrId, bool $absolute = FALSE ): string
	{
		$productLicense	= $productLicenseOrId;
		if( is_int( $productLicenseOrId ) )
			$productLicense	= $this->getProductLicense( $productLicenseOrId );
		if( !is_object( $productLicense ) )
			throw new InvalidArgumentException( 'Given product license data is invalid (neither product license object nor valid product license ID)' );
		$uri	= vsprintf( 'catalog/provision/product/license/%2$d-%3$s', array(
			$productLicense->productId,
			$productLicense->productLicenseId,
			$this->getUriPart( $productLicense->title ),
		) );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	public function getUserLicensesFromUser( int|string $userId, int|string|NULL $productId = NULL ): array
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

	/**
	 *	@todo		 code doc
	 */
	public function getUriPart( string $label, string $delimiter = '_' ): string
	{
		$label	= str_replace( ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'], $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		return preg_replace( "/ +/", $delimiter, $label );
	}

	public function getProducts( $status = NULL ): array
	{
		$indices	= [];
		if( $status !== NULL )
			$indices['status']	= $status;
		$orders		= ['rank' => 'ASC', 'title' => 'ASC'];
		return $this->modelProduct->getAll( $indices, $orders );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicMail		= Logic_Mail::getInstance( $this->env );
		$this->modelProduct		= new Model_Provision_Product( $this->env );
		$this->modelLicense		= new Model_Provision_Product_License( $this->env );
		$this->modelUserLicense	= new Model_Provision_User_License( $this->env );
		$this->modelUserKey		= new Model_Provision_User_License_Key( $this->env );
		$this->modelUser		= new Model_User( $this->env );
	}
}
