<?php

use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

/**
 *	@todo		apply module config main switch
 */
class Job_Cache extends Job_Abstract
{
	protected SimpleCacheInterface $cache;

	public function countObjects(): void
	{
//		$number		= 0;
//		$cache		= $this->env->getCache();
		$number		= count( array_diff( $this->cache->index(), ['.htaccess'] ) );
/*		foreach( $this->cache->index() as $entry )
			if( $entry !== '.htaccess' )
				$number	+= 1;*/
		$this->out( sprintf( 'Found %s objects in cache.', $number ) );
	}

	public function clearObjects(): void
	{
		$this->cache->clear();
/*		foreach( $this->cache->index() as $entry )
			if( $entry !== '.htaccess' )
				$this->cache->delete( $entry );*/
		$this->out( 'All cache objects removed.' );
	}

	protected function __onInit(): void
	{
		$this->cache	= $this->env->getCache();
	}
}
