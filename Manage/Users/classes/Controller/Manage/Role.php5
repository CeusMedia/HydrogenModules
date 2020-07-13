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
class Controller_Manage_Role extends CMF_Hydrogen_Controller
{
	protected $modelRole;
	protected $modelRoleRight;
	protected $modelUser;
	protected $messenger;
	protected $language;
	protected $request;

	public function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->language			= $this->env->getLanguage();
		$this->modelRole		= $this->getModel( 'Role' );
		$this->modelRoleRight	= $this->getModel( 'Role_Right' );
		$this->modelUser		= $this->getModel( 'User' );
		$this->addData( 'modules', $this->env->getModules()->getAll() );
	}

	public function add()
	{
		$words	= $this->language->getWords( 'manage/role' );
		if( $this->request->getMethod()->isPost() ){
			$data		= $this->request->getAllFromSource( 'POST', TRUE );
			$title		= $data->get( 'title' );

			if( $title ){
				if( !$this->modelRole->getByIndex( 'title', $data->get( 'title' ) ) ){
					$roleId		= $this->modelRole->add( array_merge( $data->getAll(), array(
						'createdAt'		=> time(),
						'modifiedAt'	=> time(),
					) ) );
					if( $roleId )
						$this->restart( NULL, TRUE );
				}
				else
					$this->messenger->noteError( 'role_title_existing' );
			}
			else
				$this->messenger->noteError( 'role_title_missing' );
		}

		$data	= $this->request->getAllFromSource( 'POST', TRUE );
		$this->addData( 'role', new ADT_List_Dictionary( $data ) );
		$this->addData( 'words', $words );
	}

	public function addRight( int $roleId )
	{
		$words		= $this->language->getWords( 'manage/role' );
		if( $this->request->getMethod()->isPost() ){
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
		$this->restart( 'edit/'.$roleId, TRUE );
	}

	public function ajaxChangeRight( int $roleId, string $controller, string $action )
	{
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

	public function edit( int $roleId ) {
		$words		= $this->language->getWords( 'manage/role' );
		$role		= $this->modelRole->get( $roleId );

		if( $this->request->getMethod()->isPost() ){
			$data	= $this->request->getAllFromSource( 'POST' );
			$this->modelRole->edit( $roleId, $data );
			$this->restart( NULL, TRUE );
		}
		$orders		= array( 'controller' => 'ASC', 'action' => 'ASC' );
		$this->addData( 'rights', $this->modelRoleRight->getAllByIndex( 'roleId', $roleId, $orders ) );

		$this->addData( 'roleId', $roleId );
		$this->addData( 'role', $role );
		$this->addData( 'words', $words );
		$this->addData( 'userCount', $this->modelUser->countByIndex( 'roleId', $roleId ) );
		$disclosure	= new CMF_Hydrogen_Environment_Resource_Disclosure();
		$options	= array( 'classPrefix' => 'Controller_', 'readParameters' => FALSE );

		$list		= array();
		$actions	= $disclosure->reflect( 'classes/Controller/', $options );
		foreach( $actions as $controllerName => $controller ){
			$module	= $this->getModuleFromControllerClassName( $controllerName );
			if( !$module )																			//  controller without module
				continue;																			//  ignore for now
			$list[]	= (object) array(
				'name'			=> $controllerName,
				'className'		=> $controller->name,
				'methods'		=> $controller->methods,
				'module'		=> $module,
				'moduleWords'	=> $this->getModuleWords( $module ),
			);
		}
		$this->addData( 'actions', $disclosure->reflect( 'classes/Controller/', $options ) );
		$this->addData( 'controllerActions', $list );
		$this->addData( 'acl', $this->env->getAcl() );
		$this->addData( 'roleId', $roleId );
	}

	public function index()
	{
		$roles	= $this->modelRole->getAll();
		foreach( $roles as $role ){
			$role->users	= $this->modelUser->getAllByIndex( 'roleId', $role->roleId );
		}
		$this->addData( 'roles', $roles );
		$this->addData( 'hasRightToAdd', $this->env->getAcl()->has( 'manage_role', 'add' ) );
		$this->addData( 'hasRightToEdit', $this->env->getAcl()->has( 'manage_role', 'edit' ) );
	}

	public function remove( $roleId )
	{
		$words		= $this->language->getWords( 'manage/role' );
		$role		= $this->modelRole->get( $roleId );

		if( $this->modelUser->getByIndex( 'roleId', $roleId ) ){
			$this->messenger->noteSuccess( $words['remove']['msgError-0'], $role->title );
			$this->restart( 'edit/'.$roleId, TRUE );
		}

		$result		= $this->modelRole->remove( $roleId );
		if( $result ){
			$this->messenger->noteSuccess( $words['remove']['msgSuccess'], $role->title );
			$this->restart( NULL, TRUE );
		}
		else{
			$this->messenger->noteSuccess( $words['remove']['msgError-1'], $role->title );
			$this->restart( 'edit/'.$roleId, TRUE );
		}
	}

	public function removeRight( int $roleId, string $controller, string $action )
	{
		$indices	= array(
			'roleId'		=> $roleId,
			'controller'	=> Model_Role_Right::minifyController( $controller ),
			'action'		=> $action
		);
		$this->modelRoleRight->removeByIndices( $indices );
		$this->restart( 'edit/'.$roleId, TRUE );
	}

	//  --  PROTECTED  --  //

	protected function getModuleFromControllerClassName( string $controller )
	{
		$controllerPathName	= "Controller/".str_replace( "_", "/", $controller );
		foreach( $this->env->getModules()->getAll() as $module ){
			foreach( $module->files->classes as $file ){
				$path	= pathinfo( $file->file, PATHINFO_DIRNAME ).'/';
				$base	= pathinfo( $file->file, PATHINFO_FILENAME );
				if( $path.$base === $controllerPathName )
					return $module;
			}
		}
	}

	protected function getModuleWords( stdClass $module ): array
	{
		$path		= $this->env->getConfig()->get( 'path.locales' );
		$language	= $this->env->getLanguage()->getLanguage();
		$moduleKey	= $this->getSingular( str_replace( '_', '/', strtolower( $module->id ) ) );
		$localeFile	= $language.'/'.$moduleKey.'.ini';
		$moduleWords	= array();
		foreach( $module->files->locales as $locale ){
			if( $localeFile == $locale->file ){
				if( file_exists( $path.$locale->file ) ){
					$reader	= new FS_File_INI_Reader( $path.$locale->file, TRUE );
					if( $reader->usesSections() && $reader->hasSection( 'module' ) )
						return $reader->getProperties( TRUE, 'module' );
				}
			}
		}
		foreach( $module->files->locales as $locale ){
			if( file_exists( $path.$locale->file ) ){
				$reader	= new FS_File_INI_Reader( $path.$locale->file, TRUE );
				if( $reader->usesSections() && $reader->hasSection( 'module' ) )
					return $reader->getProperties( TRUE, 'module' );
			}
		}
		return array();
	}

	protected function getSingular( string $string ): string
	{
		if( preg_match( "/des$/", $string ) )
			$string	= preg_replace( "/des$/", "de", $string );
		else if( preg_match( "/ies$/", $string ) )
			$string	= preg_replace( "/ies$/", "y", $string );
		else if( preg_match( "/es$/", $string ) )
			$string	= preg_replace( "/es$/", "", $string );
		else if( preg_match( "/s$/", $string ) )
			$string	= preg_replace( "/s$/", "", $string );
		return $string;
	}
}
