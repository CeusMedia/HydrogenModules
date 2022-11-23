<?php

use CeusMedia\Cache\Factory as CacheV2Factory;
use CeusMedia\Cache\SimpleCacheFactory as CacheV3Factory;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;
use RuntimeException;

class Hook_Resource_Cache extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function onEnvInitCache()
	{
		$config	= (object) $this->env->getConfig()->getAll( 'module.resource_cache.' );

		if( class_exists( '\\CeusMedia\\Cache\\SimpleCacheFactory' ) )						//  CeusMedia/Cache v0.3
			$factory	= new CacheV3Factory;
		else if( class_exists( '\\CeusMedia\\Cache\\Factory' ) )								//  CeusMedia/Cache v0.2
			$factory	= new CacheV2Factory();
		else
			throw new RuntimeException( 'No suitable cache implementation found' );

		$type		= $config->type;
		$resource	= $config->resource ?: NULL;
		$context	= $config->context ?: NULL;
		$expiration	= $config->expiration ? (int) $config->expiration : 0;

		if( 'PDO' === $type ){
			if( !$this->env->getDatabase() )
				throw new RuntimeException( 'A database connection is needed for PDO cache adapter' );
			$dbc		= $this->env->getDatabase();
			$resource	= array( $dbc, $dbc->getPrefix().$resource );
		}
		$model	= $factory->newStorage( $type, $resource, $context, $expiration );
		$this->env->set( 'cache', $model );
		//$this->env->set( 'cache', new Model_Cache( $this->env ) );
	}
}
