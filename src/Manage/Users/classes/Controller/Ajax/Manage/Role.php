<?php
/**
 *	Role AJAX Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

/**
 *	Role AJAX Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Ajax_Manage_Role extends AjaxController
{
	protected Logic_Role $logicRole;
	protected Logic_RoleRight $logicRight;

	/**
	 *	Change role right by toggling.
	 *	@access		public
	 */
	public function changeRight(): void
	{
		$roleId		= (int) $this->request->get( 'roleId', 0 );
		$controller	= trim( $this->request->get( 'controller' ) );
		$action		= trim( $this->request->get( 'action' ) );

		if( 0 === $roleId )
			$this->respondError( 0, 'No role ID given', 400 );
		if( NULL === $this->logicRole->get( $roleId ) )
			$this->respondError( 0, 'Invalid role ID', 400 );
		if( '' === $controller )
			$this->respondError( 0, 'No controller given', 400 );
		if( '' === $action )
			$this->respondError( 0, 'No action given', 400 );

		if( $this->logicRight->has( $roleId, $controller, $action ) )
			$this->logicRight->remove( $roleId, $controller, $action );
		else
			$this->logicRight->add( $roleId, $controller, $action );

		$this->respondData( [
			'current'	=> $this->logicRight->has( $roleId, $controller, $action )
		] );
	}

	protected function __onInit(): void
	{
		$this->logicRole		= Logic_Role::getInstance( $this->env );
		$this->logicRight		= Logic_RoleRight::getInstance( $this->env );
	}
}
