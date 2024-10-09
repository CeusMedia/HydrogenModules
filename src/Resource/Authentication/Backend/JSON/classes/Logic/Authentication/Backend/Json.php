<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Authentication_Backend_Json extends Logic implements Logic_Authentication_BackendInterface
{
	protected Dictionary $session;
	protected Resource_Server_Json $client;

	/**
	 *	@param		int|string		$userId			In this case, it is the username
	 *	@param		string			$password
	 *	@return		bool
	 */
	public function checkPassword( int|string $userId, string $password ): bool
	{
		$data	= [
			'filters'	=> [
				'username'	=> $userId,
				'password'	=> md5( $password )
			]
		];
		$result = $this->client->postData( 'user', 'index', NULL, $data );
		return count( $result ) === 1;
	}

	public function clearCurrentUser(): void
	{
		$this->session->remove( 'auth_user_id' );
		$this->session->remove( 'auth_role_id' );
		$this->session->remove( 'auth_status' );
		$this->session->remove( 'auth_account_id' );
		$this->session->remove( 'auth_token' );
		$this->session->remove( 'auth_rights' );
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
			$role	= $this->client->postData( 'role', 'get', [$roleId] );
			if( $role )
				return $role;
			if( $strict )
				throw new RuntimeException( 'No valid role identified' );
		}
		return NULL;
	}

	/**
	 *	@param		bool		$strict
	 *	@return		int|string|NULL
	 */
	public function getCurrentRoleId( bool $strict = TRUE ): int|string|NULL
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->env->getSession()->get( 'auth_role_id');
	}

	/**
	 *	@param		bool		$strict
	 *	@param		bool		$withRole
	 *	@return		object|NULL
	 */
	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE ): ?object
	{
		$userId	= $this->getCurrentUserId( $strict );
		if( $userId ){
			$user	= $this->client->postData( 'user', 'get', [$userId] );
			if( $user ){
				if( $withRole )
					$user->role	= $this->client->postData( 'role', 'get', [$user->roleId] );
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
		return $this->env->getSession()->get( 'auth_user_id' );
	}

	public function isAuthenticated(): bool
	{
		if( !$this->isIdentified() )
			return FALSE;
		$authStatus	= (int) $this->session->get( 'auth_status' );
		return $authStatus === Logic_Authentication::STATUS_AUTHENTICATED;
	}

	public function isIdentified(): bool
	{
		return 0 !== strlen( trim( $this->session->get( 'auth_user_id', '' ) ) );
	}

	public function isCurrentUserId( int|string $userId ): bool
	{
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	public function setAuthenticatedUser( Entity_User $user ): self
	{
		$this->setIdentifiedUser( $user );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	public function setIdentifiedUser( object $user ): self
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

	/**
	 *	@todo		implement if possible
	 */
	public function noteUserActivity(): self
	{
		return $this;
	}

	protected function __onInit(): void
	{
		$client		= $this->env->get( 'jsonServerClient' );
		if( !$client instanceof Resource_Server_Json ){
			if( class_exists( NotSupportedExtension::class ) )
				throw NotSupportedExtension::create()
					->setMessage( 'Sorry, support for Resource_Server_Json only, atm' )
					->setSuggestion( 'You can fix this! This is open source software ;-)' );
			throw new RuntimeException( 'Sorry, support for Resource_Server_Json only, atm - you can fix this: it is open source ;-)' );
		}
		$this->client		= $client;
		$this->session		= $this->env->getSession();
	}
}
