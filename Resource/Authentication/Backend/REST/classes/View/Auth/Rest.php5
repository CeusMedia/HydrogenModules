<?php
/**
 *	Authentication View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class View_Auth_Rest extends CMF_Hydrogen_View
{
	public function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.resource.auth.rest.css' );
	}

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
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, array() );
	}
}
