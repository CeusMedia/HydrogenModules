<?php
class Job_Accounting extends Job_Abstract{

	public function __onInit(){
		$this->modules	= $this->env->getModules();
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_provision.', TRUE );
		$this->cache	= $this->modules->has( 'Resource_Cache' ) ? new Model_Cache( $this->env ) : NULL;
	}

	public function clearCache(){
		if( $this->cache )
			$this->cache->flush( 'Provision.userLicenseKey' );
	}
}
?>
