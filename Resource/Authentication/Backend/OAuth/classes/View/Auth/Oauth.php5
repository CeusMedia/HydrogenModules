<?php
/**
 *	Authentication View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class View_Auth_Oauth extends CMF_Hydrogen_View
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

	public function renderRegisterFormExtensions()
	{
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, array() );
	}
}
