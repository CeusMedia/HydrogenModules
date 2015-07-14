<?php
class Logic_Authentication{

	static protected $instance;
	protected $env;

	protected function __construct( $env ){
		$this->env			= $env;
		$this->model		= new Model_User( $env );
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Authentication( $env );
		return self::$instance;
	}

	public function isAuthenticated(){
		return $this->env->getSession()->get( 'userId' );
	}

	public function isCurrentUserId( $userId ){
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	public function getCurrentRoleId( $strict = TRUE ){
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->env->getSession()->get( 'userId');
	}

	public function getCurrentUser( $strict = TRUE ){
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->model->get( $this->getCurrentUserId() );
	}

	public function getCurrentUserId( $strict = TRUE ){
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->env->getSession()->get( 'userId' );
	}

/*	public function setCurrentUser( $userId ){


		$this->env->getSession()->set( 'userId', $userId );
		$this->env->getSession()->set( 'userId', $userId );
	}*/
}
