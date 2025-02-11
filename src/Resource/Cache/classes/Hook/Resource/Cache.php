<?php

use CeusMedia\Cache\Encoder\Serial as SerialEncoder;
use CeusMedia\Cache\Factory as CacheV2Factory;
use CeusMedia\Cache\SimpleCacheFactory as CacheV3Factory;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Cache extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws 	\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onEnvInitCache(): void
	{
		$config	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );

		if( class_exists( '\\CeusMedia\\Cache\\SimpleCacheFactory' ) )						//  CeusMedia/Cache v0.3
			$factory	= new CacheV3Factory;
		else if( class_exists( '\\CeusMedia\\Cache\\Factory' ) )								//  CeusMedia/Cache v0.2
			$factory	= new CacheV2Factory();
		else
			throw new RuntimeException( 'No suitable cache implementation found' );

		$type		= $config->get( 'type' );
		$resource	= $config->get( 'resource' );
		$context	= $config->get( 'context' );
		$expiration	= (int) $config->get( 'expiration', 0 );

		if( 'PDO' === $type ){
			if( !$this->env->getDatabase() )
				throw new RuntimeException( 'A database connection is needed for PDO cache adapter' );
			$dbc		= $this->env->getDatabase();
			$resource	= [$dbc, $dbc->getPrefix().$resource];
		}
		$model	= $factory->newStorage( $type, $resource, $context, $expiration );
		if( 'noop' !== strtolower( $type ) )
			$model->setEncoder( SerialEncoder::class );
		$this->env->set( 'cache', $model );
		//$this->env->set( 'cache', new Model_Cache( $this->env ) );
	}
}
