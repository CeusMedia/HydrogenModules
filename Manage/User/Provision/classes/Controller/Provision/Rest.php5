<?php
class Controller_Provision_Rest extends CMF_Hydrogen_Controller{

	protected $logic;

	public function __construct( CMF_Hydrogen_Environment $env, $setupView = TRUE ){
		parent::__construct( $env, FALSE );
	}

	protected function __onInit(){
		$this->logic	= Logic_User_Provision::getInstance( $this->env );
	}

	public function getLicense( $productLicenseId ){
		$this->handleJsonResponse( 'data', $this->logic->getProductLicense( $productLicenseId ) );
	}

	public function getLicenses( $productId ){
		$this->handleJsonResponse( 'data', $this->logic->getProductLicenses( $productId, 2 ) );
	}

	public function getProducts(){
		$products	= $this->logic->getProducts( 1 );
		$this->handleJsonErrorResponse( $products );							//  return with error
	}

	public function handleJsonErrorResponse( $message, $code = 0 ){
		$this->handleJsonResponse( 'error', array(
			'message'	=> $message,
			'code'		=> $code,
		) );
	}

	/**
	 *	@todo 		kriss: finish implementation (exception log)
	 */
	public function handleJsonExceptionResponse( $exception ){
		$this->handleJsonResponse( 'exception', array(
			'message'	=> $exception->getMessage(),
			'code'		=> $exception->getCode(),
			'file'		=> $exception->getFile(),
			'line'		=> $exception->getLine(),
		) );
	}

	public function hasActiveKey( $showExceptions = FALSE ){
//		$productId	= $this->env->getRequest()->get( 'productId' );
//		$userId		= $this->env->getRequest()->get( 'userId' );
		$productId	= $this->env->getRequest()->getFromSource( 'productId', 'POST' );				//  get product ID from POST request, only
		$userId		= $this->env->getRequest()->getFromSource( 'userId', 'POST' );					//  get user ID from POST request, only

		if( (int) $productId < 1 )
			$this->handleJsonErrorResponse( 'No product ID given' );
		if( (int) $userId < 1 )
			$this->handleJsonErrorResponse( 'No user ID given' );
		$data	= array(
			'code'		=> 0,
			'active'	=> null,
			'pending'	=> null,
			'outdated'	=> null,
		);
		try{
			$data['product']	= $this->logic->getProduct( $productId );
			$keys	= $this->logic->getUserLicenseKeysFromUser( $userId, FALSE, $productId );
			foreach( $keys as $key ){
//				if( $key->status == Model_Provision_User_License_Key::STATUS_NEW )
//					$data['pending']	= $this->logic->getUserLicenseKey( $key->userLicenseKeyId );
				if( $key->status == Model_Provision_User_License_Key::STATUS_ASSIGNED )
					$data['active']		= $this->logic->getUserLicenseKey( $key->userLicenseKeyId );
				if( $key->status == Model_Provision_User_License_Key::STATUS_EXPIRED )
					$data['outdated']	= $this->logic->getUserLicenseKey( $key->userLicenseKeyId );
			}
		}
		catch( Exception $e ){
			if( $showExceptions )
				$this->handleJsonExceptionResponse( $e );
			$this->handleJsonErrorResponse( $e->getMessage() );
		}
		if( $data['active'] )
			$data['code']	= 2;
		else if( $data['outdated'] )
			$data['code']	= -1;
		$this->handleJsonResponse( 'data', $data );													//  return license as JSON response
	}

	/**
	 *	Allows to order free single user licenses for new users.
	 *	ATTENTION: Commercial or group licenses are not orderable using this interface.
	 *	ATTENTION: Free single user licenses are orderable only once for one user.
	 */
	public function orderLicense(){
		$request			= $this->env->getRequest();
		$userId				= $request->get( 'userId' );
//		$password			= $request->get( 'password' );
		$productLicenseId	= $request->get( 'productLicenseId' );

		try{
			$model	= new Model_User( $this->env );
			$user	= $model->get( $userId );
			if( !$productLicenseId )
				$this->handleJsonErrorResponse( 'Missing product license ID.' );							//  return with error
			if( !$user )
				$this->handleJsonErrorResponse( 'Missing user ID.' );								//  return with error
//			if( !$user->password != md5( $password ) )
//				$this->handleJsonErrorResponse( 'Invalid password.' );								//  return with error

			$license	= $this->logic->getProductLicense( $productLicenseId );
			if( !$license )
				$this->handleJsonErrorResponse( 'Invalid license ID.' );							//  return with error

			if( (float) $license->price > 0 || $license->users > 1 )											//  is commercial or group license
				$this->handleJsonErrorResponse( 'License cannot be ordered by REST interface.' );	//  do not perform and return error message
			else if( $license->price ){																//  @todo: improve this "one-free-license-per-user" check
				$userLicenses = $this->logic->getUserLicensesFromUser( $userId );					//  get user licenses
				foreach( $userLicenses as $userLicense ){											//  iterate user licenses
					if( !$userLicense->price && (int) $license->users === 1 ){						//  free single license found
						$message	= 'A free license has already been ordered for this user.';		//  set error message
						$this->handleJsonErrorResponse( $message );									//  return with error
					}
				}
			}

			$userLicenseId	= $this->logic->addUserLicense( $userId, $productLicenseId );			//  order license on accounts server

			if( $request->get( 'assign' ) ){														//  user license key is to be assigned to user
				$userKeys	= $this->logic->getUserLicenseKeys( $userLicenseId );					//  get user license keys of user license
				$userLicenseKey		= $userKeys[0];													//  get first user license key
				$userLicenseKeyId	= $userLicenseKey->userLicenseKeyId;							//  get user license key ID
				$productId			= $userLicenseKey->productId;									//  get product ID
				$this->logic->setUserOfUserLicenseKey( $userLicenseKeyId, $userId );				//  assign first user license key to license user
				if( $request->get( 'activate' ) ){													//  user license is to be activated
					$this->logic->enableNextUserLicenseKeyForProduct( $userId, $productId );		//  enable first license key of this license
					$this->logic->setUserLicenseStatus( $userLicenseId, 2 );						//  set user license status to 'active'
				}
			}
			$userLicense	= $this->logic->getUserLicense( $userLicenseId );						//  get ordered user license
			$this->handleJsonResponse( 'data', $userLicense );										//  return license as JSON response
		}
		catch( Exception $e ){																		//  an exception has been caught
			$this->handleJsonExceptionResponse( $e );												//  handle exception and response
		}
	}

	public function test(){
		$data	= array(
			'products' => $this->getProducts(),
			'licenses' => $this->getLicenses( 1 ),
		);
		$this->handleJsonResponse( 'data', $data );
	}
}
