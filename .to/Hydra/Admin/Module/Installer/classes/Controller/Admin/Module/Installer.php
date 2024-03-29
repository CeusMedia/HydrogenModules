<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Admin_Module_Installer extends Controller								//  @todo	1) inherit from View_Admin_Module after cleanup
{
	/**	@var	Logic_Module									$logic		Module logic instance */
	protected $logic;

	/** @var	Messenger		$messenger									Messenger Object */
	protected $messenger;

	/**	@var	Net_HTTP_Request_Receiver						$request	HTTP Request Object */
	protected $request;

	protected $categories;

	protected $frontendEnv;

	/**
	 *	@todo			critical: improve integration of diff library
	 */
	public function diff( string $hashFileLocal, string $hashFileSource )
	{
		if( !class_exists( 'Diff' ) )
			CMC_Loader::registerNew( 'php', NULL, '/var/www/lib/php-diff/lib/' );
		if( !class_exists( 'Diff' ) )
			throw new RuntimeException( 'Package "php-diff" is not installed.' );

		$fileLocal		= base64_decode( $hashFileLocal );
		$fileSource		= base64_decode( $hashFileSource );

		$this->addData( 'fileLocal', $fileLocal );
		$this->addData( 'fileSource', $fileSource );
	}

	public function index( string $moduleId = NULL, ?string $mainModuleId = NULL )
	{
		if( $moduleId )
			return $this->restart( 'view/'.$moduleId.'/'.$mainModuleId, TRUE );
		$this->addData( 'sources', $this->logic->listSources() );
		$this->addData( 'categories', $this->logic->getCategories() );
		$this->addData( 'modules', $this->logic->model->getAll() );
	}

	public function install( string $moduleId, ?string $mainModuleId = NULL, int $step = 0 )
	{
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$module		= $this->logic->model->get( $moduleId );

		if( $this->logic->isInstalled( $moduleId ) ){
			$this->messenger->noteNotice( 'Das Modul "'.$moduleId.'" ist bereits installiert. Weiterleitung zur Aktualisierung.' );
			$this->restart( 'update/'.$moduleId, TRUE );
		}

		$words		= (object) $this->getWords( 'msg' );
		$force		= $request->get( 'force' );
		$settings	= $request->get( 'config' );

		$urlSelf	= './admin/module/installer/'.$moduleId;
		if( $mainModuleId )
			$urlSelf	.= '/'.$mainModuleId;
		$module->neededModules		= $this->logic->model->getAllNeededModules( $moduleId );
		$module->supportedModules	= $this->logic->model->getAllSupportedModules( $moduleId );

		if( $request->get( 'doInstall' ) ){

			if( $request->get( 'force' ) )
				$mainModuleId	= $moduleId;

			if( $mainModuleId && $mainModuleId == $moduleId ){
				$solver	= new Logic_Module_Relation( $this->logic );												//	calculator for module installation order
				$solver->loadModule( $moduleId );													//  load module and related modules
				$order	= array_keys( $solver->getOrder() );										//  get calculated module installation order

				if( count( $order ) > 1 ){
					$next	= array_shift( $order );
#					remark( 'Restart: '.'./admin/module/installer/'.$next.'/'.$mainModuleId );
					$this->restart( 'install/'.$next.'/'.$moduleId.'/'.++$step, TRUE );
				}
				else if( $this->logic->model->isInstalled( $moduleId ) ){
					$messenger->noteSuccess( 'Fehlende Module wurden installiert. Nun zum Modul <b>'.$module->title.'</b>.' );
					$this->restart( './admin/module/viewer/view/'.$moduleId );
				}
				$this->restart( 'view/'.$mainModuleId.'/'.$step, TRUE );
			}
			try{
/*  --  kriss: new impl start */
				$urlInstaller	= './admin/module/installer/install/';
				if( $request->get( 'type' ) == 'copy' ){											//  mode: copy
					$type		= Logic_Module::INSTALL_TYPE_COPY;
					$msgSuccess	= $words->moduleCopied;
					$msgFailed	= $words->moduleNotCopied;
				}
				else if( $request->get( 'type' ) == 'link' ){										//  mode: link
					$type		= Logic_Module::INSTALL_TYPE_LINK;
					$msgSuccess	= $words->moduleLinked;
					$msgFailed	= $words->moduleNotLinked;
				}
				$database	= $request->has( 'database' );
				$force		= TRUE;																	//  assume forced file copy @todo make configurable
				if( $this->logic->installModule( $module->source, $moduleId, $type, $settings, $force, $database ) ){				//  try to install module by copy or link
					$messenger->noteSuccess( $msgSuccess, $moduleId );								//  success!
					if( !$mainModuleId ){															//  there is no parent module
						if( $step )
							$messenger->noteNotice( 'Fertig! Modul '.$module->title.' und '.$step.' weitere installiert.' );
						$this->restart( './admin/module/viewer/view/'.$moduleId );					//  go to module edit view
					}
					$url	= $urlInstaller.$mainModuleId.'/'.$mainModuleId.'/'.$step;				//  otherwise go to installer for parent module
					$instanceId	= $this->env->getSession()->get( 'instanceId' );
					$this->env->getCache()->remove( 'instance.'.$instanceId );
					$this->restart( $url.'?doInstall=yes' );										//  ??? @todo kriss: understand and document !!!
				}
				$messenger->noteError( $msgFailed, $moduleId );										//  still here? than error!
			}
			catch( Exception $e ){
				UI_HTML_Exception_Page::display( $e );
die;																								//  @todo handle exception without die
				$this->handleException( $e );
			}
		}
		$this->restart( $urlSelf );
	}

	public function uninstall( string $moduleId, bool $verbose = NULL )
	{
		$request	= $this->env->getRequest();
		$words		= (object) $this->getWords( 'msg' );
		$module		= $this->logic->getModule( $moduleId );
		$module->neededModules		= $this->logic->model->getNeededModulesWithStatus( $moduleId );
		$module->neededByModules	= $this->logic->model->getNeedingModulesWithStatus( $moduleId );
		$module->supportedModules	= $this->logic->model->getSupportedModulesWithStatus( $moduleId );
		$module->supportedByModules	= $this->logic->model->getSupportingModulesWithStatus( $moduleId );

 		if( !$module )
			$this->restart( './admin/module/editor' );
		if( $request->get( 'doUninstall' ) ){
			$database	= $request->get( 'database' );
			if( $this->logic->uninstallModule( $moduleId, $database, $verbose ) ){
				$this->messenger->noteSuccess( $words->moduleUninstalled, $module->title );
				$instanceId	= $this->env->getSession()->get( 'instanceId' );
				$this->env->getCache()->remove( 'instance.'.$instanceId );
				if( $module->type == Model_Module::TYPE_CUSTOM )
					$this->restart( './admin/module/viewer' );
				$this->restart( './admin/module/viewer/index/'.$moduleId );
			}
			$this->messenger->noteError( $words->moduleNotUninstalled, $module->title );
			$this->restart( 'uninstall/'.$moduleId, TRUE );
		}
		else{
			$module->neededModules		= $this->logic->model->getAllNeededModules( $moduleId );
			$module->supportedModules	= $this->logic->model->getAllSupportedModules( $moduleId );
			$this->addData( 'moduleId', $moduleId );
			$this->addData( 'module', $module );
			$this->addData( 'modules', $this->logic->model->getAll() );
		}
	}

	public function update( string $moduleId, bool $verbose = TRUE )
	{
		$request	= $this->env->getRequest();
		$words		= (object) $this->getWords( 'msg' );
		$hasUpdate	= $this->logic->checkForUpdate( $moduleId );

		$moduleLocal	= $this->logic->getModule( $moduleId );
		$moduleSource	= $this->logic->getModuleFromSource( $moduleId );

		foreach( $moduleSource->relations->needs as $module ){
			if( !$this->logic->isInstalled( $module ) ){
				$this->messenger->noteNotice( 'Das Modul "'.$module.'" wird benötigt. Weiterleitung zur Installation.' );
				$this->restart( 'view/'.$module.'/'.$moduleId, TRUE );
			}
		}

		if( !( $moduleLocal && $moduleSource ) )
			$this->restart( './admin/module/viewer' );
		if( !$hasUpdate )
			$this->restart( './admin/module/viewer/view/'.$moduleId );

		if( $request->has( 'doUpdate' ) ){
			$files		= $request->get( 'files' ) ? $request->get( 'files' ) : array();
			$settings	= $request->get( 'config' ) ? $request->get( 'config' ) : array();
			$installType	= $request->get( 'type' ) == 'link' ? Logic_Module::INSTALL_TYPE_LINK : Logic_Module::INSTALL_TYPE_COPY;
			foreach( $files as $nr => $file )
				$files[$nr]	= json_decode( base64_decode( $file ) );
			try{
				$this->logic->updateModule( $moduleId, $installType, $files, $settings, $verbose );
				$this->logic->invalidateFileCache( $this->env->getRemote() );
				$this->messenger->noteSuccess( $words->updateInstalled, $module->versionInstalled, $hasUpdate );
				$instanceId	= $this->env->getSession()->get( 'instanceId' );
				$this->env->getCache()->remove( 'instance.'.$instanceId );
				$this->restart( './admin/module/viewer/view/'.$moduleId );
			}
			catch( Exception_Logic $e ){
				$this->handleException( $e );
			}
			catch( Exception $e ){
				UI_HTML_Exception_Page::display( $e );
				exit;
			}
			$this->restart( 'update/'.$moduleId, TRUE );
		}

		$this->addData( 'files', $this->compareModuleFiles( $moduleId, ['/home/kriss/Web/' => '/var/www/'] ) );

		$this->addData( 'moduleLocal', $moduleLocal );
		$this->addData( 'moduleSource', $moduleSource );
		$this->addData( 'hasUpdate', $hasUpdate );
		$this->addData( 'moduleId', $moduleId );
		$this->addData( 'modulesInstalled', $this->logic->model->getInstalled() );
		$this->addData( 'modulesAvailable', $this->logic->model->getAvailable() );
		$this->addData( 'sql', $this->logic->getDatabaseScripts( $moduleId ) );
	}

	public function view( string $moduleId, ?string $mainModuleId = NULL )
	{
		$module		= $this->logic->model->get( $moduleId );

#		$this->addData( 'allNeededModules', $this->logic->model->getAllNeededModules( $moduleId ) );
#		$this->addData( 'allSupportedModules', $this->logic->model->getAllSupportedModules( $moduleId ) );

		$module->neededModules		= $this->logic->model->getNeededModulesWithStatus( $moduleId );
		$module->supportedModules	= $this->logic->model->getSupportedModulesWithStatus( $moduleId );

		$solver	= new Logic_Module_Relation( $this->logic );														//	calculator for module installation order
		$solver->loadModule( $moduleId );															//  load module and related modules
		$order	= $solver->getOrder();																//  get calculated module installation order

		$mainModule	= $module;
		if( $mainModuleId ){
			$mainModule	= $this->logic->getModule( $mainModuleId );
			$mainModule->neededModules	= $this->logic->model->getAllNeededModules( $mainModuleId );
			$mainModule->supportedModules	= $this->logic->model->getAllSupportedModules( $mainModuleId );
		}

		$this->addData( 'module', $module );
		$this->addData( 'moduleId', $moduleId );
		$this->addData( 'modules', $this->logic->model->getAll() );
		$this->addData( 'order', $order );
		$this->addData( 'moduleMap', $this->logic->model->getAll() );
		$this->addData( 'mainModuleId', $mainModuleId );
		$this->addData( 'mainModule', $mainModule );
		$this->addData( 'sqlScripts', $this->logic->getDatabaseScripts( $moduleId ) );
		$this->addData( 'files', $this->compareModuleFiles( $moduleId, ['/home/kriss/Web/' => '/var/www/'] ) );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Module::getInstance( $this->env );
		$this->categories	= $this->logic->getCategories();
		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
		$this->frontendEnv	= $this->getLogic( 'Frontend' )->getEnv();
#		$this->env->getPage()->addThemeStyle( 'site.admin.module.installer.css' );
#		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.js' );	//  @todo	2) move to parent class after 1)
		if( !$this->env->getSession()->get( 'instanceId' ) ){
			$words	= $this->getWords( 'msg' );
			$this->messenger->noteError( $words['noInstanceSelected'] );
			$this->restart( 'admin/module/viewer' );
		}
	}

	protected function compareModuleFiles( string $moduleId, array $pathLinks = [] )
	{
		$fileTypes	= [
			'classes'	=> 'class',
			'files'		=> 'file',
			'images'	=> 'image',
			'locales'	=> 'locale',
			'scripts'	=> 'script',
			'styles'	=> 'style',
			'templates'	=> 'template',
		];
		$files			= [];
		$moduleLocal	= $this->logic->getModule( $moduleId );

		$moduleSource	= $this->logic->getModuleFromSource( $moduleId );

		$envRemote		= $this->frontendEnv;
		$pathLocal		= $envRemote->path;
		$pathSource		= $this->logic->model->getPath( $moduleId );

		foreach( $fileTypes as $typeMember => $typeKey ){
			foreach( $moduleSource->files->$typeMember as $file ){
				$diff		= [];
				$status		= 0;
				$pathFileLocal	= $this->logic->getLocalFileTypePath( $envRemote, $typeKey, $file );
				$pathFileSource	= $this->logic->getSourceFileTypePath( $typeKey, $file );

				$source		= $pathSource.$pathFileSource;
				$target		= $pathLocal.$pathFileLocal;
				$fileSource	= isset( $file->source  ) ? strtolower( $file->source ) : NULL;
				if( in_array( $fileSource, ["url", "lib", "scripts-lib"] ) ){
					$status	= 5;
				}
				else if( $pathFileLocal && file_exists( $target ) ){
					$status			= 1;
					if( is_link( $target ) ){
						$source		= $this->resolveLinkedPath( $source, $pathLinks );
						$target		= readlink( $target );
						$target		= $this->resolveLinkedPath( $target, $pathLinks );
#						remark( 'Source: '.$source );
#						remark( 'Target: '.$target );
						$status		= $target === $source ? 3 : 4;
					}
					else{
						$cmd	= 'diff '.$source.' '.$target;
						exec( $cmd, $diff, $code );
						if( $code == 1 )
							$status	= 2;
					}
				}
				$files[]	= (object) [
					'moduleId'		=> $moduleId,
					'status'		=> $status,
					'file'			=> $file,
					'name'			=> $file->file,
					'typeMember'	=> $typeMember,
					'typeKey'		=> $typeKey,
					'pathLocal'		=> $target,
					'pathSource'	=> $source,
//					'diff'			=> $diff
				];
			}
		}
		return $files;
	}

	protected function handleException( Exception_Logic $e )
	{
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'msg' );
		if( $e instanceof Exception_Logic ){
			if( $e->getCode() !== 2 )
				$subject	= array( $e->getSubject() );
			else
				$subject	= array_merge( $e->getSubject(), array_fill( 0, 2, NULL ) );

			foreach( $subject as $exception ){
				if( is_string( $exception ) ){
					$messenger->noteFailure( 'Unbekannter Fehler: '.$exception );
				}
				else if( $exception instanceof Exception_IO ){
					list( $s0, $s1 )	= (array) $exception->getResource();
					switch( $exception->getCode() ){
						case 20:
							$messenger->noteError( $words->resourceMissing, $s0 );
							break;
						case 21:
							$messenger->noteError( $words->resourceNotReadable, $s0 );
							break;
						case 22:
							$messenger->noteError( $words->resourceNotExecutable, $s0 );
							break;
						case 30:
							$messenger->noteError( $words->pathNotCreatable, $s0 );
							break;
						case 31:
							$messenger->noteError( $words->targetExisting, $s0 );
							break;
						case 40:
							$messenger->noteError( $words->copyFailed, $s0, $s1 );
							break;
						case 50:
							$messenger->noteError( $words->linkFailed, $s0, $s1 );
							break;
						default:
							$messenger->noteFailure( 'Unbekannter Fehler ('.$exception->getCode().'): '.$exception->getMessage() );
							break;
					}
				}
				else if( $exception instanceof Exception ){
					$messenger->noteError( 'Fehler ('.$exception->getCode().'): '.$exception->getMessage() );
				}
			}
		}
		else{
			$messenger->noteFailure( 'Unbekannter Fehler: '.$e->getMessage() );
		}
	}

	protected function resolveLinkedPath( string $path, array $links = [] ): string
	{
		foreach( $links as $source => $target )
			if( preg_match( "/^".str_replace( "/", "\/", $source )."/", $path ) )
				$path	= str_replace( $source, $target, $path );
		return $path;
	}

}
