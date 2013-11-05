<?php
/**
 *	Basic view for role management.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Basic view for role management.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class View_Manage_Role extends CMF_Hydrogen_View {

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		parent::__construct( $env );
		$this->env->getPage()->addThemeStyle( 'module.manage.users.css' );
	}
	
	public function index(){
	}

	public function add(){
	}

	public function edit(){
		$disclosure	= new CMF_Hydrogen_Environment_Resource_Disclosure();
		$options	= array( 'classPrefix' => 'Controller_', 'readParameters' => FALSE );
		$this->addData( 'actions', $disclosure->reflect( 'classes/Controller/', $options ) );
		$this->addData( 'acl', $this->env->getAcl() );
	}
}
?>