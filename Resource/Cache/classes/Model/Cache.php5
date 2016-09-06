<?php
class Model_Cache{

	protected $model;
	protected $env;
	protected $config;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$this->config	= (object) $this->env->getConfig()->getAll( 'module.resource_cache.' );

		$factory	= new \CeusMedia\Cache\Factory();
		$type		= $this->config->type;
		$resource	= $this->config->resource ? $this->config->resource : NULL;
		$context	= $this->config->context ? $this->config->context : NULL;
		$expiration	= $this->config->expiration ? (int) $this->config->expiration : 0;

		if( $type === 'PDO' ){
			if( !$this->env->getDatabase() )
				throw new RuntimeException( 'A database connection is needed for PDO cache adapter' );
			$dbc		= $this->env->getDatabase();
			$resource	= array( $dbc, $dbc->getPrefix().$resource );
		}
		$this->model	= $factory->newStorage( $type, $resource, $context, $expiration );
		$this->env->set( 'cache', $this );
	}

	public function flush( $context = NULL ){
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->context.$context );
			$result	= $this->model->flush();
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->flush();
	}

	public function get( $key, $default = NULL, $context = NULL ){
		if( $context !== NULL ){
			if( !$this->has( $key, $context ) )
				return $default;
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->context.$context );
			$result	= unserialize( $this->model->get( $key ) );
			$this->setContext( $_ctx );
			return $result;
		}
		if( $this->has( $key ) )
			return unserialize( $this->model->get( $key ) );
		return $default;
	}

	public function getContext(){
		return $this->model->getContext();
	}

	public function getType(){
		return $this->config->type;
	}

	public function has( $key, $context = NULL ){
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->context.$context );
			$result	= $this->model->has( $key );
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->has( $key );
	}

	public function index( $context = NULL ){
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->context.$context );
			$result	= $this->model->index();
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->index();
	}

	public function remove( $key ){
		return $this->model->remove( $key );
	}

	public function set( $key, $value, $context = NULL ){
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->context.$context );
			$result	= $this->model->set( $key, serialize( $value ) );
			$this->setContext( $_ctx );
			return $result;
		}
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
		$this->model->setContext( $context );
	}
}
?>
