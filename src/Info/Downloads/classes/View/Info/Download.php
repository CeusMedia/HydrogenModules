<?php

use CeusMedia\HydrogenFramework\View;

class View_Info_Download extends View
{
	public function index()
	{
		$this->env->getPage()->addThemeStyle( 'module.info.downloads.css' );
	}

	public function view()
	{
	}
}
