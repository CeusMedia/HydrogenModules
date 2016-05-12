<?php
class Job_Cache extends Job_Abstract{

	public function __onInit(){
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );
	}

	public function countObjects(){
		$cache		= $this->env->getCache();
		$number		= 0;
		foreach( $cache->index() as $entry )
			if( $entry !== ".htaccess" )
				$number	+= 1;
		$this->out( sprintf( "Found %s objects in cache.\n", $number ) );
	}

	public function clearObjects(){
		$cache		= $this->env->getCache();
		foreach( $cache->index() as $entry )
			if( $entry !== ".htaccess" )
				$cache->remove( $entry );
		$this->out( "All cache objects removed.\n" );
	}
}
?>
