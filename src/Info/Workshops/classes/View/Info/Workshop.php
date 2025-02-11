<?php

use CeusMedia\HydrogenFramework\View;

class View_Info_Workshop extends View
{
	public function index(): void
	{
	}

	public function view(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->js->addScriptOnReady( 'ModuleInfoWorkshop.init();' );
	}
}
