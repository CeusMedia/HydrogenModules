<?php

use CeusMedia\HydrogenFramework\View;

/**
 *	Authentication View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 *	@version		$Id: Auth.php 1644 2010-11-03 20:39:04Z christian.wuerker $
 */
class View_Auth_Oauth2 extends View
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
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, [] );
	}
}
