<?php
/**
 *	User View.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\View;

/**
 *	User View.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.View.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class View_Manage_User extends View
{
	public function index(): void
	{
		$words		=$this->env->getLanguage()->getWords( 'manage/user' );
		$this->setData( $words['status'], 'states' );
		$this->setData( $words['activity'], 'activities' );
	}

	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	protected function __onInit(): void
	{
		$countries			= $this->env->getLanguage()->getWords( 'countries' );
		$countriesAsJson	= json_encode( array_values( $countries ) );
		$this->env->getPage()
			->addThemeStyle( 'module.manage.users.css' )
			->loadLocalScript( 'module.manage.users.js', 8 )
			->runScript( 'ModuleManageUsers.setCountries('.$countriesAsJson.').init()' );
	}
}
