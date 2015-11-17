<?php
class Logic_Authentication{

	static protected $instance;
	protected $env;
	protected $modelUser;
	protected $modelRole;

	protected function __construct( $env ){
		$this->env			= $env;
		$this->modelUser	= new Model_User( $env );
		$this->modelRole	= new Model_Role( $env );
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Authentication( $env );
		return self::$instance;
	}

	public function isAuthenticated(){
		return $this->env->getSession()->get( 'userId' );
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

	public function isCurrentUserId( $userId ){
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

/*	public function setCurrentUser( $userId ){


		$this->env->getSession()->set( 'userId', $userId );
		$this->env->getSession()->set( 'userId', $userId );
	}*/
}
