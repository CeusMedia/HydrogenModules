<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Authentication_Backend_Json extends Logic
{
	protected Dictionary $session;

	public function checkPassword( string $username, string $password ): bool
	{
		$data	= [
			'filters'	=> [
				'username'	=> $username,
				'password'	=> md5( $password )
			]
		];
		$result = $this->env->getServer()->postData( 'user', 'index', NULL, $data );
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

	public function getCurrentRole( bool $strict = TRUE ): ?object
	{
		$roleId	= $this->getCurrentRoleId( $strict );
		if( $roleId ){
			$role	= $this->env->getServer()->postData( 'role', 'get', [$roleId] );
			if( $role )
				return $role;
			if( $strict )
				throw new RuntimeException( 'No valid role identified' );
		}
		return NULL;
	}

	public function getCurrentRoleId( bool $strict = TRUE ): ?string
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->env->getSession()->get( 'auth_role_id');
	}

	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE )
	{
		$userId	= $this->getCurrentUserId( $strict );
		if( $userId ){
			$user	= $this->env->getServer()->postData( 'user', 'get', [$userId] );
			if( $user ){
				if( $withRole )
					$user->role	= $this->env->getServer()->postData( 'role', 'get', [$user->roleId] );
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

	public function isCurrentUserId( string $userId ): bool
	{
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	public function setAuthenticatedUser( object $user ): self
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
	protected function noteUserActivity()
	{
	}
}
