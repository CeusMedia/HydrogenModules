<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Authentication_Backend_Local extends Logic implements Logic_Authentication_BackendInterface
{
	protected Logic_User $logicUser;
	protected Model_User $modelUser;
	protected Model_Role $modelRole;
	protected Dictionary $session;

	/**
	 *	@param		int|string		$userId
	 *	@param		string			$password
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@todo		remove support for old user password
	 */
	public function checkPassword( int|string $userId, string $password ): bool
	{
		$hasUsersModule		= $this->env->getModules()->has( 'Resource_Users' );
		if( $this->env->getPhp()->version->isAtLeast( '5.5.0' ) && $hasUsersModule ){
			/** @var Entity_User $user */
			$user	= $this->modelUser->get( $userId );
			if( NULL !== $user && class_exists( 'Logic_UserPassword' ) ){					//  @todo  remove line if old user password support decays
				$logic	= Logic_UserPassword::getInstance( $this->env );
				if( $logic->hasUserPassword( $user ) ){											//  @todo  remove line if old user password support decays
					return $logic->validateUserPassword( $user, $password );
				}
				else{																				//  @todo  remove whole block if old user password support decays
					$salt		= $this->env->getConfig()->get( 'module.resource_users.password.salt' );
					$crypt		= md5( $salt.$password );
					$conditions	= ['userId' => $userId, 'password' => $crypt];
					if( $this->modelUser->count( $conditions ) === 1 ){
						$logic->migrateOldUserPassword( $user, $password );
						return TRUE;
					}
				}
			}
			else{																					//  @todo  remove whole block if old user password support decays
				$salt		= $this->env->getConfig()->get( 'module.resource_users.password.salt' );
				$crypt		= md5( $salt.$password );
				$conditions	= ['userId' => $userId, 'password' => $crypt];
				return $this->modelUser->count( $conditions ) === 1;
			}
		}
		return FALSE;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function clearCurrentUser(): void
	{
		$this->session->remove( 'auth_user_id' );
		$this->session->remove( 'auth_role_id' );
		$this->session->remove( 'auth_status' );
		$this->env->getCaptain()->callHook( 'Auth', 'clearCurrentUser', $this );
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
			/** @var ?Entity_Role $role */
			$role	= $this->modelRole->get( $roleId );
			if( NULL !== $role )
				return $role;
			if( $strict )
				throw new RuntimeException( 'No valid role identified' );
		}
		return NULL;
	}

	/**
	 * @param		bool	$strict
	 * @return		string|NULL
	 */
	public function getCurrentRoleId( bool $strict = TRUE ): ?string
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->session->get( 'auth_role_id' );
	}

	/**
	 *	@param		bool		$strict
	 *	@param		bool		$withRole
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE ): ?object
	{
		$userId	= $this->getCurrentUserId( FALSE );
		if( $userId ){
			$extensions	= Logic_User::EXTEND_ROLE | Logic_User::EXTEND_GROUPS;
			$user		= $this->logicUser->checkId( $userId, $extensions, FALSE );
			if( NULL !== $user )
				return $user;
		}

		if( $strict )
			throw new RuntimeException( 'No valid user identified' );
		return NULL;
	}

	/**
	 *	@param		bool		$strict
	 *	@return		int|string|NULL
	 */
	public function getCurrentUserId( bool $strict = TRUE ): int|string|null
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

	public function isCurrentUserId( int|string $userId ): bool
	{
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	@return		self
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function noteUserActivity(): self
	{
		if( $this->isAuthenticated() && $userId = $this->getCurrentUserId( FALSE ) ){				//  get ID of current user (or zero)
			$this->modelUser->edit( $userId, ['activeAt' => time()] );
		}
		return $this;
	}

	public function setAuthenticatedUser( Entity_User $user ): self
	{
		$this->setIdentifiedUser( $user );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	public function setIdentifiedUser( object $user ): self
	{
		$this->session->set( 'auth_backend', 'Local' );
		$this->session->set( 'auth_user_id', $user->userId );
		$this->session->set( 'auth_role_id', $user->roleId );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_IDENTIFIED );
		return $this;
	}

	protected function __onInit(): void
	{
		$this->session		= $this->env->getSession();
		$this->logicUser	= new Logic_User( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		$this->modelRole	= new Model_Role( $this->env );
	}
}
