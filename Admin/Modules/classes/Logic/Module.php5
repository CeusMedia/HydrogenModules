<?php
class Logic_Module {

	protected $env;
	protected $messenger;
	/**	@var	Model_Module	$model		Module module instance */
	public $model;
	
	const INSTALL_TYPE_UNKNOWN	= 0;
	const INSTALL_TYPE_LINK		= 1;
	const INSTALL_TYPE_COPY		= 2;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Module( $env );

		$moduleSource		= new Model_ModuleSource( $env );
		$this->sources		= $moduleSource->getAll( FALSE );
		foreach( $this->model->loadSources() as $sourceId => $status )
			$this->sources[$sourceId]->status = $status;

		foreach( $this->sources as $sourceId => $source ){
			if( isset( $source->status ) && !is_integer( $source->status ) ){
#				if( $status instanceof Exception ){}
				$label	= '"'.$sourceId.'"';
				if( $this->env->getAcl()->has( 'admin/source', 'edit' ) )
					$label	= UI_HTML_Tag::create( 'a', $sourceId, array( 'href' => './admin/source/edit/'.$sourceId ) );
				$this->env->getMessenger()->noteError( 'Die Quelle '.$label.' ist nicht verfügbar oder falsch konfiguriert.' );
			}
		}
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
		$pathOut	= dirname( $fileOut );
		if( !is_dir( $pathOut ) && !self::createPath( $pathOut ) )
			throw new Exception_Logic( 'Resource file is not executable', $fileOut, 30 );
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

	public function getCategories(){
		return $this->model->getCategories();
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
				
				$messenger->noteSuccess( 'Das Module "'.$moduleId.'" wurde bei der Quelle "'.$source->id.'" eingereicht.' );
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

	protected function executeSql( $sql ){
		if( !trim( $sql ) )
			throw new InvalidArgumentException( 'No SQL given' );
		$lines	= explode( "\n", trim( $sql ) );
		$cmds	= array();
		$buffer	= array();
		if( !$this->env->getRemote()->has( 'dbc' ) )
			throw new RuntimeException( 'Remvote environment has no database connection' );
		$dbc	= $this->env->getRemote()->getDatabase();
		$prefix	= $this->env->getRemote()->getConfig()->get( 'database.prefix' );					//  @todo use config of module Resource_Database instead
		while( count( $lines ) ){
			$line = array_shift( $lines );
			if( !trim( $line ) )
				continue;
			$buffer[]	= UI_Template::renderString( trim( $line ), array( 'prefix' => $prefix ) );
			if( preg_match( '/;$/', trim( $line ) ) )
			{
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

	public function getModule( $moduleId ){
		return $this->model->get( $moduleId );
	}

	public function getModulePath( $moduleId ){
		return $this->model->getPath( $moduleId );
	}
	
	public function installModule( $moduleId, $installType = 0, $settings = array(), $force = FALSE, $verbose = NULL ){
		$config		= $this->env->getConfig();
		$messenger	= $this->env->getMessenger();
		$request	= $this->env->getRequest();
		$module		= $this->model->get( $moduleId );
		$pathModule	= $this->model->getPath( $moduleId );
		
		$configApp	= $this->env->getRemote()->getConfig();

		$pathTheme	= $configApp->get( 'path.themes' ).$configApp->get( 'layout.theme' ).'/';
		$pathImages	= 'images/';
		if( $configApp->get( 'path.images' ) )
			$pathImages	= $configApp->get( 'path.images' );
		$filesLink	= array();
		$filesCopy	= array();
		
		switch( $installType ){
			
			case self::INSTALL_TYPE_LINK:
				$array	= 'filesLink'; break;
			case self::INSTALL_TYPE_COPY:
				$array	= 'filesCopy'; break;
			default:
				throw new InvalidArgumentException( 'Unknown installation type', 10 );
		}
		foreach( $module->files->classes as $class )
			${$array}['classes/'.$class->file]	= 'classes/'.$class->file;
		foreach( $module->files->templates as $template )
			${$array}['templates/'.$template->file]	= $configApp->get( 'path.templates' ).$template->file;
		foreach( $module->files->locales as $locale )
			${$array}['locales/'.$locale->file]	= $configApp->get( 'path.locales' ).$locale->file;
		foreach( $module->files->scripts as $script )
			if( empty( $script->source ) || $script->source == 'local' )
				${$array}['js/'.$script->file]	= $configApp->get( 'path.scripts' ).$script->file;
		foreach( $module->files->styles as $style )
			if( empty( $style->source ) || $style->source == 'theme' )
				${$array}['css/'.$style->file]	= $pathTheme.'css/'.$style->file;
		foreach( $module->files->images as $image ){
			if( empty( $image->source ) || $image->source == 'local' )
				${$array}['img/'.$image->file]	= $pathImages.$image->file;
			else if( $image->source == 'theme' )
				${$array}['img/'.$image->file]	= $pathTheme.'img/'.$image->file;
		}
				
		$listDone	= array();
		$exceptions	= array();

		$filesCopy['module.xml']	= 'config/modules/'.$moduleId.'.xml';
		if( file_exists( $pathModule.'config.ini' ) )
			$filesCopy['config.ini']	= 'config/modules/'.$moduleId.'.ini';

		foreach( array( 'filesLink', 'filesCopy' ) as $type ){
			foreach( $$type as $fileIn => $fileOut ){
				$listDone[]	= $fileOut;
				try{
					if( $type == 'filesLink' )													//  @todo: OS check -> no links in windows <7
						$this->linkModuleFile( $moduleId, $fileIn, $fileOut, $force );
					else
						$this->copyModuleFile( $moduleId, $fileIn, $fileOut, $force );
				}
				catch( Exception $e ){
					$exceptions[]	= $e;
				}
			}
		}

		try{
			if( !count( $exceptions ) ){															//  no error occured until now
				//  --  SQL  --  //
				if( $this->env->getRemote()->has( 'dbc' ) ){										//  remote environment has database connection
					$driver	= $this->env->getRemote()->getDatabase()->getDriver();					//  get PDO driver used on dabase connetion
					if( $driver ){																	//  remote database connection is configured
						if( strlen( trim( $module->sql['install@'.$driver] ) ) )					//  SQL for installation for specific PDO driver is given
							$this->executeSql( $module->sql['install@'.$driver] );					//  execute SQL
						else if( strlen( trim( $module->sql['install@*'] ) ) )						//  fallback: general SQL for installation is available
							$this->executeSql( $module->sql['install@*'] );							//  execute SQL
					}
				}
				//  --  CONFIGURATION  --  //
				$this->configureLocalModule( $moduleId, $settings );								//  save given configuration values in local module
				return TRUE;
			}
		}
		catch( Exception $e ){
			$exceptions[]	= $e->getMessage();
		}

		if( count( $exceptions ) ){
			foreach( $listDone as $fileName )
				@unlink( $this->env->pathApp.$fileName );
			throw new Exception_Logic( 'Install failed', $exceptions, 2 );
		}
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
			throw new Exception_Logic( 'Resource file is not executable', $fileOut, 30 );
		if( file_exists( $fileOut ) ){
			if( !$force )
				throw new Exception_Logic( 'Target file is already existing', $fileOut, 31 );
			@unlink( $fileOut );
		}
		if( !symlink( $pathNameIn, $fileOut ) )
			throw new Exception_Logic( 'Link failed', array( $fileIn, $fileOut ), 50 );
		return TRUE;
	}

	public function uninstallModule( $moduleId, $verbose = TRUE ){
		$config		= $this->env->getConfig();
		$pathTheme	= $this->env->pathApp.$config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		$pathImages	= $this->env->pathApp.'images/';
		if( $config->get( 'path.images' ) )
			$pathImages	= $this->env->pathApp.$config->get( 'path.images' );
		$module		= $this->model->get( $moduleId );

		$files	= array();
#		try{
		//  --  FILES  --  //
		foreach( $module->files->classes as $class )
			$files[]	= $this->env->pathApp.'classes/'.$class->file;
		foreach( $module->files->templates as $template )
			$files[]	= $this->env->pathApp.$config->get( 'path.templates' ).$template->file;
		foreach( $module->files->locales as $locale )
			$files[]	= $this->env->pathApp.$config->get( 'path.locales' ).$locale->file;
		foreach( $module->files->scripts as $script )
			$files[]	= $this->env->pathApp.$config->get( 'path.scripts' ).$script->file;
		foreach( $module->files->styles as $style )
			if( empty( $style->source ) || $style->source == 'theme' )
				$files[]	= $pathTheme.'css/'.$style->file;
		foreach( $module->files->images as $image )
			if( empty( $image->source ) || $image->source == 'local' )
				$files[]	= $pathImages.$image->file;
			else if( $image->source == 'theme' )
				$files[]	= $pathTheme.'img/'.$image->file;

		//  --  CONFIGURATION  --  //
		$files[]	= $this->env->pathConfig.'modules/'.$moduleId.'.xml';
		if( file_exists( $this->env->pathConfig.'modules/'.$moduleId.'.ini' ) )
			$files[]	= $this->env->pathConfig.'modules/'.$moduleId.'.ini';

		try{
			//  --  SQL  --  //
			if( $this->env->getRemote()->has( 'dbc' ) ){											//  remote environment has database connection
				$driver	= $this->env->getRemote()->getDatabase()->getDriver();						//  get PDO driver used on dabase connetion
				if( $driver ){																		//  remote database connection is configured
					if( strlen( trim( $module->sql['uninstall@'.$driver] ) ) )						//  SQL for installation for specific PDO driver is given
						$this->executeSql( $module->sql['uninstall@'.$driver] );					//  execute SQL
					else if( strlen( trim( $module->sql['uninstall@*'] ) ) )						//  fallback: general SQL for installation is available
						$this->executeSql( $module->sql['uninstall@*'] );							//  execute SQL
				}
			}
			foreach( $files as $file )
				@unlink( $file );
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
