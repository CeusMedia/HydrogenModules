<?php
class Controller_Index extends CMF_Hydrogen_Controller{

	/**	@var	Tool_Hydrogen_Setup_Environment		$env		Environment object */
	protected $env;

	static public function ___onCheckAccess( $env, $module, $context, $data = array() ){
		$allowUnsecuredLocalhost	= !TRUE;
		$isAuthorized	= (bool) $env->getRequest()->getHeader( 'Authorization', FALSE );
		$isLocalhost	= getEnv( 'HTTP_HOST' ) === "localhost";
		$isSecured		= $isAuthorized || ( $isLocalhost && $allowUnsecuredLocalhost );
		if( !$isSecured ){
			$message	= 'This Hydra instance is not secured by HTTP Basic Authentication. Please fix this!';
			$env->getMessenger()->noteFailure( $message );
		}
	}
	
	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL ){
		if( $this->env->getRequest()->has( 'resetInstanceId' ) ){
			$this->env->getSession()->remove( 'instanceId' );
			$this->restart( NULL );
		}

		$logicInstance	= Logic_Instance::getInstance( $this->env );
		$modelInstance	= new Model_Instance( $this->env );
		$instances		= $modelInstance->getAll();
		foreach( $instances as $instanceId => $instance ){
			$instance->modules	= $logicInstance->listModules( $instanceId );
		}
		$instanceId		= $this->env->getSession()->get( 'instanceId' );

		$this->addData( 'instances', $instances );
		$this->addData( 'instanceId', $instanceId );

		if( $instanceId ){
			$remote			= $this->env->getRemote();
			$logicModule	= Logic_Module::getInstance( $this->env );
			$this->env->clock->profiler->tick( 'Index::index: init' );

			$listModulesMissing		= array();
			$listModulesPossible	= array();
			$listModulesUpdate		= array();
			$modulesInstalled		= array();

			$modulesAll				= $logicModule->model->getAll();
			$this->env->clock->profiler->tick( 'Index::index: get all' );
			if( $remote instanceof CMF_Hydrogen_Environment_Remote ){
				$modulesInstalled		= $remote->getModules()->getAll();
				$this->env->clock->profiler->tick( 'Index::index: get installed' );

				foreach( $modulesInstalled as $module ){
					foreach( $module->relations->needs as $need )
						if( !array_key_exists( $need, $modulesInstalled ) )
							$listModulesMissing[]	= $need;
					foreach( $module->relations->supports as $support )
						if( !array_key_exists( $support, $modulesInstalled ) )
							$listModulesPossible[]	= $support;
				}
				$this->env->clock->profiler->tick( 'Index::index: get more' );

				foreach( $modulesInstalled as $module )
					if( $module->versionInstalled && $module->versionAvailable )
						if( version_compare( $module->versionAvailable, $module->versionInstalled ) > 0 )
							$listModulesUpdate[]	= $module;

				foreach( $listModulesMissing as $module ){
					$url	= './admin/module/installer/index/'.$module;
					$link	= UI_HTML_Tag::create( 'a', $module, array( 'href' => $url ) );
					$span	= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'icon module module-status-4' ) );
					$this->env->getMessenger()->noteFailure( 'Modul '.$span.' ist nicht vollständig installiert.' );
				}
				$this->addData( 'remote', $remote );
				$this->addData( 'remoteConfig', $remote->getConfig() );
			}

			$this->addData( 'instance', $modelInstance->get( $instanceId ) );
			$this->addData( 'modulesAll', $modulesAll );
			$this->addData( 'modulesInstalled', $modulesInstalled );
			$this->addData( 'modulesMissing', $listModulesMissing );
			$this->addData( 'modulesPossible', $listModulesPossible );
			$this->addData( 'modulesUpdate', $listModulesUpdate );
		}

		$this->env->clock->profiler->tick( 'Index::index: done' );
	}

	public function showTodos(){
		$index	= new File_RecursiveTodoLister( array( 'php', 'js' ) );
		$index->scan( $this->env->getRemote()->path );
		$this->addData( 'path', $this->env->getRemote()->path );
		$this->addData( 'todos', $index->getList( TRUE ) );
		$this->env->getMessenger()->noteNotice( 'Scanned path "'.$this->env->getRemote()->path.'" for todos.' );
		$this->env->getMessenger()->noteNotice( $index->getNumberScanned().' Files scanned.' );
		$this->env->getMessenger()->noteNotice( $index->getNumberTodos().' Files found.' );
	}

	public function showInstanceModuleGraph( $instanceId = NULL, $showExceptions = NULL ){
		try{
			if( !UI_Image_Graphviz_Renderer::checkGraphvizSupport() )
				throw new InvalidArgumentException( "No GraphViz support detected" );

			$instanceId		= $this->env->getSession()->get( 'instanceId' );
			if( !$instanceId )
				throw new InvalidArgumentException( "No instance selected" );

/*	--  SADLY this code breaks for some instances on creation of remove environment, so no support for requested instances :-(
			if( $instanceId ){
				$model		= new Model_Instance( $this->env );
				$instance	= $model->get( $instanceId );
				if( !$instance )
					throw new InvalidArgumentException( "Invalid instance ID" );
				$pathConfig	= !empty( $instance->pathConfig ) ? $instance->pathConfig : 'config/';
				$fileConfig	= !empty( $instance->pathFile ) ? $instance->pathFile : 'config.ini';
				if( !file_exists( $instance->uri.$pathConfig.$fileConfig ) )
					throw new RuntimeException( 'Instance config file missing' );
				$options	= array(
					'configFile'	=> $instance->uri.$pathConfig.$fileConfig,
					'pathApp'		=> $instance->uri
				);
				try{
					$remote		= new CMF_Hydrogen_Environment_Remote( $options );
					$modules	= $remote->getModules()->getAll();
				}
				catch( Exception $e ){
					UI_HTML_Exception_Page::display( $e );
					exit;
				}
			}
			else
				$modules	= $this->env->remote->getModules()->getAll();
*/
			if( !$this->env->remote->getModules() )
				throw new RuntimeException( 'Instance has no modules' );
			$modules	= $this->env->remote->getModules()->getAll();
			ksort( $modules );

			$nodeOptions	= array( 'shape' => 'oval', 'style' => 'filled, rounded', 'fontsize' => 10, 'fillcolor' => 'gray90', 'color' => "gray60" );
			$edgeOptions1	= array( 'arrowsize' => 0.5, 'fontsize' => 8, 'fontcolor' => 'gray50', 'color' => 'gray40' );
			$edgeOptions2	= array( 'arrowsize' => 0.5, 'fontsize' => 8, 'fontcolor' => 'gray75', 'color' => 'gray50', 'style' => 'dashed' );

			$graph		= new UI_Image_Graphviz_Graph( $instanceId, array( 'rankdir' => 'LR' ) );
			foreach( $modules as $module )
				$graph->addNode( $module->id, array( 'label' => $module->title ) + $nodeOptions );
			foreach( $modules as $module ){
				foreach( $module->relations->needs as $related )
					$graph->addEdge( $module->id, $related, array( 'label' => 'needs' ) + $edgeOptions1 );
				foreach( $module->relations->supports as $related )
					if( array_key_exists( $related, $modules ) )
						$graph->addEdge( $module->id, $related, array( 'label' => 'supports' ) + $edgeOptions2 );
			}
			$renderer	= new UI_Image_Graphviz_Renderer( $graph );
			$renderer->printGraph( "svg" );
		}
		catch( Exception $e ){
			if( $showExceptions )
				UI_HTML_Exception_Page::display( $e );
			new UI_Image_Error( $e->getMessage() );
		}
		exit;
	}
}
?>
