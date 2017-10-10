<?php
class Logic_Authentication_Backend_Local extends CMF_Hydrogen_Logic{

	protected $modelUser;
	protected $modelRole;
	protected $session;

	protected function __onInit(){
		$this->session		= $this->env->getSession();
		$this->modelUser	= new Model_User( $this->env );
		$this->modelRole	= new Model_Role( $this->env );
	}

	/**
	 *	@todo		remove support for old user password
	 */
	public function checkPassword( $userId, $password ){
		$isMinimumVersion	= version_compare( PHP_VERSION, '5.5.0', '>=' );
		$hasUsersModule		= $this->env->getModules()->has( 'Resource_Users' );
		if( $isMinimumVersion && $hasUsersModule ){
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
		return $this->env->getSession()->get( 'roleId' );
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

	public function noteUserActivity(){
		if( $userId = $this->getCurrentUserId( FALSE ) ){													//  get ID of current user (or zero)
			$this->modelUser->edit( $userId, array( 'activeAt' => time() ) );
		}
	}

/*	public function setCurrentUser( $userId ){


		$this->env->getSession()->set( 'userId', $userId );
		$this->env->getSession()->set( 'userId', $userId );
	}*/
}
