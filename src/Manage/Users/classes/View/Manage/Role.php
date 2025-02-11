<?php
/**
 *	Basic view for role management.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\View;

/**
 *	Basic view for role management.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class View_Manage_Role extends View
{
	public function index()
	{
	}

	public function add(): void
	{
	}

	public function edit(): void
	{
		$roleId	= $this->getData( 'roleId' );
		$this->env->getPage()->runScript( 'ModuleManageUsers.setRoleId('.$roleId.').init()' );
	}

	protected function __onInit(): void
	{
		$this->env->getPage()
			->addThemeStyle( 'module.manage.users.css' )
			->loadLocalScript( 'module.manage.users.js', 8 );
	}
}
