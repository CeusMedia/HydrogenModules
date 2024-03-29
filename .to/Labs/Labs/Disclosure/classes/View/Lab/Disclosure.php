<?php

use CeusMedia\HydrogenFramework\View;

class View_Lab_Disclosure extends View{
	public function index(){
		$config		= $this->env->getConfig();
		$page		= $this->env->getPage();

		$pathJs		= $config['path.js'];
		$pathTheme	= $config['path.themes'].$config['layout.theme'].'/';

		$page->js->addUrl( 'http://js.ceusmedia.de/jquery/cmLadder/0.2.js' );
		$page->css->theme->addUrl( 'http://js.ceusmedia.de/jquery/cmLadder/0.2.css' );
		$page->addThemeStyle( 'site.lab.disclosure.css' );

	//	$page->js->addScript( $script );
	}
}
?>
