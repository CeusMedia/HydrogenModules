<?php

use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo		apply module config main switch
 */
class Model_Cache
{
	protected $model;
	protected $env;
	protected $config;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->config	= (object) $this->env->getConfig()->getAll( 'module.resource_cache.' );

		if( class_exists( '\\CeusMedia\\Cache\\SimpleCacheFactory' ) )						//  CeusMedia/Cache v0.3
			$factory	= new \CeusMedia\Cache\SimpleCacheFactory;
		else if( class_exists( '\\CeusMedia\\Cache\\Factory' ) )							//  CeusMedia/Cache v0.2
			$factory	= new \CeusMedia\Cache\Factory();
		else
			throw new RuntimeException( 'No suitable cache implementation found' );

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
		$model	= $factory->newStorage( $type, $resource, $context, $expiration );
		$this->env->set( 'cache', $model );
	}

	public function flush( ?string $context = NULL )
	{
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->context.$context );
			$result	= $this->model->flush();
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->flush();
	}

	public function get( string $key, ?string $default = NULL, ?string $context = NULL )
	{
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

	public function getContext()
	{
		return $this->model->getContext();
	}

	public function getType()
	{
		return $this->config->type;
	}

	public function has( string $key, ?string $context = NULL ): bool
	{
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->context.$context );
			$result	= $this->model->has( $key );
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->has( $key );
	}

	public function index( ?string $context = NULL ): array
	{
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->context.$context );
			$result	= $this->model->index();
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->index();
	}

	public function remove( string $key )
	{
		return $this->model->remove( $key );
	}

	public function set( string $key, $value, ?string $context = NULL )
	{
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
	 *	@param		string|NULL		$context		Context within cache storage
	 *	@return		void
	 */
	public function setContext( ?string $context ): self
	{
		$this->model->setContext( $context );
	}
}
