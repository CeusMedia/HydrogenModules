<?php
class Model_Cache{

	protected $model;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$this->config	= (object) $this->env->getConfig()->getAll( 'module.resource_cache.' );

		$factory	= new CMM_SEA_Factory();
		$type		= $this->config->type;
		$resource	= $this->config->resource ? $this->config->resource : NULL;
		$context	= $this->config->context ? $this->config->context : NULL;
		$expiration	= $this->config->expiration ? (int) $this->config->expiration : 0;

		if( $type === 'PDO' ){
			if( !$this->env->getDatabase() )
				throw new RuntimeException( 'A database connection is needed for PDO cache adapter' );
			$resource	= array( $this->env->getDatabase(), $this->dbc->getPrefix().$resource );
		}
		$this->model	= $factory->newStorage( $type, $resource, $context, $expiration );
		$this->env->set( 'cache', $this );
	}

	public function flush(){
		return $this->model->flush();
	}

	public function get( $key, $default = NULL ){
		if( $this->has( $key ) )
			return unserialize( $this->model->get( $key ) );
		return $default;
	}

	public function getType(){
		return $this->config->type;
	}

	public function has( $key ){
		return $this->model->has( $key );
	}

	public function index(){
		return $this->model->index();
	}

	public function remove( $key ){
		return $this->model->remove( $key );
	}

	public function set( $key, $value ){
		return $this->model->set( $key, serialize( $value ) );
	}

	/**
	 *	Sets context within cache storage.
	 *	If folder is not existing, it will be created.
	 *	@access		public
	 *	@param		string		$context		Context within cache storage
	 *	@return		void
	 */
	public function setContext( $context ){
		$this->model->set( $context );
	}
}
?>
