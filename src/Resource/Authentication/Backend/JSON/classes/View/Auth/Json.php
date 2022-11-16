<?php

use CeusMedia\HydrogenFramework\View;

/**
 *	Authentication View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class View_Auth_Json extends View
{
	public function login()
	{
	}

	public function renderRegisterFormExtensions()
	{
		$payload	= [];
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, $payload );
	}
}
