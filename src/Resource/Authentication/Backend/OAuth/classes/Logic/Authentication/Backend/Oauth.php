<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\CURL as NetCurl;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Authentication_Backend_Oauth extends Logic implements Logic_Authentication_BackendInterface
{
	protected Dictionary $config;
	protected Dictionary $session;
	protected Dictionary $moduleConfig;
	protected Model_User $modelUser;
	protected Model_Role $modelRole;
	protected string $providerUri;

	/**
	 *	@param		int|string		$userId
	 *	@param		string			$password
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@todos		implement passwort argument, currently using request inputs, should be password + scope arguments
	 */
	public function checkPassword( int|string $userId, string $password ): bool
	{
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			return FALSE;
		/** @var ?Entity_User $user */
		$user	= $this->modelUser->get( $userId );
		if( !$user )
			return FALSE;

		$request		= $this->env->getRequest();
		$authorization	= base64_encode( implode( ':', [
			$this->moduleConfig->get( 'provider.client.ID' ),
			$this->moduleConfig->get( 'provider.client.secret' ),
		] ) );
		$postData		= http_build_query( [
			'grant_type'	=> 'password',
			'username'		=> $user->username,
			'password'		=> $request->get( 'password' ),
			'scope'			=> $request->get( 'scope' ),
		] );
		$handle	= new NetCurl();
		$handle->setUrl( $this->providerUri.'/token' );
		$handle->setOption( CURLOPT_POST, TRUE );
		$handle->setOption( CURLOPT_POSTFIELDS, $postData );
		$handle->setOption( CURLOPT_HTTPHEADER, [
			'Authorization: Basic '.$authorization,
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: '.strlen( $postData ),
		] );
		$response	= $handle->exec();
		$response	= json_decode( $response );
		if( $response && $response->access_token )
			return TRUE;
		return FALSE;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function clearCurrentUser(): void
	{
		$this->session->remove( Logic_Authentication::$sessionKeyAuthUserId );
		$this->session->remove( Logic_Authentication::$sessionKeyAuthRoleId );
		$this->session->remove( Logic_Authentication::$sessionKeyAuthStatus );
		$payload	= [];
		$this->env->getCaptain()->callHook( 'Auth', 'clearCurrentUser', $this, $payload );
	}

	/**
	 *	@param		bool		$strict
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getCurrentRole( bool $strict = TRUE ): ?object
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

	/**
	 * @param		bool	$strict
	 * @return		int|string|NULL
	 */
	public function getCurrentRoleId( bool $strict = TRUE ): int|string|NULL
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->session->get( Logic_Authentication::$sessionKeyAuthRoleId );
	}

	/**
	 *	@param		bool		$strict
	 *	@param		int			$extensions		Flags: extend user entity, default: Logic_User::EXTEND_NOTHING
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getCurrentUser( bool $strict = TRUE, int $extensions = Logic_User::EXTEND_NOTHING ): ?object
	{
		$userId	= $this->getCurrentUserId( $strict );
		if( $userId ){
			/** @var ?Entity_User $user */
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

	/**
	 *	@param		bool		$strict
	 *	@return		int|string|NULL
	 */
	public function getCurrentUserId( bool $strict = TRUE ): int|string|NULL
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return 0;
		}
		return $this->session->get( Logic_Authentication::$sessionKeyAuthUserId );
	}

	public function isAuthenticated(): bool
	{
		if( !$this->isIdentified() )
			return FALSE;
		$authStatus	= (int) $this->session->get( Logic_Authentication::$sessionKeyAuthStatus );
		return $authStatus == Logic_Authentication::STATUS_AUTHENTICATED;
	}

	public function isIdentified(): bool
	{
		return (int) $this->session->get( Logic_Authentication::$sessionKeyAuthUserId ) > 0;
	}

	public function isCurrentUserId( $userId ): bool
	{
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	@todo		implement if possible
	 */
	public function noteUserActivity(): Logic_Authentication_BackendInterface
	{
		return $this;
	}

	public function setAuthenticatedUser( object $user ): self
	{
		$this->setIdentifiedUser( $user );
		$this->session->set( Logic_Authentication::$sessionKeyAuthStatus, Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	public function setIdentifiedUser( object $user ): self
	{
		$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $user->userId );
		$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $user->roleId );
		$this->session->set( Logic_Authentication::$sessionKeyAuthStatus, Logic_Authentication::STATUS_IDENTIFIED );
		$this->session->set( 'auth_account_id', $user->data->accountId );
		$this->session->set( 'auth_token', $user->data->token );
		$this->session->set( 'auth_rights', $user->data->rights );
		$this->session->set( Logic_Authentication::$sessionKeyAuthBackend, 'Rest' );
		return $this;
	}

	/**
	 *	@return		void
	 */
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
