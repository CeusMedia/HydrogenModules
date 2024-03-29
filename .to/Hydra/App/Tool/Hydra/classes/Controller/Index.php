<?php

use CeusMedia\Common\FS\File\RecursiveTodoLister as RecursiveTodoFileLister;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\Image\Error as ErrorImage;
use CeusMedia\Common\UI\Image\Graphviz\Graph as GraphvizGraph;
use CeusMedia\Common\UI\Image\Graphviz\Renderer as GraphvizRenderer;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;

class Controller_Index extends Controller{

	/**	@var	Tool_Hydrogen_Setup_Environment		$env		Environment object */
	protected $env;

	static public function ___onCheckAccess( Environment $env, $module, $context, $data = [] ){
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
			$this->env->getRuntime()->reach( 'Index::index: init' );

			$listModulesMissing		= [];
			$listModulesPossible	= [];
			$listModulesUpdate		= [];
			$modulesInstalled		= [];

			$modulesAll				= $logicModule->model->getAll();
			$this->env->getRuntime()->reach( 'Index::index: get all' );
			if( $remote instanceof RemoteEnvironment ){
				$modulesInstalled		= $remote->getModules()->getAll();
				$this->env->getRuntime()->reach( 'Index::index: get installed' );

				foreach( $modulesInstalled as $module ){
					foreach( $module->relations->needs as $need )
						if( !array_key_exists( $need, $modulesInstalled ) )
							$listModulesMissing[]	= $need;
					foreach( $module->relations->supports as $support )
						if( !array_key_exists( $support, $modulesInstalled ) )
							$listModulesPossible[]	= $support;
				}
				$this->env->getRuntime()->reach( 'Index::index: get more' );

				foreach( $modulesInstalled as $module )
					if( $module->versionInstalled && $module->versionAvailable )
						if( version_compare( $module->versionAvailable, $module->versionInstalled ) > 0 )
							$listModulesUpdate[]	= $module;

				foreach( $listModulesMissing as $module ){
					$url	= './admin/module/installer/index/'.$module;
					$link	= HtmlTag::create( 'a', $module, ['href' => $url] );
					$span	= HtmlTag::create( 'span', $link, ['class' => 'icon module module-status-4'] );
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

		$this->env->getRuntime()->reach( 'Index::index: done' );
	}

	public function showTodos(){
		$index	= new RecursiveTodoFileLister( ['php', 'js'] );
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
				$options	= [
					'configFile'	=> $instance->uri.$pathConfig.$fileConfig,
					'pathApp'		=> $instance->uri
				];
				try{
					$remote		= new RemoteEnvironment( $options );
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

			$nodeOptions	= ['shape' => 'oval', 'style' => 'filled, rounded', 'fontsize' => 10, 'fillcolor' => 'gray90', 'color' => "gray60"];
			$edgeOptions1	= ['arrowsize' => 0.5, 'fontsize' => 8, 'fontcolor' => 'gray50', 'color' => 'gray40'];
			$edgeOptions2	= ['arrowsize' => 0.5, 'fontsize' => 8, 'fontcolor' => 'gray75', 'color' => 'gray50', 'style' => 'dashed'];

			$graph		= new GraphvizGraph( $instanceId, ['rankdir' => 'LR'] );
			foreach( $modules as $module )
				$graph->addNode( $module->id, ['label' => $module->title] + $nodeOptions );
			foreach( $modules as $module ){
				foreach( $module->relations->needs as $related )
					$graph->addEdge( $module->id, $related, ['label' => 'needs'] + $edgeOptions1 );
				foreach( $module->relations->supports as $related )
					if( array_key_exists( $related, $modules ) )
						$graph->addEdge( $module->id, $related, ['label' => 'supports'] + $edgeOptions2 );
			}
			$renderer	= new GraphvizRenderer( $graph );
			$renderer->printGraph( "svg" );
		}
		catch( Exception $e ){
			if( $showExceptions )
				UI_HTML_Exception_Page::display( $e );
			new ErrorImage( $e->getMessage() );
		}
		exit;
	}
}
?>
