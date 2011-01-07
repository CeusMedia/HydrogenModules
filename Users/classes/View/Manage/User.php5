<?php
/**
 *	User View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: User.php 1605 2010-10-29 01:10:03Z christian.wuerker $
 */
/**
 *	User View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: User.php 1605 2010-10-29 01:10:03Z christian.wuerker $
 */
class View_Manage_User extends CMF_Hydrogen_View {
	public function index(){
		$words		=$this->env->getLanguage()->getWords( 'manage/user' );
		$this->setData( $words['status'], 'states' );
		$this->setData( $words['activity'], 'activities' );
	}

	public function add(){}
	public function edit(){}
}
?>