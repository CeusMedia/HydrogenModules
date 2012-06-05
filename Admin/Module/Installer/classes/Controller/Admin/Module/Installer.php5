<?php
class Controller_Admin_Module_Installer extends CMF_Hydrogen_Controller{							//  @todo	1) inherit from View_Admin_Module after cleanup

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Module( $this->env );
		$this->categories	= $this->logic->getCategories();
		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
#		$this->env->getPage()->addThemeStyle( 'site.admin.module.installer.css' );
		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.js' );	//  @todo	2) move to parent class after 1)
	}
	
	public function index( $moduleId = NULL, $mainModuleId = NULL ){
		if( $moduleId )
			return $this->redirect( 'admin/module/installer', 'view', array( $moduleId, $mainModuleId ) );
		$this->addData( 'sources', $this->logic->listSources() );
		$this->addData( 'categories', $this->logic->getCategories() );
		$this->addData( 'modules', $this->logic->model->getAll() );
	}

	public function view( $moduleId, $mainModuleId = NULL ){
		
		$module		= $this->logic->model->get( $moduleId );

#		$this->addData( 'allNeededModules', $this->logic->model->getAllNeededModules( $moduleId ) );
#		$this->addData( 'allSupportedModules', $this->logic->model->getAllSupportedModules( $moduleId ) );
		
		$module->neededModules		= $this->logic->model->getNeededModulesWithStatus( $moduleId );
		$module->supportedModules	= $this->logic->model->getSupportedModulesWithStatus( $moduleId );

		$solver	= new Solver( $this->logic );														//	calculator for module installation order
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

	public function install( $moduleId, $mainModuleId = NULL ){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$module		= $this->logic->model->get( $moduleId );
		
		$words		= $this->getWords( 'msg' );
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
				$solver	= new Solver( $this->logic );												//	calculator for module installation order
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
			catch( Exception_Logic $e ){
				$subject	= $e->getSubject();
				if( $e->getCode() !== 2 )
					$subject	= array( $e->getSubject() );
					
				foreach( $subject as $exception ){
					list( $s0, $s1 )	= (array) $exception->getSubject();
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
			}
			catch( Exception $e ){
				$messenger->noteFailure( 'Unbekannter Fehler: '.$e->getMessage() );
			}
				
		}
		$this->restart( $urlSelf );
	}
}
?>
