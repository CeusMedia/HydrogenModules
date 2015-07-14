<?php
/**
 *	Role Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Role Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class Controller_Manage_Role extends CMF_Hydrogen_Controller {

	protected $modelRole;
	protected $modelRoleRight;
	protected $modelUser;
	protected $messenger;
	protected $language;
	protected $request;

	public function __onInit(){
		$this->modelRole		= new Model_Role( $this->env );
		$this->modelRoleRight	= new Model_Role_Right( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->messenger		= $this->env->getMessenger();
		$this->language			= $this->env->getLanguage();
		$this->request			= $this->env->getRequest();
	}

	public function add() {
		$words	= $this->language->getWords( 'manage/role' );
		if( $this->request->getMethod() == 'POST' ){
			$data		= $this->request->getAllFromSource( 'POST' );
			$title		= $data->get( 'title' );

			if( $title ){
				if( !$this->modelRole->getByIndex( 'title', $data->get( 'title' ) ) ){
					$data				= $data->getAll();
					$data['createdAt']	= time();
					$roleId		= $this->modelRole->add( $data );
					if( $roleId )
						$this->restart( './manage/role' );
				}
				else
					$this->messenger->noteError( 'role_title_existing' );
			}
			else
				$this->messenger->noteError( 'role_title_missing' );
		}

		$data	= $this->request->getAllFromSource( 'POST' );
		$this->addData( 'role', $data );
		$this->addData( 'words', $words );
	}

	public function addRight( $roleId ) {
		$words		= $this->language->getWords( 'manage/role' );
		if( $this->request->getMethod() == 'POST' ){
			$controller	= $this->request->getFromSource( 'controller', 'POST' );
			$action		= $this->request->getFromSource( 'action', 'POST' );
			$data		= array(
				'roleId'		=> $roleId,
				'controller'	=> Model_Role_Right::minifyController( $controller ),
				'action'		=> $action,
				'timestamp'		=> time(),
			);
			$this->modelRoleRight->add( $data );
		}
		$this->restart( './manage/role/edit/'.$roleId );
	}

	public function ajaxChangeRight( $roleId, $controller, $action ){
		if( $this->request->isAjax() ){
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
		}
		$right	= $this->modelRoleRight->getByIndices( $indices );
		print( json_encode( (bool) $right ) );
		exit;
	}

	public function edit( $roleId = NULL ) {
		if( empty( $roleId ) )
			throw new InvalidArgumentException( 'Invalid role id' );

		$words		= $this->language->getWords( 'manage/role' );

		$role		= $this->modelRole->get( $roleId );

		if( $this->request->getMethod() == 'POST' )
		{
			$data		= $this->request->getAllFromSource( 'POST' )->getAll();
			$this->modelRole->edit( $roleId, $data );
			$this->restart( './manage/role' );
		}
		$orders		= array( 'controller' => 'ASC', 'action' => 'ASC' );
		$this->addData( 'rights', $this->modelRoleRight->getAllByIndex( 'roleId', $roleId, $orders ) );

		$this->addData( 'roleId', $roleId );
		$this->addData( 'role', $role );
		$this->addData( 'words', $words );
		$this->addData( 'userCount', $this->modelUser->countByIndex( 'roleId', $roleId ) );
	}

	public function index() {
		$roles	= $this->modelRole->getAll();
		foreach( $roles as $role ){
			$role->users	= $this->modelUser->getAllByIndex( 'roleId', $role->roleId );
		}
		$this->addData( 'roles', $roles );
		$this->addData( 'hasRightToAdd', $this->env->getAcl()->has( 'manage_role', 'add' ) );
		$this->addData( 'hasRightToEdit', $this->env->getAcl()->has( 'manage_role', 'edit' ) );
	}

	public function remove( $roleId ) {
		$words		= $this->language->getWords( 'manage/role' );
		$role		= $this->modelRole->get( $roleId );

		if( $this->modelUser->getByIndex( 'roleId', $roleId ) ){
			$this->messenger->noteSuccess( $words['remove']['msgError-0'], $role->title );
			$this->restart( './manage/role/edit/'.$roleId );
		}

		$result		= $this->modelRole->remove( $roleId );
		if( $result ){
			$this->messenger->noteSuccess( $words['remove']['msgSuccess'], $role->title );
			$this->restart( './manage/role' );
		}
		else{
			$this->messenger->noteSuccess( $words['remove']['msgError-1'], $role->title );
			$this->restart( './manage/role/edit/'.$roleId );
		}
	}

	public function removeRight( $roleId, $controller, $action ){
		$indices	= array(
			'roleId'		=> $roleId,
			'controller'	=> Model_Role_Right::minifyController( $controller ),
			'action'		=> $action
		);
		$this->modelRoleRight->removeByIndices( $indices );
		$this->restart( './manage/role/edit/'.$roleId );
	}
}
?>
