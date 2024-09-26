<?php
/**
 *	Group AJAX Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

/**
 *	Group AJAX Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Ajax_Manage_Group extends AjaxController
{
	protected Model_Group $modelGroup;
	protected Model_Group_Right $modelGroupRight;

	/**
	 *	Change group right by toggling.
	 *	@access		public
	 */
	public function changeRight(): void
	{
		$groupId		= (int) $this->request->get( 'groupId' );
		$controller	= trim( $this->request->get( 'controller' ) );
		$action		= trim( $this->request->get( 'action' ) );

		if( $groupId === 0 )
			$this->respondError( 0, 'No group ID given', 400 );
		if( !$this->modelGroup->get( $groupId ) )
			$this->respondError( 0, 'Invalid group ID', 400 );
		if( strlen( $controller ) === 0 )
			$this->respondError( 0, 'No controller given', 400 );
		if( strlen( $action ) === 0 )
			$this->respondError( 0, 'No action given', 400 );

		$indices	= array(
			'groupId'		=> $groupId,
			'controller'	=> Model_Group_Right::minimizeController( $controller ),
			'action'		=> $action
		);
		$right	= $this->modelGroupRight->getByIndices( $indices );
		if( $right )
			$this->modelGroupRight->remove( $right->groupRightId );
		else{
			$data	= array_merge( $indices, array( 'timestamp' => time() ) );
			$this->modelGroupRight->add( $data );
		}
		$right	= $this->modelGroupRight->getByIndices( $indices );
		$this->respondData( array( 'current' => (bool) $right ) );
	}

	protected function __onInit(): void
	{
//		$this->modelGroupRight	= $this->getModel( 'Group_Right' );
		$this->modelGroup		= new Model_Group( $this->env );
		$this->modelGroupRight	= new Model_Group_Right( $this->env );
	}
}
