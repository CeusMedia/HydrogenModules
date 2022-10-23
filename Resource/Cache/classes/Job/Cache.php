<?php
/**
 *	@todo		apply module config main switch
 */
class Job_Cache extends Job_Abstract
{
	protected $cache;

	public function countObjects()
	{
		$number		= 0;
		$cache		= $this->env->getCache();
		foreach( $this->cache->index() as $entry )
			if( $entry !== '.htaccess' )
				$number	+= 1;
		$this->out( sprintf( 'Found %s objects in cache.', $number ) );
	}

	public function clearObjects()
	{
		foreach( $this->cache->index() as $entry )
			if( $entry !== '.htaccess' )
				$this->cache->remove( $entry );
		$this->out( 'All cache objects removed.' );
	}

	protected function __onInit()
	{
		$this->cache	= $this->env->getCache();
	}
}
