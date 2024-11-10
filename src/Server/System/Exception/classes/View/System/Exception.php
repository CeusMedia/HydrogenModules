<?php

use CeusMedia\HydrogenFramework\View;

class View_System_Exception extends View
{
	public function index(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.server.system.exception.css' );
	}
}
