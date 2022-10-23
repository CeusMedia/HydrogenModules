<?php
/**
 *	Role administration views.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.View.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */

use CeusMedia\HydrogenFramework\Environment\Resource\Disclosure;
use CeusMedia\HydrogenFramework\View;

/**
 *	Role administration views.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.View.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */
class View_Admin_Role extends View
{
	public function index()
	{
	}

	public function add()
	{
	}

	public function edit()
	{
		$disclosure	= new Disclosure();
		$options	= array( 'classPrefix' => 'Controller_', 'readParameters' => FALSE );
		$this->addData( 'actions', $disclosure->reflect( 'classes/Controller/', $options ) );
		$this->addData( 'acl', $this->env->getAcl() );
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'site.role.css' );
	}
}
