<?php

use CeusMedia\HydrogenFramework\View;

/**
 *	Authentication View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class View_Auth_Rest extends View
{
	public function confirm()
	{
	}

	public function login()
	{
	}

	public function register()
	{
	}

	public function renderRegisterFormExtensions()
	{
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, [] );
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.resource.auth.rest.css' );
	}
}
