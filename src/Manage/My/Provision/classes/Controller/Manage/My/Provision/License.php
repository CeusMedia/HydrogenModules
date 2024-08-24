<?php

use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_My_Provision_License extends Controller
{
	protected HttpRequest $request;
	protected PartitionSession $session;
	protected MessengerResource $messenger;
	protected Logic_User_Provision $logicProvision;
	protected Logic_Authentication $logicAuth;
	protected Logic_Member $logicMember;
	protected Logic_Mail $logicMail;
	protected string $filterPrefix		= 'filter_manage_my_license_';
	protected ?string $userId			= NULL;
	protected array $products			= [];

	/**
	 *	@param		int|string|NULL		$productId
	 *	@param		int|string|NULL		$productLicenseId
	 *	@param		int					$stage
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add( int|string|NULL $productId = NULL, int|string|NULL $productLicenseId = NULL, int $stage = 0 ): void
	{
		$words	= (object) $this->getWords( 'msg' );

		if( count( $this->products ) === 1 )
			$productId	= $this->products[0]->productId;
		if( !$productId )
			$productId	= $this->request->get( 'productId' );
//		if( !$productId )
//			$productId	= $this->session->get( $this->filterPrefix.'productId' );

		if( !$productLicenseId )
			$productLicenseId	= $this->request->get( 'productLicenseId' );

		$logicShopBridge	= new Logic_ShopBridge( $this->env );
		$bridgeId			= $logicShopBridge->getBridgeId( 'Provision' );
		if( !$bridgeId )
			throw new RuntimeException( 'Missing shop bridge "Provision"' );

		$product			= NULL;
		$productLicense		= NULL;
		$productLicenses	= [];
		if( $productId ){
			$product			= $this->logicProvision->getProduct( $productId );
			$productLicenses	= $this->logicProvision->getProductLicenses( $productId, 2 );
			if( count( $productLicenses ) === 1 )
				$productLicenseId	= $productLicenses[0]->productLicenseId;
			if( $productLicenseId ){
				$productLicense		= $this->logicProvision->getProductLicense( $productLicenseId );
				$productLicense->product = $this->logicProvision->getProduct( $productLicense->productId );
			}
		}

		if( $productId && $productLicenseId && $this->request->has( 'save' ) ){
			$password	= $this->request->get( 'password' );
			if( !$this->logicAuth->checkPassword( $this->userId, $password ) ){
				$this->messenger->noteError( $words->errorPasswordInvalid );
				$this->restart( 'add/'.$productId.'/'.$productLicenseId, TRUE );
			}
			$productId			= $this->request->get( 'productId' );
			$productLicenseId	= $this->request->get( 'productLicenseId' );
			$takeFirst			= $this->request->get( 'takeFirst' ) || TRUE;
//			$dbc	= 	$this->env->getDatabase();
			try{
				$productLicense	= $this->logicProvision->getProductLicense( $productLicenseId );	//  check product license
//				$dbc->beginTransaction();
				$userLicenseId	= $this->logicProvision->addUserLicense( $this->userId, $productLicenseId, $takeFirst );
//				$dbc->commit();
				$this->session->remove( 'register.licenseId' );										//  remove noted interest for license
				$this->session->remove( 'register.productId' );										//  remove noted interest for product
				$this->messenger->noteSuccess( 'User license and user license keys have been added.'  );
				$this->restart( 'shop/addArticle/'.$bridgeId.'/'.$productLicenseId );
			}
			catch( Exception $e ){
//				$dbc->rollBack();
				$this->messenger->noteFailure( 'Exception: '.$e->getMessage() );
			}
		}

		$this->addData( 'product', $product );
		$this->addData( 'productId', $productId );
		$this->addData( 'productLicense', $productLicense );
		$this->addData( 'productLicenses', $productLicenses );
		$this->addData( 'productLicenseId', $productLicenseId );
	}

	/**
	 *	@return		never
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function ajaxGetUsers(): never
	{
		$query		= $this->request->get( 'query' );
		$list		= [];
		if( $query ){
			$userIds	= $this->logicMember->getUserIdsByQuery( $query );
//			$userIds	= array_merge( $userIds, $userIds, $userIds, $userIds, $userIds );
			$userIds	= array_slice( $userIds, 0, 10 );
			foreach( $userIds as $userId ){
				$helper		= new View_Helper_Member( $this->env );
				$helper->setMode( "large" );
				$helper->setUser( $userId );
				$user	= $this->logicProvision->getUser( $userId );
				$list[]	= (object) [
					'user'	=> $user,
					'html'	=> $helper->render(),
					'image'	=> $helper->renderImage(),
				];
			}
		}
		$data	= [
			'status'	=> 'success',
			'data'		=> [
				'query'		=> $query,
				'count'		=> count( $list ),
				'list'		=> $list
			]
		];
		print json_encode( $data );
		exit;
	}

	/**
	 *	@param		int|string		$userLicenseId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function deactivate( int|string $userLicenseId ): void
	{
		$userLicense	= $this->logicProvision->getUserLicense( $userLicenseId );
		if( !$userLicense )
			$this->messenger->noteError( 'Diese Lizenz existiert nicht.' );
		else if( $userLicense->userId != $this->userId )
			$this->messenger->noteError( 'Diese Lizenz wurde nicht von Ihnen bestellt.' );
		else if( Model_Provision_User_License::STATUS_ACTIVE != $userLicense->status )
			$this->messenger->noteError( 'Diese Lizenz kann nicht abgebrochen werden, da sie derzeit nicht aktiv ist.' );
		else {
			$this->logicProvision->removeDesertedUserLicense( $userLicenseId );
			$this->messenger->noteSuccess( 'Diese Lizenzbestelltung wurde abgebrochen.' );
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$userLicenseKeyId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function assign( int|string $userLicenseKeyId ): void
	{
		$userLicenseKey		= $this->logicProvision->getUserLicenseKey( $userLicenseKeyId );
		$userLicense		= $this->logicProvision->getUserLicense( $userLicenseKey->userLicenseId );
		$product			= $this->logicProvision->getProduct( $userLicense->productId );

		if( $this->request->has( 'save' ) ){
			$words				= (object) $this->getWords( 'msg' );
			$userId				= $this->request->get( 'userId' );

			if( $userLicense->userId !== $this->userId ){
				$this->messenger->noteError( 'Diese Lizenz gehört einem anderen Benutzer.' );
				$this->restart( 'assign/'.$userLicenseKeyId, TRUE );
			}

			if( $userId ){
				$keys		= $this->logicProvision->getUserLicenseKeysFromUserLicense( $userLicenseKey->userLicenseId );
				$userIds	= [];
				foreach( $keys as $key )
					if( $key->userId )
						$userIds[]	= $key->userId;
				if( in_array( $userId, $userIds ) ){
					$this->messenger->noteError( 'Dieser Benutzer hat bereits einen Schlüssel von dieser Lizenz.' );
					$this->restart( 'assign/'.$userLicenseKeyId, TRUE );
				}
				$this->logicProvision->assignUserKeyOfUserLicenseToUser( $userLicenseKeyId, $userId );
				$this->messenger->noteSuccess( 'Der Lizenzschlüssel wurde dem Benutzer zugewiesen.' );

				$receiver	= $this->logicProvision->getUser( $userId );
				$owner		= $this->logicProvision->getUser( $userLicense->userId );
				$mail		= new Mail_Provision_Customer_Key_Assigned( $this->env, [
					'userLicense'		=> $userLicense,
					'userLicenseKey'	=> $userLicenseKey,
					'keyOwner'			=> $receiver,
					'licenseOwner'		=> $owner,
				] );
				$language	= $this->env->getLanguage()->getLanguage();
				$this->logicMail->handleMail( $mail, $receiver, $language );
				$this->messenger->noteNotice( 'Der Benutzer wurde per Mail über den Lizenzschlüssel informiert.' );
				$this->restart( 'view/'.$userLicense->userLicenseId, TRUE );
			}
		}
		$this->addData( 'userLicenseKey', $userLicenseKey );
		$this->addData( 'userLicense', $userLicense );
		$this->addData( 'product', $product );
	}

	/**
	 *	@param		int|string		$userLicenseId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function cancel( int|string $userLicenseId ): void
	{
		$userLicense	= $this->logicProvision->getUserLicense( $userLicenseId );
		if( !$userLicense )
			$this->messenger->noteError( 'Diese Lizenz existiert nicht.' );
		else if( $userLicense->userId != $this->userId )
			$this->messenger->noteError( 'Diese Lizenz wurde nicht von Ihnen bestellt.' );
		else if( $userLicense->status > 0 )
			$this->messenger->noteError( 'Diese Lizenz kann nicht abgebrochen werden, da sie bereits aktiv ist.' );
		else {
			$this->logicProvision->removeDesertedUserLicense( $userLicenseId );
			$this->messenger->noteSuccess( 'Diese Lizenz wurde abgebrochen.' );
		}
		$this->restart( NULL, TRUE );
	}

	public function filter( $reset = NULL ): void
	{
		$filters	= ['productId'];
		if( $reset ){
			foreach( $filters as $filter )
				$this->session->remove( $this->filterPrefix.$filter );
		}
		foreach( $filters as $filter )
			$this->session->set( $this->filterPrefix.$filter, $this->request->get( $filter ) );
		$this->restart( NULL, TRUE );
	}

	public function index(): void
	{
		$productId		= $this->session->get( $this->filterPrefix.'productId' );
		$userLicenses	= $this->logicProvision->getUserLicensesFromUser( $this->userId, $productId );
//		$userLicenseKeys	= $this->logicProvision->getUserLicenseKeysFromUser( $this->userId );
/*		foreach( $userLicenses as $userLicense ){
			$userLicense->user	= $this->logicProvision->getUserLicenseOwner( $userLicense->userLicenseId );
		}*/

		$notAssigned	= $this->logicProvision->getNotAssignedUserLicenseKeysFromUser( $this->userId, $productId );

		$this->addData( 'userLicenses', $userLicenses );
//		$this->addData( 'userLicenseKeys', $userLicenseKeys );
		$this->addData( 'userLicensesWithNotAssignedKeys', $notAssigned );
		$this->addData( 'filterProductId', $productId );

		$logicShopBridge	= new Logic_ShopBridge( $this->env );
		$bridgeId			= $logicShopBridge->getBridgeId( 'Provision' );

		$cartIsEmpty	= empty( $this->session->get( 'shop_cart_positions' ) ) ;
		foreach( $userLicenses as $userLicense ){
			$userLicense->user	= $this->logicProvision->getUserLicenseOwner( $userLicense->userLicenseId );
			if( $userLicense->status == 0 && $cartIsEmpty )
				$userLicense->bridgeId	= $bridgeId;
		}
	}

/*	public function pay( $userLicenseId ){
		$this->logicProvision->setUserLicenseStatus( $userLicenseId, 1 );
	}
*/
	public function view( int|string $userLicenseId ): void
	{
//		$userLicenseKey		= $this->logicProvisionAccounting->getNotAssignedUserLicenseKeysFromUserLicense( $userLicenseId );
		$userLicense		= $this->logicProvision->getUserLicense( $userLicenseId );
		$userLicense->keys	= $this->logicProvision->getUserLicenseKeys( $userLicenseId );
		foreach( $userLicense->keys as $key )
			if( $key->userId )
				$key->user	= $this->logicProvision->getUser( $key->userId );
		$product			= $this->logicProvision->getProduct( $userLicense->productId );

		$productId		= $this->session->get( $this->filterPrefix.'productId' );
		$this->addData( 'notAssignedKeys', $this->logicProvision->getNotAssignedUserLicenseKeysFromUserLicense( $userLicenseId ) );
		$this->addData( 'filterProductId', $productId );
		$this->addData( 'product', $product );
//		$this->addData( 'userLicenseKey', $userLicenseKey );
		$this->addData( 'userLicense', $userLicense );
//		$this->addData( 'unassignedKeys')

	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicProvision	= Logic_User_Provision::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->logicMember		= Logic_Member::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicMail		= Logic_Mail::getInstance( $this->env );

		if( !$this->logicAuth->isAuthenticated() )
			$this->restart( './?from='.$this->request->get( '__path' ) );

		$this->userId			= $this->logicAuth->getCurrentUserId();

		$this->products	= $this->logicProvision->getProducts( 1 );
		if( count( $this->products ) == 1 ){
			$productId	= $this->products[0]->productId;
			$this->session->set( $this->filterPrefix.'productId', $productId );
		}
		$this->addData( 'currentUserId', $this->userId );
		$this->addData( 'products', $this->products );
		$this->addData( 'filterProductId', $this->session->get( $this->filterPrefix.'productId' ) );
	}
}
