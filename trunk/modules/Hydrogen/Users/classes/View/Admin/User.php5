<?php
/**
 *	User View.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.View.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	User View.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.View.Admin
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class View_Admin_User extends CMF_Hydrogen_View {

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		parent::__construct( $env );
		$this->env->getPage()->addThemeStyle( 'site.user.css' );
	}

	public function index(){
		$words		=$this->env->getLanguage()->getWords( 'admin/user' );
		$this->setData( $words['status'], 'states' );
		$this->setData( $words['activity'], 'activities' );
	}

	public function add(){}

	public function edit(){}
}
?>