<?php
/**
 *	Role administration views.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.View.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Role administration views.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.View.Admin
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class View_Admin_Role extends CMF_Hydrogen_View
{
	public function index()
	{
	}

	public function add()
	{
	}

	public function edit()
	{
		$disclosure	= new CMF_Hydrogen_Environment_Resource_Disclosure();
		$options	= array( 'classPrefix' => 'Controller_', 'readParameters' => FALSE );
		$this->addData( 'actions', $disclosure->reflect( 'classes/Controller/', $options ) );
		$this->addData( 'acl', $this->env->getAcl() );
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'site.role.css' );
	}
}
