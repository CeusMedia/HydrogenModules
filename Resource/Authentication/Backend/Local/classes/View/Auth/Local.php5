<?php

use CeusMedia\HydrogenFramework\View;

/**
 *	Authentication View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class View_Auth_Local extends View
{
	protected function __onInit()
	{
		$this->env->getPage()->addCommonStyle('module.resource.auth.local.css');
		$this->env->getPage()->js->addModuleFile( 'module.resource.auth.local.js');
	}

	public function confirm()
	{
	}

	public function login()
	{
		$this->env->getPage()->js->addScriptOnReady('ModuleResourceAuthLocal.Login.init();');
	}

	public function password()
	{
		$this->env->getPage()->js->addScriptOnReady('ModuleResourceAuthLocal.Password.init();');
	}

	public function register()
	{
		$this->env->getPage()->js->addScriptOnReady('ModuleResourceAuthLocal.Registration.init();');
	}

	public function renderRegisterFormExtensions()
	{
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, array() );
	}
}
