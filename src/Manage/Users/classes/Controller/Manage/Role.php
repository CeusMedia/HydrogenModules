<?php
/**
 *	Role Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\INI\Reader as IniFileReader;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use Resource_Disclosure as Disclosure;

/**
 *	Role Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Manage_Role extends Controller
{
	protected HttpRequest $request;
	protected Model_Role $modelRole;
	protected Model_Role_Right $modelRoleRight;
	protected Model_User $modelUser;
	protected MessengerResource $messenger;
	protected LanguageResource $language;
	protected Logic_Role $logicRole;
	protected Logic_RoleRight $logicRoleRight;

	/**
	 *	@return		void
	 */
	public function add(): void
	{
		$words	= $this->language->getWords( 'manage/role' );
		if( $this->request->getMethod()->isPost() ){
			$data		= $this->request->getAllFromSource( 'POST', TRUE );
			$title		= $data->get( 'title' );

			if( $title ){
				if( !$this->logicRole->hasByTitle( $data->get( 'title' ) ) ){
					$roleId		= $this->logicRole->add(
						$data->get( 'title' ),
						$data->get( 'title' ),
						$data->get( 'access' ),
						$data->get( 'register' ),
					);
					$this->restart( 'edit/'.$roleId, TRUE );
				}
				else
					$this->messenger->noteError( 'role_title_existing' );
			}
			else
				$this->messenger->noteError( 'role_title_missing' );
		}

		$postDataArray	= $this->request->getAllFromSource( 'POST' );
		$this->addData( 'role', new Dictionary( $postDataArray ) );
		$this->addData( 'words', $words );
	}

	/**
	 *	@param		int|string		$roleId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addRight( int|string $roleId ): void
	{
		$words		= $this->language->getWords( 'manage/role' );
		if( $this->request->getMethod()->isPost() ){
			$controller	= $this->request->getFromSource( 'controller', 'POST' );
			$action		= $this->request->getFromSource( 'action', 'POST' );
			$this->logicRoleRight->add( $roleId, $controller, $action );
		}
		$this->restart( 'edit/'.$roleId, TRUE );
	}

	/**
	 *	@param		int|string		$roleId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $roleId ): void
	{
		$words		= $this->language->getWords( 'manage/role' );
		$role		= $this->logicRole->get( $roleId );

		if( $this->request->getMethod()->isPost() ){
			$data	= $this->request->getAllFromSource( 'POST' );
			$this->modelRole->edit( $roleId, $data );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'rights', $this->logicRoleRight->getRights( $roleId ) );
		$this->addData( 'roleId', $roleId );
		$this->addData( 'role', $role );
		$this->addData( 'words', $words );
		$this->addData( 'userCount', $this->modelUser->countByIndex( 'roleId', $roleId ) );

		$disclosure	= new Disclosure( $this->env );
		$options	= ['classPrefix' => 'Controller_', 'readParameters' => FALSE];

		$list		= [];
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

	public function index(): void
	{
		$roles	= $this->logicRole->getAll();
		foreach( $roles as $role ){
			$role->users	= $this->modelUser->getAllByIndex( 'roleId', $role->roleId );
			$role->nrRights	= $this->logicRoleRight->count( $role->roleId );
		}
		$this->addData( 'roles', $roles );
		$this->addData( 'hasRightToAdd', $this->env->getAcl()->has( 'manage_role', 'add' ) );
		$this->addData( 'hasRightToEdit', $this->env->getAcl()->has( 'manage_role', 'edit' ) );
	}

	/**
	 *	@param		int|string		$roleId
	 *	@return		void
	 */
	public function remove( int|string $roleId ): void
	{
		$words		= $this->language->getWords( 'manage/role' );
		$role		= $this->logicRole->get( $roleId );

		if( $this->modelUser->hasByIndex( 'roleId', $roleId ) ){
			$this->messenger->noteSuccess( $words['remove']['msgError-0'], $role->title );
			$this->restart( 'edit/'.$roleId, TRUE );
		}

		try{
			$this->logicRole->remove( $roleId );
			$this->messenger->noteSuccess( $words['remove']['msgSuccess'], $role->title );
			$this->restart( NULL, TRUE );
		}
		catch( Throwable ){
			$this->messenger->noteSuccess( $words['remove']['msgError-1'], $role->title );
			$this->restart( 'edit/'.$roleId, TRUE );
		}
	}

	/**
	 *	@param		int|string		$roleId
	 *	@param		string			$controller
	 *	@param		string			$action
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeRight( int|string $roleId, string $controller, string $action ): void
	{
		$this->logicRoleRight->remove( $roleId, $controller, $action );
		$this->restart( 'edit/'.$roleId, TRUE );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->language			= $this->env->getLanguage();
		$this->logicRoleRight	= Logic_RoleRight::getInstance( $this->env );
		$this->logicRole		= Logic_Role::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->modelRole		= $this->getModel( 'Role' );
		$this->modelUser		= Model_User::getInstance( $this->env );
		$this->addData( 'modules', $this->env->getModules()->getAll() );
	}

	/**
	 *	@param		string		$controller
	 *	@return		?object
	 */
	protected function getModuleFromControllerClassName( string $controller ): ?object
	{
		$controllerPathName	= 'Controller/'.str_replace( '_', '/', $controller );
		foreach( $this->env->getModules()->getAll() as $module ){
			foreach( $module->files->classes as $file ){
				$path	= pathinfo( $file->file, PATHINFO_DIRNAME ).'/';
				$base	= pathinfo( $file->file, PATHINFO_FILENAME );
				if( $path.$base === $controllerPathName )
					return $module;
			}
		}
		return NULL;
	}

	protected function getModuleWords( ModuleDefinition $module ): array
	{
		$path		= $this->env->getConfig()->get( 'path.locales' );
		$language	= $this->env->getLanguage()->getLanguage();
		$moduleKey	= $this->getSingular( str_replace( '_', '/', strtolower( $module->id ) ) );
		$localeFile	= $language.'/'.$moduleKey.'.ini';
		$moduleWords	= [];
		foreach( $module->files->locales as $locale ){
			if( $localeFile == $locale->file ){
				if( file_exists( $path.$locale->file ) ){
					$reader	= new IniFileReader( $path.$locale->file, TRUE );
					if( $reader->usesSections() && $reader->hasSection( 'module' ) )
						return $reader->getProperties( TRUE, 'module' );
				}
			}
		}
		foreach( $module->files->locales as $locale ){
			if( file_exists( $path.$locale->file ) ){
				$reader	= new IniFileReader( $path.$locale->file, TRUE );
				if( $reader->usesSections() && $reader->hasSection( 'module' ) )
					return $reader->getProperties( TRUE, 'module' );
			}
		}
		return [];
	}

	protected function getSingular( string $string ): string
	{
		if( str_ends_with( $string, 'des' ) )
			$string	= preg_replace( "/des$/", 'de', $string );
		else if( str_ends_with( $string, 'ies' ) )
			$string	= preg_replace( "/ies$/", 'y', $string );
		else if( str_ends_with( $string, 'es' ) )
			$string	= preg_replace( "/es$/", '', $string );
		else if( str_ends_with( $string, 's' ) )
			$string	= preg_replace( "/s$/", '', $string );
		return $string;
	}
}
