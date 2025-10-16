<?php

use CeusMedia\HydrogenFramework\View;

/**
 *	Authentication View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class View_Auth_Oauth extends View
{
	public function confirm()
	{
	}

	public function index()
	{
	}

	public function login()
	{
	}

	public function password()
	{
	}

	public function register()
	{
	}

	/**
	 *	@throws		ReflectionException
	 */
	public function renderRegisterFormExtensions(): ?bool
	{
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this );
	}
}
