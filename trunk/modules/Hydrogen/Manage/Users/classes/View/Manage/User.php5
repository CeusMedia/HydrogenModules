<?php
/**
 *	User View.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	User View.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class View_Manage_User extends CMF_Hydrogen_View {

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		parent::__construct( $env );
		$this->env->getPage()->addThemeStyle( 'module.manage.users.css' );
	}

	public function index(){
		$words		=$this->env->getLanguage()->getWords( 'manage/user' );
		$this->setData( $words['status'], 'states' );
		$this->setData( $words['activity'], 'activities' );
	}

	public function add(){}

	public function edit(){}
}
?>