<?php
/**
 *	Authentication View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Auth.php 1644 2010-11-03 20:39:04Z christian.wuerker $
 */
class View_Auth_Rest extends CMF_Hydrogen_View {

	public function login() {}

	public function renderRegisterFormExtensions(){
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, array() );
	}
}
?>
