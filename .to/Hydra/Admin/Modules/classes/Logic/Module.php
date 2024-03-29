<?php
/**
 *	Singleton of module logic.
 */

use CeusMedia\Common\FS\File\Backup as FileBackup;
use CeusMedia\Common\FS\File\Editor as FileEditor;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\FS\Folder\Reader as FolderReader;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\XML\Element as XmlElement;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;
use CeusMedia\HydrogenFramework\Logic;

/**
 *	Singleton of module logic.
 */
class Logic_Module extends Logic
{
	public $model;

	protected static $instance	= NULL;

	protected $env;
	protected $messenger;
	/**	@var	Model_Module	$model		Module module instance */

	const INSTALL_TYPE_UNKNOWN	= 0;
	const INSTALL_TYPE_LINK		= 1;
	const INSTALL_TYPE_COPY		= 2;

	public function checkForUpdate( string $moduleId )
	{
		$module		= $this->model->get( $moduleId );
		if( $module && strlen( trim( $module->versionInstalled ) ) )
			if( version_compare( $module->versionAvailable, $module->versionInstalled ) )
				return $module->versionAvailable;
		return FALSE;
	}

	public function configureLocalModule( string $moduleId, $pairs, /*int*/$installType = NULL, string $sourceId = NULL )
	{
		$fileName	= $this->env->pathApp.'config/modules/'.$moduleId.'.xml';
		$xml	= FileReader::load( $fileName );
		$xml	= new XmlElement( $xml );
		foreach( $xml->config as $nr => $node ){
			$name	= $node->getAttribute( 'name' );
			if( array_key_exists( $name, $pairs ) )
				$node->setValue( $pairs[$name] );
		}
		if( is_int( $installType ) && !is_null( $sourceId ) ){
			$xml->version->setAttribute( 'install-type', $installType );
			$xml->version->setAttribute( 'install-source', $sourceId );
			$xml->version->setAttribute( 'install-date', date( "c" ) );
		};
		return FileWriter::save( $fileName, $xml->asXml() );
	}

	public function getCategories(): array
	{
		return $this->model->getCategories();
	}

	public function getDatabaseScripts( string $moduleId, ?string $versionStart = NULL, ?string $versionTarget = NULL ): array
	{
		if( !$this->env/*->getRemote()*/->has( 'dbc' ) )												//  remote environment has no database connection
			return array();
		if( !( $driver = $this->env/*->getRemote()*/->getDatabase()->getDriver() ) )					//  no PDO driver set on database connection
			return array();
		$list			= [];
		$module			= $this->model->get( $moduleId );
		$moduleSource	= $this->model->getFromSource( $moduleId );
		$versionStart	= $versionStart ? $versionStart : $module->versionInstalled;
		$versionTarget	= $versionTarget ? $versionTarget : $module->versionAvailable;
		if( !$versionStart ){
			if( isset( $moduleSource->sql['install@'.$driver] ) ){										//  SQL for installation for specific PDO driver is given
				$versionStart	= $this->version( $moduleSource->versionAvailable );
				if( $moduleSource->sql['install@'.$driver]->version )
					$versionStart	= $this->version( $moduleSource->sql['install@'.$driver]->version );
				$list[]	= $moduleSource->sql['install@'.$driver];
			}
			else if( isset( $moduleSource->sql['install@*'] ) ){											//  fallback: general SQL for installation is available
				$versionStart	= $this->version( $moduleSource->versionAvailable );
				if( $moduleSource->sql['install@'.$driver]->version )
					$versionStart	=  $this->version($moduleSource->sql['install@'.$driver]->version );
				$list[]	= $moduleSource->sql['install@*'];
			}
		}
		foreach( $moduleSource->sql as $key => $sql ){													//  iterate module SQL parts
			if( $sql->event === "update" ){															//  found update
				$versionStep	= $this->version( $sql->version );									//  target version of sql part
				if( version_compare( $versionStep, $versionStart, '>' ) ){							//  sql part is newer than current version
					if( version_compare( $versionStep, $versionTarget, '<=' ) ){					//  sql part is older or related to new version
						$key	= 'update_'.$versionStart.'_'.$versionStep;							//  generate version key for list
						if( $sql->type == $driver ){												//  update SQL is for instance database driver
							$list[$key]	= (object) array(											//  enlist SQL to execute
								'type'		=> $driver,
								'event'		=> 'update',
								'version'	=> $versionStep,
								'sql'		=> trim( $sql->sql )
							);
						}
						else if( $sql->type === '*' && !isset( $list[$key] ) ){						//  update SQL is general and no master entry available
							$list[$key]	= (object) array(											//  enlist SQL to execute
								'type'		=> '*',
								'event'		=> 'update',
								'version'	=> $versionStep,
								'sql'		=> trim( $sql->sql )
							);
						}
						$versionStart	= $versionStep;
					}
				}
			}
		}
		return $list;
	}

/*	static public function getInstance( Environment $env ): self
	{
		if( !self::$instance )
			self::$instance	= new Logic_Module( $env );
		return self::$instance;
	}*/

	public function getLocalFileTypePath( Environment $env, string $fileType, $file ): string
	{
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

	public function getModule( string $moduleId )
	{
		return $this->model->get( $moduleId );
	}

	public function getModuleFileMap( RemoteEnvironment $env, $module ): array
	{
		$map		= [];
		$fileTypes	= [
			'classes'		=> 'class',
			'files'			=> 'file',
			'images'		=> 'image',
			'locales'		=> 'locale',
			'scripts'		=> 'script',
			'styles'		=> 'style',
			'templates'		=> 'template',
		];
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

	public function getModuleFromSource( string $moduleId, string $source = NULL )
	{
		return $this->model->getFromSource( $moduleId, $source );
	}

	public function getModulePath( string $moduleId ): ?string
	{
		return $this->model->getPath( $moduleId );
	}

	/**
	 *	@todo  			critical: apply paths from app base config
	 */
	public function getSourceFileTypePath( string $fileType, $file ): string
	{
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

	public function getSourceFromModuleId( string $moduleId )
	{
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

/*	public function importLocalModule( string $moduleId, string $title, string $description = NULL, $version = NULL, $route = NULL )
	{
		$path	= $this->getModulePath( $moduleId );
		if( !file_exists( $path ) )
			throw new RuntimeException( 'Path of module to import is not existing' );
	#	- create XML file
	#	- open XML file
	#	- scan classes
	#	- append files in XML
	#	- write XML file
	}*/

	public function importLocalModuleToRepository( string $moduleId, string $sourceId, bool $move = NULL ): bool
	{
		$config		= $this->env/*->getRemote()*/->getConfig();
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
				'resources'		=> array( "module.xml" => (object) ['file' => $moduleId.'.xml'] ),
				'pathSource'	=> 'config/modules/',
				'pathTarget'	=> '',
			),
			(object) [
				'resources'		=> $module->files->classes,
				'pathSource'	=> 'classes/',
				'pathTarget'	=> 'classes/',
			],
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
			(object) [
				'resources'		=> $module->files->styles,
				'pathSource'	=> $pathTheme.'css/',
				'pathTarget'	=> 'css/',
			],
			(object) array(
				'resources'		=> $module->files->scripts,
				'pathSource'	=> $config->get( 'path.scripts' ),
				'pathTarget'	=> 'js/',
			),
			(object) [
				'resources'		=> $module->files->files,
				'pathSource'	=> '',
				'pathTarget'	=> '',
			],
		);

		/*  --  IMAGES NEED SPECIAL TREATMENT  --  */
		$images	= (object) array( 'content' => array(), 'theme' => array() );						//  prepare image lists
		foreach( $module->files->images as $image ){												//  iterate noted imaged
			if( empty( $image->source ) )															//  no image source given
				$images->content[]	= $image;														//  append to content image list
			else if( $image->source === "theme" )													//  image is within theme
				$images->theme[]	= $image;														//  append to theme image list
		}
		if( $images->content )																		//  content images are noted
			$types[]	= (object) array(															//  add content images to file list
				'resources'		=> $images->content,												//  set image list
				'pathSource'	=> $config->get( 'path.images' ),									//  set source folder
				'pathTarget'	=> 'img/',															//  set target folder
			);
		if( $images->theme )																		//  theme images are noted
			$types[]	= (object) array(															//  add theme images to file list
				'resources'		=> $images->theme,													//  set image list
				'pathSource'	=> $pathTheme.'img/',												//  set source folder
				'pathTarget'	=> 'img/',															//  set target folder
			);

		$list	= [];
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
					$result	= self::saveFile( $target, FileReader::load( $source ) );
					if( $result	&& $move )
						unlink( $source );
	#				$messenger->noteNotice( 'Copy: '.$source.' => '.$target );
				}
				$xml	= FileReader::load( $path1.'config/modules/'.$moduleId.'.xml' );
				$result	= self::saveFile( $path2.'module.xml', $xml );
				if( $result	&& $move )
					unlink( $source );

				if( file_exists( $path1.'config/modules/'.$moduleId.'.png' ) ){
					$icon	= FileReader::load( $path1.'config/modules/'.$moduleId.'.png' );
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

	public function installModule( string $sourceId, string $moduleId, int $installType = 0, array $settings = [], bool $force = FALSE, bool $database = TRUE, bool $verbose = NULL ): bool
	{
		try{
			if( $database )
				$this->installModuleDatabase( $moduleId );
			$exceptions	= $this->installModuleFiles( $moduleId, $installType, $force, $verbose );
			if( !count( $exceptions ) ){															//  no error occured until now
				$this->configureLocalModule( $moduleId, $settings, $installType, $sourceId );		//  save given configuration values in local module
				$this->invalidateFileCache( $this->env/*->getRemote()*/ );
				return TRUE;
			}
		}
		catch( Exception $e ){
			$exceptions	= [$e];
			throw new Exception_Logic( 'Module installation failed', $exceptions, 2 );
		}
		return FALSE;
	}

	public function invalidateFileCache( Environment $env = NULL, bool $verbose = NULL )
	{
		if( $env->getConfig()->get( 'system.cache.modules' ) ){
			$fileCache	= $env->path.'config/modules.cache.serial';
			if(file_exists( $fileCache ) ){
				@unlink( $fileCache );
				if( $verbose )
					$this->env->getMessenger()->noteNotice( 'Removed module cache file <small><code>'.$fileCache.'</code></small>.' );
			}
		}
	}

	public function updateModule( string $moduleId, int $installType = 0, array $files = [], array $settings = [], bool $verbose = TRUE ): array
	{
		$exceptions	= $this->updateModuleFiles( $moduleId, $installType, $files, $verbose );
		if( !count( $exceptions ) ){
			try{
				$this->configureLocalModule( $moduleId, $settings, $installType );
				$this->updateModuleDatabase( $moduleId );
			}
			catch( Exception $e){
				$exceptions[]	= $e->getMessage();
			}
		}
		if( count( $exceptions ) )																	//  several exceptions occured
			throw new Exception_Logic( 'Module update failed', $exceptions, 2 );
		return $exceptions;
	}

	public function uninstallModule( string $moduleId, bool $database = TRUE, bool $verbose = TRUE ): bool
	{
		try{
			if( $database )
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
	public function addSource()
	{
	}

	/**
	 *	@todo		implement
	 */
	public function editSource( string $sourceId )
	{
	}

	public function getSource( string $sourceId )
	{
		if( !array_key_exists( $sourceId, $this->sources ) )
			throw new RuntimeException( 'Source "'.$sourceId.'" not existing' );
		return $this->sources[$sourceId];
	}

	/**
	 *	@todo		implement
	 */
	public function hasSource( string $sourceId )
	{
	}

	public function isInstalled( $moduleOrId ): bool
	{
		if( is_object( $moduleOrId ) )
			return in_array( (int) $moduleOrId->type, [
				Model_Module::TYPE_CUSTOM,
				Model_Module::TYPE_COPY,
				Model_Module::TYPE_LINK
			] );
		else if( $moduleOrId )
			return $this->isInstalled( $this->getModule ( $moduleOrId ) );
		return NULL;
	}

	public function listModulesMissing( string $instanceId ): array
	{
		$remote		= $this->env/*->getRemote()*/;
		$this->env->getRuntime()->reach( 'Logic_Module::list: got remote' );
		$list		= [];
		if( $remote instanceof RemoteEnvironment ){
			$modulesAll				= $this->model->getAll();
			$this->env->getRuntime()->reach( 'Logic_Module::list: got  all' );
			$modulesInstalled		= $remote->getModules()->getAll();
			$this->env->getRuntime()->reach( 'Logic_Module::list: got installed' );
			foreach( $modulesInstalled as $module )
				foreach( $module->relations->needs as $need )
					if( !array_key_exists( $need, $modulesInstalled ) )
						$list[$need]	= isset( $list[$need] ) ? $list[$need] + 1 : 1;
			$this->env->getRuntime()->reach( 'Logic_Module::list: got list' );
			arsort( $list );
		}
		return $list;
	}

	public function listModulesPossible( string $instanceId ): array
	{
		$remote		= $this->env/*->getRemote()*/;
		$this->env->getRuntime()->reach( 'Logic_Module::list: got remote' );
		$list		= [];
		if( $remote instanceof RemoteEnvironment ){
			$modulesAll				= $this->model->getAll();
			$this->env->getRuntime()->reach( 'Logic_Module::list: got  all' );
			$modulesInstalled		= $remote->getModules()->getAll();
			$this->env->getRuntime()->reach( 'Logic_Module::list: got installed' );
			foreach( $modulesInstalled as $module )
				foreach( $module->relations->supports as $support )
					if( !array_key_exists( $support, $modulesInstalled ) )
						$list[$support]	= isset( $list[$support] ) ? $list[$support] + 1 : 1;
			$this->env->getRuntime()->reach( 'Logic_Module::list: got list' );
			arsort( $list );
		}
		return $list;
	}

	public function listModulesOutdated( string $instanceId ): array
	{
		$remote		= $this->env/*->getRemote()*/;
		$this->env->getRuntime()->reach( 'Logic_Module::list: got remote' );
		$list		= [];
		if( $remote instanceof RemoteEnvironment ){
			$modulesAll				= $this->model->getAll();
			$this->env->getRuntime()->reach( 'Logic_Module::list: got  all' );
			$modulesInstalled		= $remote->getModules()->getAll();
			$this->env->getRuntime()->reach( 'Logic_Module::list: got installed' );
			foreach( $modulesInstalled as $module )
				if( $module->versionInstalled && $module->versionAvailable )
					if( version_compare( $module->versionAvailable, $module->versionInstalled ) > 0 )
						$list[$module]	= isset( $list[$module] ) ? $list[$module] + 1 : 1;
			$this->env->getRuntime()->reach( 'Logic_Module::list: got list' );
			arsort( $list );
		}
		return $list;
	}

	public function listSources( bool $activeOnly = FALSE ): array
	{
		$list	= [];
		$model	= new Model_ModuleSource( $this->env );
		return $model->getAll( $activeOnly );
	}

	/**
	 *	@todo		implement
	 */
	public function removeSource( string $sourceId )
	{
	}

	//  --  PROTECTED  --  //

	protected function __clone()
	{
	}

	protected function __onInit()
	{
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Module( $this->env );
		$this->env->getRuntime()->reach( 'Logic_Module: init' );

		$moduleSource		= new Model_ModuleSource( $this->env );
		$this->sources		= $moduleSource->getAll( FALSE );
		$this->env->getRuntime()->reach( 'Logic_Module: get sources' );
		foreach( $this->model->loadSources() as $sourceId => $status )
			$this->sources[$sourceId]->status = $status;
		$this->env->getRuntime()->reach( 'Logic_Module: load sources' );

		foreach( $this->sources as $sourceId => $source ){
			if( isset( $source->status ) && !is_integer( $source->status ) ){
				if( $source->status instanceof Exception ){
					$this->messenger->noteFailure( $source->status->getMessage() );
				}
				$label	= '"'.$sourceId.'"';
				if( $this->env->getAcl()->has( 'admin/source', 'edit' ) )
					$label	= HtmlTag::create( 'a', $sourceId, ['href' => './admin/module/source/edit/'.$sourceId] );
				$this->messenger->noteError( 'Die Quelle '.$label.' ist nicht verfügbar oder falsch konfiguriert.' );
			}
		}
		$this->env->getRuntime()->reach( 'Logic_Module: check sources' );
	}

	protected function copyModuleFile( string $moduleId, string $fileIn, string $fileOut, bool $force = FALSE ): bool
	{
		$source		= $this->getSourceFromModuleId( $moduleId );
		$fileIn		= $source->path.str_replace( '_', '/', $moduleId ).'/'.$fileIn;
		$fileOut	= $this->env->pathApp.$fileOut;
#		$this->messenger->noteNotice( $fileIn." -> ".$fileOut );
		$pathNameIn		= realpath( $fileIn );
		if( !$pathNameIn )
			throw new Exception_IO( 'Resource file is not existing', 20, $fileIn );
		if( !is_readable( $pathNameIn ) )
			throw new Exception_IO( 'Resource file is not readable', 21, $fileIn );
		if( !is_writable( $pathNameIn ) )
			throw new Exception_IO( 'Resource file is not executable', 22, $fileIn );
		$pathOut	= dirname( $fileOut );
		if( !is_dir( $pathOut ) && !self::createPath( $pathOut ) )
			throw new Exception_IO( 'Target path could not been created', 30, $pathOut );
		if( file_exists( $fileOut ) ){
			if( !$force )
				throw new Exception_IO( 'Target file is already existing', 31, $fileOut );
			@unlink( $fileOut );
		}
		if( !copy( $pathNameIn, $fileOut ) )
			throw new Exception_IO( 'Link failed', 50, [$fileIn, $fileOut] );
		chmod( $fileOut, 0770 );
		return TRUE;
	}

	/**
	 *	Creates a Path by creating all Path Steps.
	 *	@access		protected
	 *	@param		string		$path				Path to create
	 *	@return		void
	 */
	protected static function createPath( string $path )
	{
		$dirname	= dirname( $path );
		if( file_exists( $path ) && is_dir( $path ) )
			return;
		$hasParent	= file_exists( $dirname ) && is_dir( $dirname );
		if( $dirname != "./" && !$hasParent )
			self::createPath( $dirname );
		return mkdir( $path, 02770, TRUE );
	}

	/**
	 *	Executes SQL/DDL and returns number of executed statements.
	 *	You can provide complex collections of SQL statements, which will be chunked and executed separately.
	 *	@access		protected
	 *	@param		string		$sql		SQL statements
	 *	@return		integer					Number of executed SQL statements
	 *	@throws		RuntimeException		if remote environment/instance has no database resource
	 */
	protected function executeSql( string $sql ): int
	{
		if( !trim( $sql ) )
			return 0;
		$lines		= explode( "\n", trim( $sql ) );
		$statements	= [];
		$buffer		= [];
		if( !$this->env/*->getRemote()*/->has( 'dbc' ) )
			throw new RuntimeException( 'Remvote environment has no database connection' );
		$dbc	= $this->env/*->getRemote()*/->getDatabase();
		$prefix	= $this->env/*->getRemote()*/->getDatabase()->getPrefix();								//  @todo use config of module Resource_Database instead
		while( count( $lines ) ){
			$line = array_shift( $lines );
			if( !trim( $line ) )
				continue;
			$buffer[]	= UI_Template::renderString( trim( $line ), ['prefix' => $prefix] );
			if( preg_match( '/;$/', trim( $line ) ) ){
				$statements[]	= join( "\n", $buffer );
				$buffer			= [];
			}
			if( !count( $lines ) && $buffer )
				$statements[]	= join( "\n", $buffer ).';';
		}
		foreach( $statements as $statement )
			$dbc->exec( $statement );
		return count( $statements );
	}

	protected function installModuleDatabase( string $moduleId )
	{
		foreach( $this->getDatabaseScripts( $moduleId ) as $step ){
			$this->executeSql( $step->sql );																//  execute SQL
		}
	}

	protected function installModuleFiles( string $moduleId, int $installType = 0, bool $force = FALSE, bool $verbose = TRUE ): array
	{
		$module		= $this->model->get( $moduleId );
		$pathModule	= $this->model->getPath( $moduleId );
		$pathApp	= $this->env/*->getRemote()*/->path;

		if( !in_array( $installType, [self::INSTALL_TYPE_LINK, self::INSTALL_TYPE_COPY] ) )	//  unsupported install type
			throw new InvalidArgumentException( 'Unknown installation type', 10 );

		$files		= array( 'link' => array(), 'copy' => array() );
		$fileMap	= $this->getModuleFileMap( $this->env/*->getRemote()*/, $module );
		$listDone	= [];
		$exceptions	= [];

		$files[( $installType == self::INSTALL_TYPE_LINK ? 'link' : 'copy' )]	= $fileMap;

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

	protected function linkModuleFile( string $moduleId, string $fileIn, string $fileOut, bool $force = FALSE ): bool
	{
		$source		= $this->getSourceFromModuleId( $moduleId );
		$fileIn		= $source->path.str_replace( '_', '/', $moduleId ).'/'.$fileIn;
		$fileOut	= $this->env->pathApp.$fileOut;
		$pathNameIn	= realpath( $fileIn );
		if( !$pathNameIn )
			throw new Exception_IO( 'Resource file is not existing', 20, $fileIn );
		if( !is_readable( $pathNameIn ) )
			throw new Exception_IO( 'Resource file is not readable', 21, $fileIn );
		if( !is_executable( $pathNameIn ) )
			throw new Exception_IO( 'Resource file is not executable', 22, $fileIn );
		$pathOut	= dirname( $fileOut );
		if( !is_dir( $pathOut ) && !self::createPath( $pathOut ) )
			throw new Exception_IO( 'Target path could not been created', 30, $pathOut );
		if( file_exists( $fileOut ) ){
			if( !$force )
				throw new Exception_IO( 'Target file is already existing', 31, $fileOut );
			@unlink( $fileOut );
		}
		if( !symlink( $pathNameIn, $fileOut ) )
			throw new Exception_IO( 'Link failed', 50, [$fileIn, $fileOut] );
		return TRUE;
	}

	protected static function saveFile( string $filePath, string $content, $mode = 0777 )
	{
		FolderEditor::createFolder( dirname( $filePath ), $mode );
		$e	= new FileEditor( $filePath );
		$e->writeString( $content );
		$e->setPermissions( 0777 );
	}

	protected function uninstallModuleDatabase( string $moduleId, bool $verbose = TRUE )
	{
		$module		= $this->model->get( $moduleId );
		if( $this->env/*->getRemote()*/->has( 'dbc' ) ){												//  remote environment has database connection
			$driver	= $this->env/*->getRemote()*/->getDatabase()->getDriver();							//  get PDO driver used on dabase connetion
			if( $driver ){																			//  remote database connection is configured
				if( strlen( trim( $module->sql['uninstall@'.$driver]->sql ) ) )						//  SQL for installation for specific PDO driver is given
					$this->executeSql( $module->sql['uninstall@'.$driver]->sql );					//  execute SQL
				else if( strlen( trim( $module->sql['uninstall@*']->sql ) ) )						//  fallback: general SQL for installation is available
					$this->executeSql( $module->sql['uninstall@*']->sql );							//  execute SQL
			}
		}
	}

	protected function uninstallModuleFiles( string $moduleId, bool $verbose = TRUE ): array
	{
		$configApp	= $this->env/*->getRemote()*/->getConfig();
		$pathApp	= $this->env/*->getRemote()*/->path;
		$module		= $this->model->get( $moduleId );
		$files		= array_values( $this->getModuleFileMap( $this->env/*->getRemote()*/, $module ) );

		//  --  CONFIGURATION  --  //
		$files[]	= 'config/modules/'.$moduleId.'.xml';
		if( file_exists( 'config/modules/'.$moduleId.'.ini' ) )
			$files[]	= 'config/modules/'.$moduleId.'.ini';
		$this->invalidateFileCache( $this->env/*->getRemote()*/ );

		$folders	= [];
		$baseAppPaths	= $configApp->getAll( 'path.' );
		foreach( $files as $file ){
			if( $file === "config/config.ini" )														//  @todo	fix this hack
				continue;
			@unlink( $path = $pathApp.$file );
			if( file_exists( dirname( $path ) ) ){
				do{
					$path	= dirname( $path );
					$folder	= new FolderReader( $path );
					if( !( $count = $folder->getNestedCount() ) ){
						if( !in_array( basename( $path ).'/', $baseAppPaths ) ){
							FolderEditor::removeFolder( $path );
							$folders[]	= substr( $path, strlen( $pathApp ) );
						}
					}
				}
				while( !$count );
			}
		}
		if( $verbose ){
			if( $files ){
				$files	= '<ul><li>'.implode( '</li><li>', $files ).'</li></ul>';
				$this->messenger->noteNotice( 'Removed files: '.$files );
			}
			if( $folders ){
				$folders	= '<ul><li>'.implode( '</li><li>', $folders ).'</li></ul>';
				$this->messenger->noteNotice( 'Removed folders: '.$folders );
			}
		}
		return [$files, $folders];
	}

	protected function updateModuleDatabase( string $moduleId, bool $verbose = TRUE )
	{
		foreach( $this->getDatabaseScripts( $moduleId ) as $step ){
			$this->executeSql( $step->sql );														//  execute SQL
		}
	}

	protected function updateModuleFiles( string $moduleId, int $installType = 0, array $files = [], bool $verbose = TRUE ): array
	{
		$module		= $this->model->getFromSource( $moduleId );
		$pathApp	= $this->env/*->getRemote()*/->path;

		if( !in_array( $installType, [self::INSTALL_TYPE_LINK, self::INSTALL_TYPE_COPY] ) )	//  unsupported install type
			throw new InvalidArgumentException( 'Unknown installation type', 10 );

		$list	= [];																			//  prepare new module file list
		foreach( array_keys( (array) $module->files ) as $type )									//  iterate module file types
			$list[$type]	= [];																//  ...

		foreach( $files as $file ){																	//  iterate file selection
			$type	= $file->typeMember;															//  ...
			foreach( $module->files->$type as $moduleFile )											//  ...
				if( $moduleFile->file == $file->file->file )										//  ...
					$list[$type][]	= $moduleFile;													//  ...
		}
		$module->files	= (object) $list;															//  set new file list on module

		$fileMap	= $this->getModuleFileMap( $this->env/*->getRemote()*/, $module );
		$fileLists[( $installType == self::INSTALL_TYPE_LINK ? 'link' : 'copy' )]	= $fileMap;

		$listDone	= [];
		$exceptions	= [];
		$fileLists['copy']['module.xml']	= 'config/modules/'.$moduleId.'.xml';
		foreach( $fileLists as $type => $map ){
			foreach( $map as $fileIn => $fileOut ){
				$listDone[]	= $fileOut;
				try{
					if( file_exists( $pathApp.$fileOut ) ){
						$backup	= new FileBackup( $pathApp.$fileOut );
						$backup->store();
					}
					if( $type == 'link' )														//  @todo: OS check -> no links in windows <7
						$this->linkModuleFile( $moduleId, $fileIn, $fileOut, TRUE );
					else
						$this->copyModuleFile( $moduleId, $fileIn, $fileOut, TRUE );
				}
				catch( Exception $e ){
					$exceptions[]	= $e;
				}
			}
		}
		if( count( $exceptions ) ){																	//  there have been severe problems
			foreach( $listDone as $fileName ){														//  iterate list of updated files
				if( file_exists( $pathApp.$fileName ) ){											//  target file exists
					$backup	= new FileBackup( $pathApp.$fileName );								//  to target file under backup perspective
					if( $backup->getVersion() !== NULL )											//  there is atleast 1 backup of target file
						$backup->restore( -1, TRUE );												//  restore backup file
					else																			//  otherwise ...
						@unlink( $pathApp.$fileName );												//  ... remove file as is was new
				}
			}

		}
		return $exceptions;
	}

	protected function version( string $version ): string
	{
		return preg_replace( "/-pl?([0-9])/", ".0.\\1", $version );
	}
}
