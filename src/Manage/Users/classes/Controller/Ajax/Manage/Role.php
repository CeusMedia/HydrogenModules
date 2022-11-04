<?php
/**
 *	Role AJAX Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

/**
 *	Role AJAX Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
class Controller_Ajax_Manage_Role extends AjaxController
{
	protected $request;
	protected $modelRole;
	protected $modelRoleRight;

	/**
	 *	Change role right by toggling.
	 *	@access		public
	 */
	public function changeRight()
	{
		$roleId		= (int) $this->request->get( 'roleId' );
		$controller	= trim( $this->request->get( 'controller' ) );
		$action		= trim( $this->request->get( 'action' ) );

		if( $roleId === 0 )
			$this->respondError( 0, 'No role ID given', 400 );
		if( !$this->modelRole->get( $roleId ) )
			$this->respondError( 0, 'Invalid role ID', 400 );
		if( strlen( $controller ) === 0 )
			$this->respondError( 0, 'No controller given', 400 );
		if( strlen( $action ) === 0 )
			$this->respondError( 0, 'No action given', 400 );

		$indices	= array(
			'roleId'		=> $roleId,
			'controller'	=> Model_Role_Right::minifyController( $controller ),
			'action'		=> $action
		);
		$right	= $this->modelRoleRight->getByIndices( $indices );
		if( $right )
			$this->modelRoleRight->remove( $right->roleRightId );
		else{
			$data	= array_merge( $indices, array( 'timestamp' => time() ) );
			$this->modelRoleRight->add( $data );
		}
		$right	= $this->modelRoleRight->getByIndices( $indices );
		$this->respondData( array( 'current' => (bool) $right ) );
	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
//		$this->modelRoleRight	= $this->getModel( 'Role_Right' );
		$this->modelRole		= new Model_Role( $this->env );
		$this->modelRoleRight	= new Model_Role_Right( $this->env );
	}
}
