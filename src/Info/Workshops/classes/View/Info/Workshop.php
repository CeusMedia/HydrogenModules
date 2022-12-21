<?php

use CeusMedia\HydrogenFramework\View;

class View_Info_Workshop extends View
{
	protected function __onInit(): void
	{
		$this->env->getPage()->js->addScriptOnReady( 'ModuleInfoWorkshop.init();' );
	}

	public function index()
	{
	}

	public function view()
	{
	}
}
