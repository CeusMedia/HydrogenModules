<?php
/**
 *	Role View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1490 2010-10-07 08:37:18Z christian.wuerker $
 */
/**
 *	Role View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1490 2010-10-07 08:37:18Z christian.wuerker $
 */
class View_Manage_Role extends CMF_Hydrogen_View {

	public function index() {}

	public function add() {}

	public function edit() {
		$disclosure	= new CMF_Hydrogen_Environment_Resource_Disclosure();
		$options	= array( 'classPrefix' => 'Controller_', 'readParameters' => FALSE );
		$this->addData( 'actions', $disclosure->reflect( 'classes/Controller/', $options ) );
		$this->addData( 'acl', $this->env->getAcl() );
	}
}
?>