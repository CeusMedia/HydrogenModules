<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage extends View
{
	public function index(): string
	{
		return $this->loadContent( 'manage', 'index' );
	}
}
