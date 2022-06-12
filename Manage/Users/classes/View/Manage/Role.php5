<?php
/**
 *	Basic view for role management.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */

use CeusMedia\HydrogenFramework\View;

/**
 *	Basic view for role management.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
class View_Manage_Role extends View
{
	public function index()
	{
	}

	public function add()
	{
	}

	public function edit()
	{
		$roleId	= $this->getData( 'roleId' );
		$this->env->getPage()->runScript( 'ModuleManageUsers.setRoleId('.$roleId.').init()' );
	}

	protected function __onInit()
	{
		$this->env->getPage()
			->addThemeStyle( 'module.manage.users.css' )
			->loadLocalScript( 'module.manage.users.js', 8 );
	}
}
