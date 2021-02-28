<?php
class Logic_Instance{

	protected $env;
	static protected $instance;

	protected function __construct( $env ){
		$this->env	= $env;
	}

	protected function __clone(){}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	public function listModules( $instanceId ){
		$cache	= $this->env->getCache();
		$list	= $cache->get( 'instance.'.$instanceId );
		if( !$list ){
			$this->env->setRemoteInstance( $instanceId );
			$env		= $this->env->getRemote();
			$list['installed']	= array();
			$list['missing']	= array();
			$list['updatable']	= array();
			$list['supported']	= array();
			if( $env->getModules() instanceof CMF_Hydrogen_Environment_Resource_Module_Library_Local ){
				$logic		= Logic_Module::getInstance( $this->env );
				$modulesAll	= $logic->model->getAll();
				$modules	= $env->getModules()->getAll();
				foreach( $modules as $module ){
					if( $module->isInstalled )
						$list['installed'][$module->id]	= $module;
					foreach( $module->relations->needs as $need )
						if( !array_key_exists( $need, $modules ) )
							$list['missing'][]	= $need;
					foreach( $module->relations->supports as $support )
						if( !array_key_exists( $support, $modules ) )
							$list['supported'][]	= $support;
					$version	= 0;
					if( isset( $modulesAll[$module->id] ) ){
						$version	= $modulesAll[$module->id]->versionAvailable;
						if( $module->versionInstalled && $version ){
							$modulesAll[$module->id];
							if( version_compare( $version, $module->versionInstalled ) > 0 )
								$list['updatable'][]	= $module->id;
						}
					}
				}
			}
			$cache->set( 'instance.'.$instanceId, $list );
		}
		return $list;
	}

/*	public function showTodos(){
		$index	= new FS_File_RecursiveTodoLister( array( 'php', 'js' ) );
		$index->scan( $this->env->getRemote()->path );
		$this->addData( 'path', $this->env->getRemote()->path );
		$this->addData( 'todos', $index->getList( TRUE ) );
		$this->env->getMessenger()->noteNotice( 'Scanned path "'.$this->env->getRemote()->path.'" for todos.' );
		$this->env->getMessenger()->noteNotice( $index->getNumberScanned().' Files scanned.' );
		$this->env->getMessenger()->noteNotice( $index->getNumberTodos().' Files found.' );
	}*/

/*	public function showInstanceModuleGraph( $instanceId = NULL, $showExceptions = NULL ){
		try{
			if( !UI_Image_Graphviz_Renderer::checkGraphvizSupport() )
				throw new InvalidArgumentException( "No GraphViz support detected" );

			$instanceId		= $this->env->getSession()->get( 'instanceId' );
			if( !$instanceId )
				throw new InvalidArgumentException( "No instance selected" );

*//*	--  SADLY this code breaks for some instances on creation of remove environment, so no support for requested instances :-(
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
*//*
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
	}*/
}
?>
