<?php
/**
 *	User View.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.View.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\View;

/**
 *	User View.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.View.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class View_Admin_User extends View
{
	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
		$words		=$this->env->getLanguage()->getWords( 'admin/user' );
		$this->setData( $words['status'], 'states' );
		$this->setData( $words['activity'], 'activities' );
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'site.user.css' );
	}
}
