<?php
class Logic_Authentication_Backend_Rest{

	static protected $instance;
	protected $env;

	protected function __construct( $env ){
		$this->env			= $env;
		$this->client		= $this->env->get( 'restClient' );
	}

	public function checkPassword( $username, $password ){
		$parameters	= array(
			'username'	=> $username,
			'password'	=> $password,
		);
		$result	= $this->client->post( 'authenticate', $parameters );
		return $result;
	}

	public function getCurrentRole( $strict = TRUE ){
		$roleId	= $this->getCurrentRoleId( $strict );
		if( $roleId ){
			$role	= $this->env->getServer()->postData( 'role', 'get', array( $roleId ) );
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
			$user	= $this->env->getServer()->postData( 'user', 'get', array( $userId ) );
			if( $user ){
				if( $withRole )
					$user->role	= $this->env->getServer()->postData( 'role', 'get', array( $user->roleId ) );
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

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	public function isAuthenticated(){
		return $this->env->getSession()->get( 'userId' );
	}

	public function isCurrentUserId( $userId ){
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	@todo		implement if possible, for example by using available REST resource
	 */
	protected function noteUserActivity(){
	}

/*	public function setCurrentUser( $userId ){


		$this->env->getSession()->set( 'userId', $userId );
		$this->env->getSession()->set( 'userId', $userId );
	}*/
}