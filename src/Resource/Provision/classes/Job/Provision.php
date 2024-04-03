<?php

use CeusMedia\Common\ADT\Collection\Dictionary;

/**
 *	@todo		apply module config main switch
 */
class Job_Provision extends Job_Abstract
{
	protected $cache;
	protected Dictionary $options;
	protected $modules;

	public function clearCache(): void
	{
		if( $this->cache )
			$this->cache->flush( 'Provision.userLicenseKey' );
	}

	protected function __onInit(): void
	{
		$this->modules	= $this->env->getModules();
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_provision.', TRUE );
		$this->cache	= $this->modules->has( 'Resource_Cache' ) ? new Model_Cache( $this->env ) : NULL;
	}
}
