<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Cache extends View
{
	public function index()
	{
		$page	= $this->env->getPage();
		$config	= $this->env->getConfig();
		$page->js->addUrl( $config->get( 'path.scripts' ).'admin.cache.js' );
	}
}
