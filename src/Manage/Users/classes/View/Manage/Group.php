<?php
/**
 *	Basic view for group management.
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
class View_Manage_Group extends View
{
	public function index()
	{
	}

	public function add(): void
	{
	}

	public function edit(): void
	{
		$groupId	= $this->getData( 'groupId' );
		$this->env->getPage()->runScript( 'ModuleManageUsers.setGroupId('.$groupId.').init()' );
	}

	protected function __onInit(): void
	{
		$this->env->getPage()
			->addThemeStyle( 'module.manage.users.css' )
			->loadLocalScript( 'module.manage.users.js', 8 );
	}
}
