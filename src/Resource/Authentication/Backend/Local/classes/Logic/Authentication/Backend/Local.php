<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
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
	 *	@throws		ReflectionException
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
		$this->session->remove( Logic_Authentication::$sessionKeyAuthUserId );
		$this->session->remove( Logic_Authentication::$sessionKeyAuthRoleId );
		$this->session->remove( Logic_Authentication::$sessionKeyAuthStatus );
		$this->env->getCaptain()->callHook( 'Auth', 'clearCurrentUser', $this );
	}

	/**
	 *	@return		Entity_Group[]
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getCurrentGroups(): array
	{
		return $this->logicUser->getUserGroups( $this->getCurrentUser() );
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
	 *	@param		int			$extensions		Flags: extend user entity, default: Logic_User::EXTEND_ROLE|Logic_User::EXTEND_GROUPS
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getCurrentUser( bool $strict = TRUE, int $extensions = Logic_User::EXTEND_ROLE|Logic_User::EXTEND_GROUPS ): ?object
	{
		$userId	= $this->getCurrentUserId( FALSE );
		if( $userId ){
			$user	= $this->logicUser->checkId( $userId, $extensions, FALSE );
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
	 *	@throws		RuntimeException	if not user is authenticated in session
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

	/**
	 *	Indicates whether the current user has access a module entity by groups.
	 *	@param		ModuleDefinition|string		$module
	 *	@param		int|string					$entityId
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function hasAccessToModuleEntity( ModuleDefinition|string $module, int|string $entityId ): bool
	{
		$userId			= $this->getCurrentUserId();
		$logicUser		= Logic_User::getInstance( $this->env );
		$logicRelation	= Logic_GroupRelation::getInstance( $this->env );
		return $logicRelation->isRelatedToModuleEntity( $module, $entityId );
	}

	/**
	 *	Indicates whether given user ID is currently authenticated within in this session.
	 *	@return bool
	 */
	public function isAuthenticated(): bool
	{
		if( !$this->isIdentified() )
			return FALSE;
		$authStatus	= (int) $this->session->get( Logic_Authentication::$sessionKeyAuthStatus );
		return $authStatus == Logic_Authentication::STATUS_AUTHENTICATED;
	}

	/**
	 *	Indicates whether given user ID is currently authenticated within in this session.
	 *	@param		int|string		$userId
	 *	@return		bool
	 */
	public function isCurrentUserId( int|string $userId ): bool
	{
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	Indicates whether a user is at least identified within this session.
	 *	@return		bool
	 */
	public function isIdentified(): bool
	{
		return (int) $this->session->get( Logic_Authentication::$sessionKeyAuthUserId ) > 0;
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

	/**
	 *	@param		Entity_User		$user
	 *	@return		self
	 */
	public function setAuthenticatedUser( Entity_User $user ): self
	{
		$this->setIdentifiedUser( $user );
		$this->session->set( Logic_Authentication::$sessionKeyAuthStatus, Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	/**
	 *	@param		Entity_User		$user
	 *	@return		self
	 */
	public function setIdentifiedUser( Entity_User $user ): self
	{
		$this->session->set( Logic_Authentication::$sessionKeyAuthBackend, 'Local' );
		$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $user->userId );
		$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $user->roleId );
		$this->session->set( Logic_Authentication::$sessionKeyAuthStatus, Logic_Authentication::STATUS_IDENTIFIED );
		return $this;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->session		= $this->env->getSession();
		$this->logicUser	= new Logic_User( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		$this->modelRole	= new Model_Role( $this->env );
	}
}
