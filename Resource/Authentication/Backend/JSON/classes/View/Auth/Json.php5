<?php
/**
 *	Authentication View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class View_Auth_Json extends CMF_Hydrogen_View
{
	public function login()
	{
	}

	public function renderRegisterFormExtensions()
	{
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, array() );
	}
}
