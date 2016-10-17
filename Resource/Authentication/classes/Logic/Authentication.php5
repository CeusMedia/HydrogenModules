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
		$backends	= array_keys( $this->getBackends() );
		$this->setBackend( @array_shift( $backends ) );
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

	public function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->getCurrentRoleId() );
	}

	public function isAuthenticated(){
		return $this->backend->isAuthenticated();
	}

	public function isCurrentUserId( $userId ){
		return $this->backend->getCurrentUserId( FALSE ) == $userId;
	}

	public function registerBackend( $key, $path, $label ){
		if( array_key_exists( $key, $this->backends ) )
			throw new RangeException( 'Backend "'.$key.'" is already registered' );
		$backend	= (object) array(
			'key'		=> $key,
			'path'		=> $path,
			'label'		=> $label,
			'module'	=> 'Resource_Authentication_Backend_'.$key,
			'classes'	=> (object) array(
				'logic'		=> NULL,
			),
		);
		$this->backends[$key]	= $backend;
		$classLogic		= 'Logic_Authentication_Backend_'.$key;
		if( !class_exists( $classLogic ) )
			throw new BadFunctionCallException( 'Authentication logic class for backend "'.$key.'" is not existing' );
		$backend->classes->logic = $classLogic;
	}

	public function setBackend( $key ){
		if( !array_key_exists( $key, $this->backends ) )
			throw new OutOfRangeException( 'Authentication backend "'.$key.'" is not registered' );
		$backend		= $this->backends[$key];
		$factory		= new ReflectionMethod( $backend->classes->logic, 'getInstance' );
		$this->backend	= $factory->invokeArgs( NULL, array( $this->env ) );
//		$this->backend	= call_user_func_array( array( $className, 'getInstance' ), array( $this->env ) );
	}

/*	public function setCurrentUser( $userId ){
		$this->env->getSession()->set( 'userId', $userId );
		$this->env->getSession()->set( 'userId', $userId );
	}*/
}
