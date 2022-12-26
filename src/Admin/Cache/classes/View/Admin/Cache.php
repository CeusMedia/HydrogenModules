<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Cache extends View
{
	public function index(): void
	{
		$page	= $this->env->getPage();
		$config	= $this->env->getConfig();
		$page->js->addUrl( $config->get( 'path.scripts' ).'admin.cache.js' );
	}
}
