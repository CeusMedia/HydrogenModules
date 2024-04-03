<?php
/**
 *	Role Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Controller.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	Role Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Controller.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Admin_Role extends Controller
{
	public function add()
	{
		$words	= $this->env->getLanguage()->getWords( 'admin/role' );
		if( $this->env->getRequest()->getMethod()->isPost() ){
			$data		= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
			$title		= $data->get( 'title' );

			if( $title ){
				$model		= new Model_Role( $this->env );
				if( !$model->getByIndex( 'title', $data->get( 'title' ) ) ){
					$data				= $data->getAll();
					$data['createdAt']	= time();
					$roleId		= $model->add( $data );
					if( $roleId )
						$this->restart( './admin/role' );
				}
				else
					$this->env->messenger->noteError( 'role_title_existing' );

			}
			else
				$this->env->messenger->noteError( 'role_title_missing' );
		}

		$data	= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
		$this->addData( 'role', $data );
		$this->addData( 'words', $words );
	}

	public function addRight( $roleId )
	{
		$words		= $this->env->getLanguage()->getWords( 'admin/role' );
		$request	= $this->env->getRequest();
		if( $request->getMethod()->isPost() ){
			$controller	= $request->getFromSource( 'controller', 'POST' );
			$action		= $request->getFromSource( 'action', 'POST' );
			$data		= array(
				'roleId'		=> $roleId,
				'controller'	=> Model_Role_Right::minifyController( $controller ),
				'action'		=> $action,
				'timestamp'		=> time(),
			);
			$modelRight	= new Model_Role_Right( $this->env );
			$modelRight->add( $data );
		}
		$this->restart( './admin/role/edit/'.$roleId );
	}

	public function ajaxChangeRight( $roleId, $controller, $action )
	{
		if( $this->env->getRequest()->isAjax() ){
			$modelRight	= new Model_Role_Right( $this->env );
			$indices	= array(
				'roleId'		=> $roleId,
				'controller'	=> Model_Role_Right::minifyController( $controller ),
				'action'		=> $action
			);
			$right	= $modelRight->getByIndices( $indices );
			if( $right )
				$modelRight->remove( $right->roleRightId );
			else{
				$data	= array_merge( $indices, array( 'timestamp' => time() ) );
				$modelRight->add( $data );
			}
		}
		$right	= $modelRight->getByIndices( $indices );
		print( json_encode( (bool) $right ) );
		exit;
	}

	public function edit( $roleId = NULL )
	{
		if( empty( $roleId ) )
			throw new InvalidArgumentException( 'Invalid role id' );

		$words		= $this->env->getLanguage()->getWords( 'admin/role' );
		$messenger	= $this->env->getMessenger();

		$modelRole	= new Model_Role( $this->env );
		$role		= $modelRole->get( $roleId );

		if( $this->env->getRequest()->getMethod()->isPost() )
		{
			$data		= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
			$modelRole->edit( $roleId, $data );
			$this->restart( './admin/role' );
		}

		$modelRight	= new Model_Role_Right( $this->env );
		$orders		= ['controller' => 'ASC', 'action' => 'ASC'];
		$this->addData( 'rights', $modelRight->getAllByIndex( 'roleId', $roleId, $orders ) );

		$this->addData( 'roleId', $roleId );
		$this->addData( 'role', $role );
		$this->addData( 'words', $words );
	}

	public function index()
	{
		$model	= new Model_Role( $this->env );
		$roles	= $model->getAll();
		foreach( $roles as $role ){
			$model	= new Model_User( $this->env );
			$role->users	= $model->getAllByIndex( 'roleId', $role->roleId );
		}
		$this->addData( 'roles', $roles );
		$this->addData( 'hasRightToAdd', $this->env->getAcl()->has( 'admin_role', 'add' ) );
		$this->addData( 'hasRightToEdit', $this->env->getAcl()->has( 'admin_role', 'edit' ) );
	}

	public function remove( $roleId )
	{
		$words		= $this->env->getLanguage()->getWords( 'admin/role' );
		$messenger	= $this->env->getMessenger();

		$modelRole	= new Model_Role( $this->env );
		$role		= $modelRole->get( $roleId );

		$modelUser	= new Model_User( $this->env );
		if( $modelUser->getByIndex( 'roleId', $roleId ) ){
			$messenger->noteSuccess( $words['remove']['msgError-0'], $role->title );
			$this->restart( './admin/role/edit/'.$roleId );
		}

		$result		= $modelRole->remove( $roleId );
		if( $result ){
			$messenger->noteSuccess( $words['remove']['msgSuccess'], $role->title );
			$this->restart( './admin/role' );
		}else{
			$messenger->noteSuccess( $words['remove']['msgError-1'], $role->title );
			$this->restart( './admin/role/edit/'.$roleId );
		}
	}

	public function removeRight( $roleId, $controller, $action )
	{
		$modelRight	= new Model_Role_Right( $this->env );
		$indices	= array(
			'roleId'		=> $roleId,
			'controller'	=> Model_Role_Right::minifyController( $controller ),
			'action'		=> $action
		);
		$modelRight->removeByIndices( $indices );
		$this->restart( './admin/role/edit/'.$roleId );
	}
}
