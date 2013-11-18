<?php
class Controller_Admin_Module_Installer extends CMF_Hydrogen_Controller{							//  @todo	1) inherit from View_Admin_Module after cleanup

	/**	@var	Logic_Module									$logic		Module logic instance */
	protected $logic;
	/** @var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger	Messenger Object */
	protected $messenger;
	/**	@var	Net_HTTP_Request_Receiver						$request	HTTP Request Object */
	protected $request;

	protected $categories;

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Module::getInstance( $this->env );
		$this->categories	= $this->logic->getCategories();
		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
#		$this->env->getPage()->addThemeStyle( 'site.admin.module.installer.css' );
#		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.js' );	//  @todo	2) move to parent class after 1)
		if( !$this->env->getSession()->get( 'instanceId' ) ){
			$words	= $this->getWords( 'msg' );
			$this->messenger->noteError( $words['noInstanceSelected'] );
			$this->restart( 'admin/module/viewer' );
		}
	}

	protected function handleException( Exception_Logic $e ){
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

	public function index( $moduleId = NULL, $mainModuleId = NULL ){
		if( $moduleId )
			return $this->redirect( 'admin/module/installer', 'view', array( $moduleId, $mainModuleId ) );
		$this->addData( 'sources', $this->logic->listSources() );
		$this->addData( 'categories', $this->logic->getCategories() );
		$this->addData( 'modules', $this->logic->model->getAll() );
	}

	public function install( $moduleId, $mainModuleId = NULL ){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$module		= $this->logic->model->get( $moduleId );
		
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
					$this->restart( './admin/module/installer/'.$next.'/'.$moduleId );
				}
				else
					$this->restart( './admin/module/installer/'.$mainModuleId );
			}
			try{
				if( $request->get( 'type' ) == 'copy' ){
					$type	= Logic_Module::INSTALL_TYPE_COPY;
					if( $this->logic->installModule( $moduleId, $type, $settings, TRUE ) ){
						$messenger->noteSuccess( $words->moduleCopied, $moduleId );
						if( $mainModuleId )
							$this->restart( './admin/module/installer/install/'.$mainModuleId.'/'.$mainModuleId.'?doInstall=yes' );
						$this->restart( './admin/module/viewer/index/'.$moduleId );
					}
					$messenger->noteError( $words->moduleNotCopied, $moduleId );
				}
				else if( $request->get( 'type' ) == 'link' ){
					$type	= Logic_Module::INSTALL_TYPE_LINK;
					if( $this->logic->installModule( $moduleId, $type, $settings, TRUE ) ){
						$messenger->noteSuccess( $words->moduleLinked, $moduleId );
						if( $mainModuleId )
							$this->restart( './admin/module/installer/install/'.$mainModuleId.'/'.$mainModuleId.'?doInstall=yes' );
						$this->restart( './admin/module/viewer/index/'.$moduleId );
					}
					$messenger->noteError( $words->moduleNotLinked, $moduleId );
				}
				
			}
			catch( Exception $e ){
				$this->handleException( $e );
			}
		}
		$this->restart( $urlSelf );
	}
	
	public function uninstall( $moduleId, $verbose = TRUE ){
		$words		= (object) $this->getWords( 'msg' );
		$module		= $this->logic->getModule( $moduleId );
		if( !$module )
			$this->restart( './admin/module/editor' );
		if( $this->logic->uninstallModule( $moduleId, $verbose ) ){
			$this->messenger->noteSuccess( $words->moduleUninstalled, $module->title );
			if( $module->type == Model_Module::TYPE_CUSTOM )
				$this->restart( './admin/module/viewer' );
		}
		else
			$this->messenger->noteError( $words->moduleNotUninstalled, $module->title );
		$this->restart( './admin/module/viewer/index/'.$moduleId );
	}

	public function update( $moduleId, $verbose = TRUE ){

#		$modules	= array_keys( $this->env->getRemote()->getModules()->getAll() );
#		$moduleId	= $modules[array_rand( $modules )];
#		remark( "Module: ".$moduleId );
		
		$request	= $this->env->getRequest();
		$words		= (object) $this->getWords( 'msg' );
		$module		= $this->logic->getModule( $moduleId );
		$hasUpdate	= $this->logic->checkForUpdate( $moduleId );
		if( !$module )
			$this->restart( './admin/module/viewer' );
		if( !$hasUpdate )
			$this->restart( './admin/module/viewer/view/'.$moduleId );

		remark( "Module update in progress." );
		remark( "Module ID: ".$moduleId );
		remark( "Logic::getModule(ID): " );
#		$moduleLocal	= $this->env->getModules()->get( $moduleId );
		$moduleLocal	= $this->logic->getModule( $moduleId );
		$moduleSource	= $this->logic->getModuleFromSource( $moduleId );

		
		$files	= array();
		$fileTypes	= array(
			'classes'		=> 'class',
			'files'			=> 'file',
			'images'		=> 'image',
			'locales'		=> 'locale',
			'scripts'		=> 'script',
			'styles'		=> 'style',
			'templates'		=> 'template',
		);
		$files	= array();
		$pathSource		= $this->logic->model->getPath( $moduleId );
		$pathLocal		= $this->env->getRemote()->path;
		foreach( $fileTypes as $typeMember => $typeKey ){
			foreach( $module->files->$typeMember as $file ){
				$pathFileSource	= $this->logic->getSourceFileTypePath( $typeKey, $file );
				$pathFileLocal	= $this->logic->getLocalFileTypePath( $this->env->getRemote(), $typeKey, $file );
				if( $pathFileSource && $pathFileLocal ){

					$answer	= array();
					remark( "File Source: ". $pathSource.$pathFileSource );
					remark( "File Local: ". $pathLocal.$pathFileLocal );
					$cmd	= 'diff '.$pathSource.$pathFileSource.' '.$pathLocal.$pathFileLocal;
					remark( "Diff: ". $cmd );
					exec( $cmd, $answer, $code );
					print_m( $code );
					print_m( $answer );
					die;
					$cmd	= 'diff '.$pathSource.$pathFileSource.' '.$pathLocal.$pathFileLocal;
					exec( $cmd, $answer, $code );
					if( $code != 1 )
						throw new Exception( 'diff failed with return code '.$code );
					if( empty( $answer ) )
						$files[$typeKey.":".$file->file]	= NULL;
					else
						$files[$typeKey.":".$file->file]	= implode( "\n", $answer );
				}
			}
		}
		die;
		$files	= join( ", ", $files );
		print( '<div>
	<div style="float:left;width:50%;">
		<h3>LOKAL</h3>
		<b>Module</b>
		<div style="height: 300px; overflow: auto;">
			'.print_m( $moduleLocal, NULL, NULL, TRUE ).'
		</div>
	</div>
	<div style="float:left;width:50%;">
		<h3>SOURCE</h3>
		<b>Module</b>
		<div style="height: 300px; overflow: auto;">
			'.print_m( $moduleSource, NULL, NULL, TRUE ).'
		</div>
	</div>
	<b>Files</b>
	'.$files.'
</div>' );
		die;
		$module	= $this->logic->model->getLocal( $moduleId );
		remark( "Model::getLocal(ID): " );
		print_m( $module );
		die( "!" );




		if( $request->has( 'doUpdate' ) ){
			try{
				$this->logic->updateModule( $moduleId, $verbose );
			}
			catch( Exception $e ){
				$this->handleException( $e );
			}
			$this->messenger->noteSuccess( $words->updateInstalled, $module->versionInstalled, $hasUpdate );
			$this->restart( './admin/module/viewer/view/'.$moduleId );
		}
		$this->addData( 'hasUpdate', $hasUpdate );
		$this->addData( 'moduleId', $moduleId );
		$this->addData( 'module', $module );
		$this->addData( 'modulesInstalled', $this->logic->model->getInstalled() );
		$this->addData( 'modulesAvailable', $this->logic->model->getAvailable() );
	}

	public function view( $moduleId, $mainModuleId = NULL ){
		
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
	}
}
?>
