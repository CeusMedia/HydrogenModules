<?php
class Controller_Compressor extends CMF_Hydrogen_Controller{
	public function flush(){
		$page	= $this->env->getPage();
		$page->js->clearCache();
		$page->css->primer->clearCache();
		$page->css->theme->clearCache();
		$this->env->getMessenger()->noteNotice( 'Compressed resource files removed from cache' );
		$this->restart( './' );
	}
}
?>
