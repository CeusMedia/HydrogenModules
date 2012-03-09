<?php
/**
 *	Role Controller.
 *	@category		cmFrameworks.Modules.Hydrogen
 *	@package		Roles.Controller.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Role Controller.
 *	@category		cmFrameworks.Modules.Hydrogen
 *	@package		Roles.Controller.Admin
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class Controller_Admin_Role extends CMF_Hydrogen_Controller {

	public function add() {
		$words	= $this->env->getLanguage()->getWords( 'manage/role' );
		if( $this->env->getRequest()->getMethod() == 'POST' ){
			$data		= $this->env->getRequest()->getAllFromSource( 'POST' );
			$title		= $data->get( 'title' );

			if( $title ){
				$model		= new Model_Role( $this->env );
				if( !$model->getByIndex( 'title', $data->get( 'title' ) ) ){
					$data				= $data->getAll();
					$data['createdAt']	= time();
					$roleId		= $model->add( $data );
					if( $roleId )
						$this->restart( './manage/role' );
				}
				else
					$this->env->messenger->noteError( 'role_title_existing' );

			}
			else
				$this->env->messenger->noteError( 'role_title_missing' );
		}

		$data	= $this->env->getRequest()->getAllFromSource( 'POST' );
		$this->addData( 'role', $data );
		$this->addData( 'words', $words );
	}

	public function addRight( $roleId ) {
		$words		= $this->env->getLanguage()->getWords( 'manage/role' );
		$request	= $this->env->getRequest();
		if( $request->getMethod() == 'POST' ){
			$controller	= $request->getFromSource( 'controller', 'POST' );
			$action		= $request->getFromSource( 'action', 'POST' );
			$data		= array(
				'roleId'		=> $roleId,
				'controller'	=> $controller,
				'action'		=> $action,
				'timestamp'		=> time(),
			);
			$modelRight	= new Model_Role_Right( $this->env );
			$modelRight->add( $data );
		}
		$this->restart( './manage/role/edit/'.$roleId );
	}

	public function ajaxChangeRight( $roleId, $controller, $action ){
		if( $this->env->getRequest()->isAjax() ){
			$modelRight	= new Model_Role_Right( $this->env );
			$indices	= array(
				'roleId'		=> $roleId,
				'controller'	=> str_replace( '-', '/', strtolower( $controller ) ),
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
	
	public function edit( $roleId = NULL ) {
		if( empty( $roleId ) )
			throw new InvalidArgumentException( 'Invalid role id' );

		$words		= $this->env->getLanguage()->getWords( 'manage/role' );
		$messenger	= $this->env->getMessenger();

		$modelRole	= new Model_Role( $this->env );
		$role		= $modelRole->get( $roleId );

		if( $this->env->getRequest()->getMethod() == 'POST' )
		{
			$data		= $this->env->getRequest()->getAllFromSource( 'POST' )->getAll();
			$modelRole->edit( $roleId, $data );
			$this->restart( './manage/role' );
		}

		$modelRight	= new Model_Role_Right( $this->env );
		$orders		= array( 'controller' => 'ASC', 'action' => 'ASC' );
		$this->addData( 'rights', $modelRight->getAllByIndex( 'roleId', $roleId, $orders ) );

		$this->addData( 'roleId', $roleId );
		$this->addData( 'role', $role );
		$this->addData( 'words', $words );
	}

	public function index() {
		$model	= new Model_Role( $this->env );
		$roles	= $model->getAll();
		foreach( $roles as $role ){
			$model	= new Model_User( $this->env );
			$role->users	= $model->getAllByIndex( 'roleId', $role->roleId );
		}
		$this->addData( 'roles', $roles );
	}

	public function remove( $roleId ) {
		$words		= $this->env->getLanguage()->getWords( 'manage/role' );
		$messenger	= $this->env->getMessenger();

		$modelRole	= new Model_Role( $this->env );
		$role		= $modelRole->get( $roleId );

		$modelUser	= new Model_User( $this->env );
		if( $modelUser->getByIndex( 'roleId', $roleId ) ){
			$messenger->noteSuccess( $words['remove']['msgError-0'], $role->title );
			$this->restart( './manage/role/edit/'.$roleId );
		}

		$result		= $modelRole->remove( $roleId );
		if( $result ){
			$messenger->noteSuccess( $words['remove']['msgSuccess'], $role->title );
			$this->restart( './manage/role' );
		}else{
			$messenger->noteSuccess( $words['remove']['msgError-1'], $role->title );
			$this->restart( './manage/role/edit/'.$roleId );
		}
	}

	public function removeRight( $roleId, $controller, $action ){
		$modelRight	= new Model_Role_Right( $this->env );
		$indices	= array(
			'roleId'		=> $roleId,
			'controller'	=> str_replace( '-', '/', $controller ),
			'action'		=> $action
		);
		$modelRight->removeByIndices( $indices );
		$this->restart( './manage/role/edit/'.$roleId );
	}
}
?>