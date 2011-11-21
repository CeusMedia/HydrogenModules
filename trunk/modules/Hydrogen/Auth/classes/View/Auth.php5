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
	public function login() {}

	public function loginInside(){
		return $this->loadContentFile( 'html/auth.login.inside.html' );
	}
}
?>
