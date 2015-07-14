<?php
class Tool_Hydrogen_Setup_Environment extends CMF_Hydrogen_Environment_Web{

	/**	@var	CMF_Hydrogen_Environment_Remote	$remote		Instance of remote environment */
	public $remote;

	/**	@var	array							$words		List of main language topics */
	public $words;

	public function __construct( $forceInstanceId = NULL ){

		self::$classRouter	= 'CMF_Hydrogen_Environment_Router_Recursive';
		self::$configFile	= "config/config.ini";

		date_default_timezone_set( "Europe/Berlin" );

		$this->detectSelf();

		$this->checkConfig();																		//  create main configuation if missing
		$this->checkInstances();																	//  create setup tool as first instance of none are defined yet
		$this->checkSources();																		//  create local cmFrameworks copy as first module source of none are defined yet
		$this->checkThemes();																		//  link in petrol theme is missing

		$this->pathConfig	= '';

		$pathModules	= CMF_PATH.'modules/Hydrogen/';												//  
		if( !preg_match( '/^\//', $pathModules ) )													//  module path is not absolute @todo kriss: remove
			$pathModules	= getEnv( 'DOCUMENT_ROOT' ).'/'.$pathModules;							//  prepend document root to module path @todo kriss: remove
		$this->pathModules	= $pathModules;															//  store module path @todo kriss: remove

		$this->path			= dirname( getEnv( 'SCRIPT_FILENAME' ) ).'/';
		if( isset( $options['pathApp'] ) )
			$this->path		= $options['pathApp'];													//	@todo: is this needed after migration of setup to CMF/Tools/Hydrogen ?

		$this->initClock();																			//  setup clock
		$this->initConfiguration();																	//  setup configuration
		$this->initModules();																		//  setup module support

		if( !$this->getModules()->has( 'Admin_Module_Sources' ) )									//  source administration module not installed yet
			require_once $pathModules.'Admin/Module/Sources/classes/Model/ModuleSource.php5';		//  load atleast module source model class
		if( !$this->getModules()->has( 'Admin_Instances' ) )										//  instance administration module not installed yet
			require_once $pathModules.'Admin/Instances/classes/Model/Instance.php5';				//  load atleast instance model class
		if( !$this->getModules()->has( 'Admin_Modules' ) ){											//  module administration module not installed yet
			require_once $pathModules.'Admin/Modules/classes/Model/Module.php5';					//  load atleast module model class
			require_once $pathModules.'Admin/Modules/classes/Logic/Module.php5';					//  and module logic class for installating missing modules
		}

		$this->initSession();																		//  setup session support
		$this->initMessenger();																		//  setup user interface messenger
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
		$this->initRequest();																		//  setup HTTP request handler
		$this->initResponse();																		//  setup HTTP response handler
		$this->initRouter();																		//  setup request router
		$this->initLanguage();																		//  setup language support
		$this->initPage();																			//  
		$this->initAcl();																			//  
		$this->initRemote( $this->request->get( 'forceInstanceId' ) );														//  
		$this->words	= $this->language->getWords( 'main' );
		$this->__onInit();																			//  
		$this->checkModules();																		//  try to install missing modules
	}

	protected function checkConfig(){
		if( file_exists( self::$configFile ) )														//  config file is existing
			return;																					//  
		if( !@copy( self::$configFile.'.dist', self::$configFile ) )									//  copy config file
			die( "Missing write permissions for config folder." );
//		$editor	= new File_INI_Editor( self::$configFile );											//  
//		$editor->setProperty( 'app.base.url', $this->url );											//  
	}

	protected function checkInstances(){
		$fileName	= 'config/instances.json';
		if( !file_exists( $fileName ) ){
			if( file_exists( 'config/instances.ini' ) ){
				$data	= parse_ini_file( 'config/instances.ini', TRUE );
				$data['Hydra']	= $data['Setup'];
				$data['Hydra']['uri']	= $this->uri;
				$data['Hydra']['path']	= $this->path;
				$data['Hydra']['host']	= $this->host;
				$data['Hydra']['protocol']	= $this->scheme.'://';
				unset( $data['Setup'] );
				File_Writer::save( $fileName, ADT_JSON_Formater::format( json_encode( $data ) ) );
				@rename( 'config/instances.ini', 'config/instances.ini.old' );
			}
			else
				File_Writer::save( $fileName, File_Reader::load( $fileName.'.dist' ) );
		}
		$data	= json_decode( File_Reader::load( $fileName ), TRUE );
		$self	= $data['Hydra'];
		if( !empty( $data['Hydra'] ) )
			if( !empty( $data['Hydra']['path'] ) )
				return;
		$data['Hydra']['path']	= dirname( getEnv( 'SCRIPT_NAME' ) ).'/';
		$data['Hydra']['uri']	= dirname( getEnv( 'SCRIPT_FILENAME' ) ).'/';
		$json	= ADT_JSON_Formater::format( json_encode( $data ) );
		File_Writer::save( $fileName, $json );
	}

	protected function checkModules(){
#		CMC_Loader::registerNew( 'php5', NULL, 'classes/' );
#		$modelSource	= new Model_ModuleSource( $this );
#		$modelInstance	= new Model_Instance( $this );
		$logic			= Logic_Module::getInstance( $this );
#		remark( "Sources:" );
#		print_m( array_keys( $modelSource->getAll( FALSE ) ) );
#		remark( "Instances:" );
#		print_m( array_keys( $modelInstance->getAll( FALSE ) ) );
#		remark( "Categories:" );
#		print_m( $logic->getCategories() );
#		remark( "Modules installed:" );
#		print_m( array_keys( $logic->model->getInstalled() ) );

		try{
			$modules	= array(
				'Resource_Library_cmModules'	=> array(
					'path'	=> CMF_PATH,
				),
				'Resource_Cache'			=> array(
					'type'		=> 'Folder',
					'resource'	=> 'tmp/cache/'
				),
				'Admin_Instances'			=> array(),
				'Admin_Modules'				=> array(),
				'Admin_Module_Sources'		=> array(),
				'Admin_Module_Installer'	=> array(),
				'Admin_Module_Editor'		=> array(),
				'Admin_Module_Creator'		=> array(),
				'UI_Helper_Content'			=> array(),
				'UI_CSS_Reset'				=> array(),
				'UI_DevLayers'				=> array(),
				'UI_Indicator'				=> array(),
				'JS_jQuery'					=> array(),
				'JS_jQuery_UI'				=> array(),
				'JS_Layer'					=> array(),
			);
			$list	= array();
			foreach( $modules as $moduleId => $settings )
				if( !$this->getModules()->has( $moduleId ) )
					$list[$moduleId]	= $settings;

			$countAll	= count( $modules );
			$countHave	= $countAll - count( $list );

			if( $list){
				foreach( $list as $moduleId => $settings){
					$module		= $logic->getModule( $moduleId );
					$type		= Logic_Module::INSTALL_TYPE_LINK;
					$message	= $this->words['msg']['moduleAutoInstalled'];
					$logic->installModule( $module->source, $moduleId, $type, $settings, TRUE );
					$this->messenger->noteNotice( $message, $module->title );
				}
				header( 'Location: '.$this->url.$this->request->get( '__path' ) );
				exit;
			}
		}
		catch( Exception_Logic $e ){
			if( $e->getCode() == 2 ){
				$messages	= array();
				foreach( $e->getSubject() as $exception ){
					if( $exception instanceof Exception_IO )
						$messages[]	= '<li>'.$exception->getMessage().': '.$exception->getResource().'</li>';
					else
						$messages[]	= '<li>'.$exception->getMessage().'</li>';
				}
				$this->messenger->noteFailure( $e->getMessage().":<br/><ul>".join( $messages ).'</ul>' );
			}
		}
		catch( Exception $e ){
			die( UI_HTML_Exception_Page::display( $e ) );
		}
		$this->clock->profiler->tick( 'env: check: modules' );
	}

	protected function checkSources(){
		$fileName	= 'config/modules/sources.json';
		if( !file_exists( $fileName ) ){
			if( file_exists( 'config/modules/sources.ini' ) ){
				$json	= json_encode( parse_ini_file( 'config/modules/sources.ini', TRUE ) );
				File_Writer::save( $fileName, ADT_JSON_Formater::format( $json ) );
				@rename( 'config/modules/sources.ini', 'config/modules/sources.ini.old' );
			}
			else
				copy( $fileName.'.dist', $fileName );
		}
		$data	= json_decode( File_Reader::load( $fileName ), TRUE );
		if( empty( $data['Local_CM_Public']['path'] ) ){
			$data['Local_CM_Public']['path']	= CMF_PATH.'modules/Hydrogen/';
			$json	= ADT_JSON_Formater::format( json_encode( $data ) );
			File_Writer::save( $fileName, $json );
		}
	}

	protected function checkThemes(){
		if( !file_exists( 'themes/petrol' ) ){
			$source	= CMF_PATH.'themes/Hydrogen/petrol';
			$target	= $this->uri.'themes/petrol';
			if( !file_exists( 'themes' ) )
				Folder_Editor::createFolder( 'themes', 0770 );
			if( !file_exists( 'themes/petrol' ) ){
				if( !file_exists( $source ) )
					throw new RuntimeException( 'Could not find Hydrogen theme "petrol" in '.$source );
				if( !symlink( $source, $target ) )
					throw new RuntimeException( 'Could not create link to petrol theme' );
			}
		}
	}

	public function getRemote(){
		return $this->remote;
	}

	public function setRemoteInstance( $instanceId ){
		$this->initRemote( $instanceId );
	}

	protected function initRemote( $forceInstanceId = NULL ){
		$messenger		= $this->getMessenger();
		$instance		= $this;
		$this->remote	= $this;																	//  use own environment by default

		if( class_exists( 'Model_Instance' ) ){														//  module for instance support is installed
			$model		= new Model_Instance( $this );												//  create model for reading instance settings
			$instances	= $model->getAll();															//  get all configured instances
			if( count( $instances ) == 1 )															//  only one instance is configured
				$instance	= array_pop( $instances );												//  get this instance's environment
			else if( $instances ){																	//  several instances are configured
				$sessionedId	= $this->session->get( 'instanceId' );								//  
				if( $forceInstanceId ){																//  an instance is forced
					if( !array_key_exists( $forceInstanceId, $instances ) )							//  but not configured
						throw new InvalidArgumentException( 'Forced instance "'.$forceInstanceId.'" is not existing' );
					$instance	=  $instances[$forceInstanceId];									//  get forced instance's environment
				}
				else if( $sessionedId ){															//  an instance has been selected before
					if( !array_key_exists( $sessionedId, $instances ) ){							//  but is not configured anymore
						$this->session->remove( 'instanceId' );										//  remove selected instance ID from session
						throw new InvalidArgumentException( 'Selected instance "'.$sessionedId.'" is not existing anymore' );
					}
					$instance	= $instances[$sessionedId];											//  get instance environment
				}
#				else{
#					$instance	=  $instances[array_shift( array_keys( $instances ) )];
#				}
			}
			$pathApp		= $instance->uri;
			$pathConfig		= !empty( $instance->configPath ) ? $instance->configPath : "config/";
			$fileConfig		= !empty( $instance->configFile ) ? $instance->configFile : "config.ini";

			if( !preg_match( '/^\//', $pathApp ) )
				$pathApp	= getEnv( 'DOCUMENT_ROOT' ).'/'.$pathApp;

			$options	= array(
				'configFile'	=> $pathApp.$pathConfig.$fileConfig,
				'pathApp'		=> $pathApp
			);
			try{
				$this->remote		= new CMF_Hydrogen_Environment_Remote( $options );
			}
			catch( Exception $e )
			{
				switch( $e->getCode() ){
					case 503:
						$this->getMessenger()->noteError( 'Access to application denied: Server load to high' );
						break;
					default:
						$this->getMessenger()->noteError( $e->getMessage() );
				}
				$this->remote		= new CMF_Hydrogen_Environment_Dummy( $options );
			}
			$this->pathApp		= $pathApp;
			$this->pathConfig	= $pathApp.$pathConfig;
		}
		$this->clock->profiler->tick( 'env: remote' );
	}

	protected function restart( $output = NULL ){
		if( !getEnv( 'HTTP_HOST' ) )
			die( "Restart is not implemented for console applications by now. Sorry." );
		if( !is_null( $output ) && strlen( $output ) ){
			$page	= new UI_HTML_PageFrame();
			$page->addBody( $output );
			$page->addMetaTag( 'http-equiv', 'refresh', 0 );
			print( $page->build() );
			flush();
		}
		else
			header( 'Location: ./' );
		exit;
	}
}
?>
