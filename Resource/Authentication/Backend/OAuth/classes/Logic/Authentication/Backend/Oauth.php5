<?php
class Logic_Authentication_Backend_Oauth extends CMF_Hydrogen_Logic{

	protected $config;
	protected $modelUser;
	protected $modelRole;
	protected $moduleConfig;
	protected $providerUri;

	protected function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->moduleConfig	= $this->config->getAll( 'module.resource_authentication_backend_oauth', TRUE );
		$this->providerUri	= $this->moduleConfig->get( 'provider.URI' );
		$this->modelUser	= new Model_User( $this->env );
		$this->modelRole	= new Model_Role( $this->env );
	}

	public function checkPassword( $userId, $password ){
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			return FALSE;
		if( !$this->modelUser->get( $userId ) )
			return FALSE;

		$authorization	= base64_encode( implode( ':', array(
			$this->moduleConfig->get( 'provider.client.ID' ),
			$this->moduleConfig->get( 'provider.client.secret' ),
		) ) );
		$postData		= http_build_query( array(
			'grant_type'	=> 'password',
			'username'		=> $user->username,
			'password'		=> $request->get( 'password' ),
			'scope'			=> $request->get( 'scope' ),
		) );
		$handle	= new Net_CURL();
		$handle->setUrl( $this->providerUri.'/token' );
		$handle->setOption( CURLOPT_POST, TRUE );
		$handle->setOption( CURLOPT_POSTFIELDS, $postData );
		$handle->setOption( CURLOPT_HTTPHEADER, array(
			'Authorization: Basic '.$authorization,
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: '.strlen( $postData ),
		) );
		$response	= $handle->exec();
		$response	= json_decode( $response );
		if( $response && $response->access_token )
			return TRUE;
		return FALSE;
	}

	public function getCurrentRole( $strict = TRUE ){
		$roleId	= $this->getCurrentRoleId( $strict );
		if( $roleId ){
			$role	= $this->modelRole->get( $roleId );
			if( $role )
				return $role;
			if( $strict )
				throw new RuntimeException( 'No valid role identified' );
		}
		return NULL;
	}

	public function getCurrentRoleId( $strict = TRUE ){
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->env->getSession()->get( 'roleId');
	}

	public function getCurrentUser( $strict = TRUE, $withRole = FALSE ){
		$userId	= $this->getCurrentUserId( $strict );
		if( $userId ){
			$user	= $this->modelUser->get( $userId );
			if( $user ){
				if( $withRole )
					$user->role	= $this->modelRole->get( $user->roleId );
				return $user;
			}
		}
		if( $strict )
			throw new RuntimeException( 'No valid user identified' );
		return NULL;
	}

	public function getCurrentUserId( $strict = TRUE ){
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return 0;
		}
		return $this->env->getSession()->get( 'userId' );
	}

	public function isAuthenticated(){
		return $this->env->getSession()->get( 'userId' );
	}

	public function isCurrentUserId( $userId ){
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	@todo		implement if possible
	 */
	protected function noteUserActivity(){
	}

/*	public function setCurrentUser( $userId ){


		$this->env->getSession()->set( 'userId', $userId );
		$this->env->getSession()->set( 'userId', $userId );
	}*/
}
