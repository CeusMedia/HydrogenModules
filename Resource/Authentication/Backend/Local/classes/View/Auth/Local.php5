<?php
/**
 *	Authentication View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Auth.php 1644 2010-11-03 20:39:04Z christian.wuerker $
 */
class View_Auth_Local extends CMF_Hydrogen_View {

	public function __onInit(){
		$this->env->getPage()->addCommonStyle('module.resource.auth.local.css');
		$this->env->getPage()->js->addModuleFile( 'module.resource.auth.local.js');
	}

	public function confirm(){}

	public function login() {
		$this->env->getPage()->js->addScriptOnReady('ModuleResourceAuthLocal.Login.init();');
	}

	public function password(){}

	public function register(){
		$this->env->getPage()->js->addScriptOnReady('ModuleResourceAuthLocal.Registration.init();');
	}

	public function renderRegisterFormExtensions(){
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, array() );
	}
}
?>
