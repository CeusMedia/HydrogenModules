<?php
class Logic_Authentication_Backend_Local extends CMF_Hydrogen_Logic
{
	protected $modelUser;
	protected $modelRole;
	protected $session;

	protected function __onInit()
	{
		$this->session		= $this->env->getSession();
		$this->modelUser	= new Model_User( $this->env );
		$this->modelRole	= new Model_Role( $this->env );
	}

	/**
	 *	@todo		remove support for old user password
	 */
	public function checkPassword( $userId, string $password ): bool
	{
		$hasUsersModule		= $this->env->getModules()->has( 'Resource_Users' );
		if( $this->env->getPhp()->version->isAtLeast( '5.5.0' ) && $hasUsersModule ){
			if( class_exists( 'Logic_UserPassword' ) ){												//  @todo  remove line if old user password support decays
				$logic	= Logic_UserPassword::getInstance( $this->env );
				if( $logic->hasUserPassword( $userId ) ){											//  @todo  remove line if old user password support decays
					return $logic->validateUserPassword( $userId, $password );
				}
				else{																				//  @todo  remove whole block if old user password support decays
					$salt		= $this->env->getConfig()->get( 'module.resource_users.password.salt' );
					$crypt		= md5( $salt.$password );
					$conditions	= array( 'userId' => $userId, 'password' => $crypt );
					if( $this->modelUser->count( $conditions ) === 1 ){
						$logic->migrateOldUserPassword( $userId, $password );
						return TRUE;
					}
				}
			}
			else{																					//  @todo  remove whole block if old user password support decays
				$salt		= $this->env->getConfig()->get( 'module.resource_users.password.salt' );
				$crypt		= md5( $salt.$password );
				$conditions	= array( 'userId' => $userId, 'password' => $crypt );
				return $this->modelUser->count( $conditions ) === 1;
			}
		}
		return FALSE;
	}

	public function clearCurrentUser()
	{
		$this->session->remove( 'auth_user_id' );
		$this->session->remove( 'auth_role_id' );
		$this->session->remove( 'auth_status' );
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
		return $this->session->get( 'auth_role_id' );
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

	public function noteUserActivity(): self
	{
		if( $this->isAuthenticated() && $userId = $this->getCurrentUserId( FALSE ) ){				//  get ID of current user (or zero)
			$this->modelUser->edit( $userId, array( 'activeAt' => time() ) );
		}
		return $this;
	}

	public function setAuthenticatedUser( $user ): self
	{
		$this->setIdentifiedUser( $user );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	public function setIdentifiedUser( $user ): self
	{
		$this->session->set( 'auth_backend', 'Local' );
		$this->session->set( 'auth_user_id', $user->userId );
		$this->session->set( 'auth_role_id', $user->roleId );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_IDENTIFIED );
		return $this;
	}
}
