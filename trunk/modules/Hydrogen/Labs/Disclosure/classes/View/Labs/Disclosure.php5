<?php
class View_Labs_Disclosure extends CMF_Hydrogen_View{
	public function index(){
		$config		= $this->env->getConfig();
		$page		= $this->env->getPage();

		$pathJs		= $config['path.js'];
		$pathTheme	= $config['path.themes'].$config['layout.theme'].'/';

	//	$page->js->addUrl( $pathJs.'labs.js' );

		$page->addStyleSheet( $config['path.themes'].'custom/css/site.labs.info.css' );
	}
}
?>
