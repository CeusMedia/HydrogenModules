<?php

use CeusMedia\Cache\SimpleCacheInterface;

use CeusMedia\Cache\Factory as CacheV2Factory;
use CeusMedia\Cache\SimpleCacheFactory as CacheV3Factory;

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo		apply module config main switch
 */
class Model_Cache
{
	protected SimpleCacheInterface $model;
	protected Environment $env;
	protected Dictionary $config;

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->config	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );

		if( class_exists( '\\CeusMedia\\Cache\\SimpleCacheFactory' ) )						//  CeusMedia/Cache v0.3
			$factory	= new CacheV3Factory();
		else if( class_exists( '\\CeusMedia\\Cache\\Factory' ) )							//  CeusMedia/Cache v0.2
			$factory	= new CacheV2Factory();
		else
			throw new RuntimeException( 'No suitable cache implementation found' );

		$type		= $this->config->get( 'type' );
		$resource	= $this->config->get( 'resource' );
		$context	= $this->config->get( 'context' );
		$expiration	= (int) $this->config->get( 'expiration', 0 );

		if( 'PDO' === $type ){
			if( !$this->env->getDatabase() )
				throw new RuntimeException( 'A database connection is needed for PDO cache adapter' );
			$dbc		= $this->env->getDatabase();
			$resource	= [$dbc, $dbc->getPrefix().$resource];
		}
		$model	= $factory->newStorage( $type, $resource, $context, $expiration );
		$this->env->set( 'cache', $model );
	}

	public function flush( ?string $context = NULL ): bool
	{
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->get( 'context', '' ).$context );
			$result	= $this->model->clear();
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->clear();
	}

	/**
	 *	@param		string			$key
	 *	@param		string|NULL		$default
	 *	@param		string|NULL		$context
	 *	@return		mixed
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( string $key, ?string $default = NULL, ?string $context = NULL ): mixed
	{
		if( $context !== NULL ){
			if( !$this->has( $key, $context ) )
				return $default;
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->get( 'context' ).$context );
			$result	= unserialize( $this->model->get( $key ) );
			$this->setContext( $_ctx );
			return $result;
		}
		if( $this->has( $key ) )
			return unserialize( $this->model->get( $key ) );
		return $default;
	}

	public function getContext(): ?string
	{
		return $this->model->getContext();
	}

	public function getType()
	{
		return $this->config->get( 'type' );
	}

	/**
	 *	@param		string		$key
	 *	@param		string|NULL		$context
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function has( string $key, ?string $context = NULL ): bool
	{
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->get( 'context' ).$context );
			$result	= $this->model->has( $key );
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->has( $key );
	}

	/**
	 *	@param		string|NULL		$context
	 *	@return		array
	 */
	public function index( ?string $context = NULL ): array
	{
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->get( 'context' ).$context );
			$result	= $this->model->index();
			$this->setContext( $_ctx );
			return $result;
		}
		return $this->model->index();
	}

	/**
	 *	@param		string		$key
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $key ): bool
	{
		return $this->model->delete( $key );
	}

	/**
	 *	@param		string			$key
	 *	@param		mixed			$value
	 *	@param		string|NULL		$context
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function set( string $key, mixed $value, ?string $context = NULL ): bool
	{
		if( $context !== NULL ){
			$_ctx	= $this->getContext();
			$this->setContext( $this->config->get( 'context' ).$context );
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
	 *	@return		self
	 */
	public function setContext( ?string $context ): self
	{
		$this->model->setContext( $context );
		return $this;
	}
}
