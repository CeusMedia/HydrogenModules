<?php
class View_Admin_Cache extends CMF_Hydrogen_View{

	public function index(){
		$page	= $this->env->getPage();
		$config	= $this->env->getConfig();
		$page->js->addUrl( $config->get( 'path.scripts' ).'admin.cache.js' );
	}
}
?>
