<?php
/**
 *	Singleton of module logic. 
 */
/**
 *	Singleton of module logic. 
 */
class Logic_Module {

	static protected $instance	= NULL;
	protected $env;
	protected $messenger;
	/**	@var	Model_Module	$model		Module module instance */
	public $model;
	
	const INSTALL_TYPE_UNKNOWN	= 0;
	const INSTALL_TYPE_LINK		= 1;
	const INSTALL_TYPE_COPY		= 2;

	protected function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Module( $env );
		$this->env->clock->profiler->tick( 'Logic_Module: init' );

		$moduleSource		= new Model_ModuleSource( $env );
		$this->sources		= $moduleSource->getAll( FALSE );
		$this->env->clock->profiler->tick( 'Logic_Module: get sources' );
		foreach( $this->model->loadSources() as $sourceId => $status )
			$this->sources[$sourceId]->status = $status;
		$this->env->clock->profiler->tick( 'Logic_Module: load sources' );

		foreach( $this->sources as $sourceId => $source ){
			if( isset( $source->status ) && !is_integer( $source->status ) ){
				if( $source->status instanceof Exception ){
					$this->messenger->noteFailure( $source->status->getMessage() );
				}
				$label	= '"'.$sourceId.'"';
				if( $this->env->getAcl()->has( 'admin/source', 'edit' ) )
					$label	= UI_HTML_Tag::create( 'a', $sourceId, array( 'href' => './admin/module/source/edit/'.$sourceId ) );
				$this->messenger->noteError( 'Die Quelle '.$label.' ist nicht verfügbar oder falsch konfiguriert.' );
			}
		}
		$this->env->clock->profiler->tick( 'Logic_Module: check sources' );
	}
	protected function __clone(){}

	public function checkForUpdate( $moduleId ){
		$module		= $this->model->get( $moduleId );
		if( $module && strlen( trim( $module->versionInstalled ) ) )
			if( version_compare( $module->versionAvailable, $module->versionInstalled ) )
				return $module->versionAvailable;
		return FALSE;
	}

	public function configureLocalModule( $moduleId, $pairs ){
		$fileName	= $this->env->pathApp.'config/modules/'.$moduleId.'.xml';
		$xml	= File_Reader::load( $fileName );
		$xml	= new XML_Element( $xml );
		foreach( $xml->config as $nr => $node ){
			$name	= $node->getAttribute( 'name' );
			if( array_key_exists( $name, $pairs ) )
				$node->setValue( $pairs[$name] );
		}
		return File_Writer::save( $fileName, $xml->asXml() );
	}

	protected function copyModuleFile( $moduleId, $fileIn, $fileOut, $force = FALSE ){
		$source		= $this->getSourceFromModuleId( $moduleId );
		$fileIn		= $source->path.str_replace( '_', '/', $moduleId ).'/'.$fileIn;
		$fileOut	= $this->env->pathApp.$fileOut;
#		$this->messenger->noteNotice( $fileIn." -> ".$fileOut );
		$pathNameIn		= realpath( $fileIn );
		if( !$pathNameIn )
			throw new Exception_Logic( 'Resource file is not existing', $fileIn, 20 );
		if( !is_readable( $pathNameIn ) )
			throw new Exception_Logic( 'Resource file is not readable', $fileIn, 21 );
		if( !is_writable( $pathNameIn ) )
			throw new Exception_Logic( 'Resource file is not executable', $fileIn, 22 );
		$pathOut	= dirname( $fileOut );
		if( !is_dir( $pathOut ) && !self::createPath( $pathOut ) )
			throw new Exception_Logic( 'Target path could not been created', $pathOut, 30 );
		if( file_exists( $fileOut ) ){
			if( !$force )
				throw new Exception_Logic( 'Target file is already existing', $fileOut, 31 );
			@unlink( $fileOut );
		}
		if( !copy( $pathNameIn, $fileOut ) )
			throw new Exception_Logic( 'Link failed', array( $fileIn, $fileOut ), 50 );
		chmod( $fileOut, 0770 );
		return TRUE;
	}

	/**
	 *	Creates a Path by creating all Path Steps.
	 *	@access		protected
	 *	@param		string		$path				Path to create
	 *	@return		void
	 */
	static protected function createPath( $path ){
		$dirname	= dirname( $path );
		if( file_exists( $path ) && is_dir( $path ) )
			return;
		$hasParent	= file_exists( $dirname ) && is_dir( $dirname );
		if( $dirname != "./" && !$hasParent )
			self::createPath( $dirname );
		return mkdir( $path, 02770, TRUE );
	}

	protected function executeSql( $sql ){
		if( !trim( $sql ) )
			throw new InvalidArgumentException( 'No SQL given' );
		$lines	= explode( "\n", trim( $sql ) );
		$cmds	= array();
		$buffer	= array();
		if( !$this->env->getRemote()->has( 'dbc' ) )
			throw new RuntimeException( 'Remvote environment has no database connection' );
		$dbc	= $this->env->getRemote()->getDatabase();
		$prefix	= $this->env->getRemote()->getDatabase()->getPrefix();								//  @todo use config of module Resource_Database instead
		while( count( $lines ) ){
			$line = array_shift( $lines );
			if( !trim( $line ) )
				continue;
			$buffer[]	= UI_Template::renderString( trim( $line ), array( 'prefix' => $prefix ) );
			if( preg_match( '/;$/', trim( $line ) ) ){
				$cmds[]	= join( "\n", $buffer );
				$buffer	= array();
			}
			if( !count( $lines ) && $buffer )
				$cmds[]	= join( "\n", $buffer ).';';
		}
		$state	= NULL;
		foreach( $cmds as $command ){
			$dbc->exec( $command );
#			$this->env->getMessenger()->noteNotice( 'DBC: '.$command );
		}
		return $state;
	}

	public function getCategories(){
		return $this->model->getCategories();
	}

	static public function getInstance( CMF_Hydrogen_Environment_Abstract $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Module( $env );
		return self::$instance;
	}

	public function getModule( $moduleId ){
		return $this->model->get( $moduleId );
	}

	public function getLocalFileTypePath( CMF_Hydrogen_Environment_Remote $env, $fileType, $file ){
		$config		= $env->getConfig();
		$paths		= $config->getAll( 'path.', TRUE );
		$pathTheme	= $paths->get( 'themes' ).$config->get( 'layout.theme' ).'/';
		switch( $fileType ){
			case 'class':
				return $paths->get( 'classes' ).$file->file;
			case 'locale':
			case 'template':
				return $paths->get( $fileType.'s' ).$file->file;
			case 'image':
				if( empty( $file->source ) || $file->source == 'local' )
					return $paths->get( 'images' ).$file->file;
				else if( $file->source == 'theme' ){
					if( !empty( $file->theme ) )
						return $paths->get( 'themes' ).$file->theme.'/img/'.$file->file;
					return $pathTheme.'img/'.$file->file;
				}
				break;
			case 'script':
				if( empty( $file->source ) || $file->source == 'local' )
					return $paths->get( 'scripts' ).$file->file;
				break;
			case 'style':
				if( empty( $file->source ) || $file->source == 'theme' ){
					if( !empty( $file->theme ) )
						return $paths->get( 'themes' ).$file->theme.'/css/'.$file->file;
					return $pathTheme.'css/'.$file->file;
				}
				break;
			default:
				return $file->file;
		}
	}

	public function getSourceFileTypePath( $fileType, $file ){
		switch( $fileType ){
			case 'class':
				return 'classes/'.$file->file;
			case 'locale':
				return 'locales/'.$file->file;
			case 'script':
				return 'js/'.$file->file;
			case 'template':
				return 'templates/'.$file->file;
			case 'style':
				return 'css/'.$file->file;
			case 'image':
				return 'img/'.$file->file;
			default:
				return $file->file;
		}
	}
	
	public function getModuleFileMap( CMF_Hydrogen_Environment_Remote $env, $module ){
		$map		= array();
		$fileTypes	= array(
			'classes'		=> 'class',
			'files'			=> 'file',
			'images'		=> 'image',
			'locales'		=> 'locale',
			'scripts'		=> 'script',
			'styles'		=> 'style',
			'templates'		=> 'template',
		);
		foreach( $fileTypes as $typeMember => $typeKey ){
			foreach( $module->files->$typeMember as $file ){
				$pathSource	= $this->getSourceFileTypePath( $typeKey, $file );
				$pathLocal	= $this->getLocalFileTypePath( $env, $typeKey, $file );
				if( $pathSource && $pathLocal )
					$map[$pathSource]	= $pathLocal;
			}
		}
		return $map;
	}

	public function getModuleFromSource( $moduleId, $source = NULL ){
		return $this->model->getFromSource( $moduleId, $source );
	}

	public function getModulePath( $moduleId ){
		return $this->model->getPath( $moduleId );
	}

	public function getSourceFromModuleId( $moduleId ){
		$module		= $this->getModule( $moduleId );
		if( !$module )
			throw new InvalidArgumentException( 'Module '.$moduleId.' not existing' );
		if( !$module->source )
			throw new InvalidArgumentException( 'Module '.$moduleId.' has no source attached' );
		$source		= $this->getSource( $module->source );
		if( !$source )
			throw new InvalidArgumentException( 'Module source '.$module->source.' is not existing' );
		return $source;
	}

	
/*	public function importLocalModule( $moduleId, $title, $description = NULL, $version = NULL, $route = NULL ){
		$path	= $this->getModulePath( $moduleId );
		if( !file_exists( $path ) )
			throw new RuntimeException( 'Path of module to import is not existing' );
	#	- create XML file
	#	- open XML file
	#	- scan classes 
	#	- append files in XML
	#	- write XML file
	}*/

	public function importLocalModuleToRepository( $moduleId, $sourceId, $move = NULL ){
		$config		= $this->env->getRemote()->getConfig();
		$messenger	= $this->env->getMessenger();
		$module		= $this->getModule( $moduleId );
		if( !$module )
			throw new RuntimeException( 'Invalid module ID: '.$moduleId );

#		print_m( $module );
#		remark( 'moduleId: '.$moduleId );
#		remark( 'sourceId: '.$sourceId );

#		$source		= $this->getSourceFromModuleId( $moduleId );
		$source		= $this->getSource( $sourceId );
#		print_m( $source );

		$pathTheme	= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		$path1		= $this->env->pathApp;
#		remark( 'path1: '.$path1 );
		$path2		= $source->path.str_replace( '_', '/', $moduleId ).'/';
#		remark( 'path2: '.$path2 );

		$types	= array(
			(object) array(
				'resources'		=> array( "module.xml" => (object) array( 'file' => $moduleId.'.xml' ) ),
				'pathSource'	=> 'config/modules/',
				'pathTarget'	=> '',
			),
			(object) array(
				'resources'		=> $module->files->classes,
				'pathSource'	=> 'classes/',
				'pathTarget'	=> 'classes/',
			),
			(object) array(
				'resources'		=> $module->files->templates,
				'pathSource'	=> $config->get( 'path.templates' ),
				'pathTarget'	=> 'templates/',
			),
			(object) array(
				'resources'		=> $module->files->locales,
				'pathSource'	=> $config->get( 'path.locales' ),
				'pathTarget'	=> 'locales/',
			),
			(object) array(
				'resources'		=> $module->files->styles,
				'pathSource'	=> $pathTheme.'css/',
				'pathTarget'	=> 'css/',
			),
			(object) array(
				'resources'		=> $module->files->scripts,
				'pathSource'	=> $config->get( 'path.scripts' ),
				'pathTarget'	=> 'js/',
			),
			(object) array(
				'resources'		=> $module->files->files,
				'pathSource'	=> '',
				'pathTarget'	=> '',
			),
		);

		$list	= array();
		foreach( $types as $type ){
			foreach( $type->resources as $targetFile => $resource ){
				if( !empty( $resource->source ) && $resource->source != 'theme' )
					continue;
				if( !is_string( $targetFile ) )
					$targetFile	= $resource->file;
				$list[$path1.$type->pathSource.$resource->file]	= $path2.$type->pathTarget.$targetFile;
				if( !file_exists( $path1.$type->pathSource.$resource->file ) )
					$messenger->noteError( 'Quelldatei "'.$type->pathSource.$resource->file.'" existiert nicht.' );
				if( file_exists( $path2.$type->pathTarget.$targetFile ) )
					$messenger->noteError( 'Zieldatei "'.$type->pathTarget.$targetFile.'" existiert bereits.' );
			}
		}
		if( !$messenger->gotError() ){
			try{
				foreach( $list as $source => $target ){
					$result	= self::saveFile( $target, File_Reader::load( $source ) );
					if( $result	&& $move )
						unlink( $source );
	#				$messenger->noteNotice( 'Copy: '.$source.' => '.$target );
				}
				$xml	= File_Reader::load( $path1.'config/modules/'.$moduleId.'.xml' );
				$result	= self::saveFile( $path2.'module.xml', $xml );
				if( $result	&& $move )
					unlink( $source );

				if( file_exists( $path1.'config/modules/'.$moduleId.'.png' ) ){
					$icon	= File_Reader::load( $path1.'config/modules/'.$moduleId.'.png' );
					$result	= self::saveFile( $path2.'icon.png', $icon );
					if( $result	&& $move )
						unlink( $source );
				}

				//  @todo	when $move = TRUE => remove database tables of module
				
				$messenger->noteSuccess( 'Das Module "'.$moduleId.'" wurde bei der Quelle "'.$sourceId.'" eingereicht.' );
				$messenger->noteNotice( 'Das Module kann nun deinstalliert und aus der Quelle wieder installiert werden.' );
				return TRUE;
			}
			catch( Exception $e ){
				foreach( $list as $target )
					unlink( $target );
			}
		}
		return FALSE;
	}
	
	public function installModule( $moduleId, $installType = 0, $settings = array(), $force = FALSE, $verbose = NULL ){
		try{
			$this->installModuleDatabase( $moduleId );
			$exceptions	= $this->installModuleFiles( $moduleId, $installType, $force, $verbose );
			if( !count( $exceptions ) ){																//  no error occured until now
				$this->configureLocalModule( $moduleId, $settings );								//  save given configuration values in local module
				$this->invalidateFileCache( $this->env->getRemote() );
				return TRUE;
			}
		}
		catch( Exception $e ){
			$exceptions	= array( $e->getMessage() );
		}
			
		if( count( $exceptions ) )																	//  several exceptions occured
			throw new Exception_Logic( 'Install failed', $exceptions, 2 );
		return FALSE;
	}

	protected function installModuleDatabase( $moduleId, $verbose = TRUE ){
		$module		= $this->model->get( $moduleId );
		if( $this->env->getRemote()->has( 'dbc' ) ){												//  remote environment has database connection
			$driver	= $this->env->getRemote()->getDatabase()->getDriver();							//  get PDO driver used on dabase connetion
			if( $driver ){																			//  remote database connection is configured
				if( strlen( trim( $module->sql['install@'.$driver] ) ) )							//  SQL for installation for specific PDO driver is given
					$this->executeSql( $module->sql['install@'.$driver] );							//  execute SQL
				else if( strlen( trim( $module->sql['install@*'] ) ) )								//  fallback: general SQL for installation is available
					$this->executeSql( $module->sql['install@*'] );									//  execute SQL
				else
					return NULL;
				return TRUE;
			}
		}
		return FALSE;
	}

	protected function installModuleFiles( $moduleId, $installType = 0, $force = FALSE, $verbose = TRUE ){
		$module		= $this->model->get( $moduleId );
		$pathModule	= $this->model->getPath( $moduleId );
		$pathApp	= $this->env->getRemote()->path;

		$files		= array( 'link' => array(), 'copy' => array() );
		$fileMap	= $this->getModuleFileMap( $this->env->getRemote(), $module );
		$listDone	= array();
		$exceptions	= array();

		if( $installType == self::INSTALL_TYPE_LINK )
			$files['link']	= $fileMap;
		else if( $installType == self::INSTALL_TYPE_COPY )
			$files['copy']	= $fileMap;
		else
			throw new InvalidArgumentException( 'Unknown installation type', 10 );

		$files['copy']['module.xml']	= 'config/modules/'.$moduleId.'.xml';
		if( file_exists( $pathModule.'config.ini' ) )
			$files['copy']['config.ini']	= 'config/modules/'.$moduleId.'.ini';

		foreach( $files as $type => $map ){
			foreach( $map as $fileIn => $fileOut ){
				$listDone[]	= $fileOut;
				try{
					if( $type == 'link' )														//  @todo: OS check -> no links in windows <7
						$this->linkModuleFile( $moduleId, $fileIn, $fileOut, $force );
					else
						$this->copyModuleFile( $moduleId, $fileIn, $fileOut, $force );
				}
				catch( Exception $e ){
					$exceptions[]	= $e;
				}
			}
		}
		if( count( $exceptions ) ){
			foreach( $listDone as $fileName )
				@unlink( $pathApp.$fileName );
		}
		return $exceptions;
	}

	public function invalidateFileCache( $env = NULL, $f = NULL ){
		if( $env->getConfig()->get( 'system.cache.modules' ) ){
			$fileCache	= $env->path.'config/modules.cache.serial';
			if(file_exists( $fileCache ) ){
				@unlink( $fileCache );
				$this->env->getMessenger()->noteNotice( 'Removed module cache file <small><code>'.$fileCache.'</code></small>.' );
			}
		}
	}

	protected function linkModuleFile( $moduleId, $fileIn, $fileOut, $force = FALSE ){
		$source		= $this->getSourceFromModuleId( $moduleId );
		$fileIn		= $source->path.str_replace( '_', '/', $moduleId ).'/'.$fileIn;
		$fileOut	= $this->env->pathApp.$fileOut;
		$pathNameIn	= realpath( $fileIn );
		if( !$pathNameIn )
			throw new Exception_Logic( 'Resource file is not existing', $fileIn, 20 );
		if( !is_readable( $pathNameIn ) )
			throw new Exception_Logic( 'Resource file is not readable', $fileIn, 21 );
		if( !is_executable( $pathNameIn ) )
			throw new Exception_Logic( 'Resource file is not executable', $fileIn, 22 );
		$pathOut	= dirname( $fileOut );
		if( !is_dir( $pathOut ) && !self::createPath( $pathOut ) )
			throw new Exception_Logic( 'Target path could not been created', $pathOut, 30 );
		if( file_exists( $fileOut ) ){
			if( !$force )
				throw new Exception_Logic( 'Target file is already existing', $fileOut, 31 );
			@unlink( $fileOut );
		}
		if( !symlink( $pathNameIn, $fileOut ) )
			throw new Exception_Logic( 'Link failed', array( $fileIn, $fileOut ), 50 );
		return TRUE;
	}

	public function updateModule( $moduleId, $settings = array(), $verbose = TRUE ){
		$this->uninstallModuleFiles( $moduleId, $verbose );
		$exceptions	= $this->installModuleFiles( $moduleId, $installType, $force, $verbose );
		if( !count( $exceptions ) ){
//			$this->configureLocalModule( $moduleId, $settings );
		}
		if( count( $exceptions ) )																	//  several exceptions occured
			throw new Exception_Logic( 'Install failed', $exceptions, 2 );
		return $exceptions;
	}

	protected function uninstallModuleDatabase( $moduleId, $verbose = TRUE ){
		$module		= $this->model->get( $moduleId );
		if( $this->env->getRemote()->has( 'dbc' ) ){												//  remote environment has database connection
			$driver	= $this->env->getRemote()->getDatabase()->getDriver();							//  get PDO driver used on dabase connetion
			if( $driver ){																			//  remote database connection is configured
				if( strlen( trim( $module->sql['uninstall@'.$driver] ) ) )							//  SQL for installation for specific PDO driver is given
					$this->executeSql( $module->sql['uninstall@'.$driver] );						//  execute SQL
				else if( strlen( trim( $module->sql['uninstall@*'] ) ) )							//  fallback: general SQL for installation is available
					$this->executeSql( $module->sql['uninstall@*'] );								//  execute SQL
			}
		}
	}

	protected function uninstallModuleFiles( $moduleId, $verbose = TRUE ){
		$configApp	= $this->env->getRemote()->getConfig();
		$pathApp	= $this->env->getRemote()->path;
		$module		= $this->model->get( $moduleId );
		$files		= array_values( $this->getModuleFileMap( $this->env->getRemote(), $module ) );
		
		//  --  CONFIGURATION  --  //
		$files[]	= 'config/modules/'.$moduleId.'.xml';
		if( file_exists( 'config/modules/'.$moduleId.'.ini' ) )
			$files[]	= 'config/modules/'.$moduleId.'.ini';
		$this->invalidateFileCache( $this->env->getRemote() );

		$folders	= array();
		$baseAppPaths	= $configApp->getAll( 'path.' );
		foreach( $files as $file ){
			@unlink( $path = $pathApp.$file );
			do{
				$path	= dirname( $path );
				$folder	= new Folder_Reader( $path );
				$count	= $folder->getNestedCount();
				if( !$count ){
					if( !in_array( basename( $path ).'/', $baseAppPaths ) ){
						Folder_Editor::removeFolder( $path );
						$folders[]	= substr( $path, strlen( $pathApp ) );
					}
				}
			}
			while( !$count );
		}
		if( ( 1 || $verbose ) ){
			if( $files ){
				$files	= '<ul><li>'.implode( '</li><li>', $files ).'</li></ul>';
				$this->messenger->noteNotice( 'Removed files: '.$files );
			}
			if( $folders ){
				$folders	= '<ul><li>'.implode( '</li><li>', $folders ).'</li></ul>';
				$this->messenger->noteNotice( 'Removed folders: '.$folders );
			}
		}
		return array( $files, $folders );
	}

	public function uninstallModule( $moduleId, $verbose = TRUE ){
		try{
			$this->uninstallModuleDatabase( $moduleId, $verbose );
			$this->uninstallModuleFiles( $moduleId, $verbose );
			return TRUE;
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( 'Failed: '.$e->getMessage() );
		}
		return FALSE;
	}

	/**
	 *	@todo		implement
	 */
	public function addSource(){
		
	}
	
	public function getSource( $sourceId ){
		if( !array_key_exists( $sourceId, $this->sources ) )
			throw new RuntimeException( 'Source "'.$sourceId.'" not existing' );
		return $this->sources[$sourceId];
	}
	
	/**
	 *	@todo		implement
	 */
	public function hasSource( $sourceId ){
		
	}
	
	/**
	 *	@todo		implement
	 */
	public function editSource( $sourceId ){
		
	}
	
	public function listSources(){
		$list	= array();
		$model	= new Model_ModuleSource( $this->env );
		foreach( $model->getAll( FALSE ) as $source )
			$list[$source->id]	= $source;
		return $list;
	}
	
	/**
	 *	@todo		implement
	 */
	public function removeSource( $sourceId ){
	}

	static protected function saveFile( $filePath, $content, $mode = 0777 ){
		Folder_Editor::createFolder( dirname( $filePath ), $mode );
		$e	= new File_Editor( $filePath );
		$e->writeString( $content );
		$e->setPermissions( 0777 );
	}
}
?>
