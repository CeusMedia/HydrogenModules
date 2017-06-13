<?php
/**
 *	Authentication View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Auth.php 1644 2010-11-03 20:39:04Z christian.wuerker $
 */
class View_Auth extends CMF_Hydrogen_View {

	public function confirm(){}

	public function login() {}

	public function password(){}

	public function register(){}

	public function renderRegisterFormExtensions(){
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, array() );
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './' );
		$env->getModules()->callHook( "Auth", "registerLoginTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}


}
?>
