<?php

use CeusMedia\Common\Net\CURL as NetCurl;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Authentication_Backend_Oauth extends Logic
{
	protected $modelUser;
	protected $modelRole;
	protected $moduleConfig;
	protected $providerUri;

	public function checkPassword( $userId, string $password ): bool
	{
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
		$handle	= new NetCurl();
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

	public function clearCurrentUser()
	{
		$this->session->remove( 'auth_user_id' );
		$this->session->remove( 'auth_role_id' );
		$this->session->remove( 'auth_status_id' );
		$this->env->getCaptain()->callHook( 'Auth', 'clearCurrentUser', $this );
	}

	public function getCurrentRole( bool $strict = TRUE )
	{
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

	public function getCurrentRoleId( bool $strict = TRUE )
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->session->get( 'auth_role_id');
	}

	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE )
	{
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

	public function getCurrentUserId( bool $strict = TRUE )
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return 0;
		}
		return $this->session->get( 'auth_user_id' );
	}

	public function isAuthenticated(): bool
	{
		if( !$this->isIdentified() )
			return FALSE;
		$authStatus	= (int) $this->session->get( 'auth_status' );
		return $authStatus == Logic_Authentication::STATUS_AUTHENTICATED;
	}

	public function isIdentified(): bool
	{
		return (int) $this->session->get( 'auth_user_id' ) > 0;
	}

	public function isCurrentUserId( $userId ): bool
	{
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	@todo		implement if possible
	 */
	public function noteUserActivity()
	{
	}

	public function setAuthenticatedUser( $user )
	{
		$this->setIdentifiedUser( $user );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	public function setIdentifiedUser( $user )
	{
		$this->session->set( 'auth_user_id', $user->userId );
		$this->session->set( 'auth_role_id', $user->roleId );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_IDENTIFIED );
		$this->session->set( 'auth_account_id', $user->data->accountId );
		$this->session->set( 'auth_token', $user->data->token );
		$this->session->set( 'auth_rights', $user->data->rights );
		$this->session->set( 'auth_backend', 'Rest' );
		return $this;
	}

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->session		= $this->env->getSession();
		$this->moduleConfig	= $this->config->getAll( 'module.resource_authentication_backend_oauth', TRUE );
		$this->providerUri	= $this->moduleConfig->get( 'provider.URI' );
		$this->modelUser	= new Model_User( $this->env );
		$this->modelRole	= new Model_Role( $this->env );
	}
}
