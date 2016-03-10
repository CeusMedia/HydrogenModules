<?php
class Logic_Authentication{

	static protected $instance;
	protected $env;
	protected $backend;
	protected $backends	= array();

	protected function __construct( $env ){
		$this->env			= $env;
		$this->env->getCaptain()->callHook( 'Auth', 'registerBackends', $this );
		if( !$this->backends )
			throw new RuntimeException( 'No authentication backend installed' );
		$this->setBackend( @array_shift( $this->getBackends() ) );
	}

	public function checkPassword( $userId, $password ){
		return $this->backend->checkPassword( $userId, $password );
	}

	public function getBackends(){
		return $this->backends;
	}

	public function getCurrentRole( $strict = TRUE ){
		return $this->backend->getCurrentRole( $strict );
	}

	public function getCurrentRoleId( $strict = TRUE ){
		return $this->backend->getCurrentRoleId( $strict );
	}

	public function getCurrentUser( $strict = TRUE, $withRole = FALSE ){
		return $this->backend->getCurrentUser( $strict, $withRole );
	}

	public function getCurrentUserId( $strict = TRUE ){
		return $this->backend->getCurrentUserId( $strict );
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Authentication( $env );
		return self::$instance;
	}

	public function isAuthenticated(){
		return $this->backend->isAuthenticated();
	}

	public function isCurrentUserId( $userId ){
		return $this->backend->getCurrentUserId( FALSE ) == $userId;
	}

	public function registerBackend( $backend ){
		if( !in_array( $backend, $this->backends ) )
			$this->backends[]	= $backend;
	}

	public function setBackend( $backend ){
		if( !in_array( $backend, $this->backends ) )
			throw new OutOfRangeException( 'Authentication backend "'.$backend.'" is not registered' );
		$className		= 'Logic_Authentication_Backend_'.$backend;
		if( !class_exists( $className ) )
			throw new BadFunctionCallException( 'Authentication backend logic class "'.$backend.'" is not existing' );
		$factory		= new ReflectionMethod( $className, 'getInstance' );
		$this->backend	= $factory->invokeArgs( NULL, array( $this->env ) );
//		$this->backend	= call_user_func_array( array( $className, 'getInstance' ), array( $this->env ) );
	}

/*	public function setCurrentUser( $userId ){
		$this->env->getSession()->set( 'userId', $userId );
		$this->env->getSession()->set( 'userId', $userId );
	}*/
}
