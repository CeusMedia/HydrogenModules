<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Admin_Module extends Controller
{
	public const INSTALL_TYPE_UNKNOWN	= 0;
	public const INSTALL_TYPE_LINK		= 1;
	public const INSTALL_TYPE_COPY		= 2;

	public const INSTALL_TYPES			= [
		self::INSTALL_TYPE_UNKNOWN,
		self::INSTALL_TYPE_LINK,
		self::INSTALL_TYPE_COPY,
	];

	protected Messenger $messenger;
	/**
	 *	@deprecated		replaced by module installer
	 *	@todo			remove
	 */
	public function copy( string $moduleId )
	{
		if( $this->installModule( $moduleId, self::INSTALL_TYPE_COPY ) )
			$this->env->getMessenger()->noteSuccess( 'Module "'.$moduleId.'" successfully copied.' );
		$this->restart( './admin/module/view/'.$moduleId );
	}

	public function index()
	{
		$model	= new Model_Module( $this->env );
		$this->addData( 'modules', $model->getAll() );
/*		$this->addData( 'modulesAvailable', $model->getAvailable() );
		$this->addData( 'modulesInstalled', $model->getInstalled() );
		$this->addData( 'modulesNotInstalled', $model->getNotInstalled() );
*/	}

	/**
	 * @deprecated	use Logic_Module::installModule instead
	 */
	public function installModule( string $moduleId, int $installType = 0, bool $verbose = NULL )
	{
		$config		= $this->env->getConfig();
		$model		= new Model_Module( $this->env );
		$module		= $model->get( $moduleId );
		$pathModule	= $model->getPath( $moduleId );
		$pathTheme	= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		$filesLink	= [];
		$filesCopy	= [];

		switch( $installType ){

			case self::INSTALL_TYPE_LINK:
				$array	= 'filesLink'; break;
			case self::INSTALL_TYPE_COPY:
				$array	= 'filesCopy'; break;
			default:
				throw new InvalidArgumentException( 'Unknown installation type' );
		}
		foreach( $module->files->classes as $class )
			${$array}['classes/'.$class]	= 'classes/'.$class;
		foreach( $module->files->templates as $template )
			${$array}['templates/'.$template]	= $config->get( 'path.templates' ).$template;
		foreach( $module->files->locales as $locale )
			${$array}['locales/'.$locale]	= $config->get( 'path.locales' ).$locale;
		foreach( $module->files->scripts as $script )
			${$array}['js/'.$script]	= $config->get( 'path.scripts' ).$script;
		foreach( $module->files->styles as $style )
			${$array}['css/'.$style]	= $pathTheme.'css/'.$style;
		$filesCopy['module.xml']	= 'config/modules/'.$moduleId.'.xml';
		if( file_exists( $pathModule.'config.ini' ) )
			$filesCopy['config.ini']	= 'config/modules/'.$moduleId.'.ini';

		$state		= NULL;
		$listDone	= [];
		foreach( ['filesLink', 'filesCopy'] as $type ){
			foreach( $$type as $fileIn => $fileOut ){
				if( $state !== FALSE ){
					if( $type == 'filesLink' )														//  @todo: OS check -> no links in windows <7
						$state	= $this->linkModuleFile( $moduleId, $fileIn, $fileOut );
					else
						$state	= $this->copyModuleFile( $moduleId, $fileIn, $fileOut );
					if( $state )
						$listDone[]	= $fileOut;
				}
			}
		}

		//  --  SQL  --  //
		if( $state !== FALSE ){
			$driver	= $this->env->dbc->getDriver();
			if( $driver && !empty( $module->sql['install@'.$driver] ) )
				$state	= $this->executeSql( $module->sql['install@'.$driver] );
			else if( !empty( $module->sql['install@*'] ) )
				$state	= $this->executeSql( $module->sql['install@*'] );
		}
		if( $state === FALSE )
			foreach( $listDone as $fileName )
				@unlink( $fileName );
		else if( $verbose ){
			$list	= '<ul><li>'.join( '</li><li>', $listDone ).'</li></ul>';
			$this->messenger->noteNotice( 'Installed: '.$list );
		}
		return $state !== FALSE;
	}

	/**
	 *
	 *	@deprecated		replaced by module installer
	 *	@todo			remove
	 */
	public function link( string $moduleId )
	{
		if( $this->installModule( $moduleId, self::INSTALL_TYPE_LINK ) )
			$this->messenger->noteSuccess( 'Module "'.$moduleId.'" successfully linked.' );
		else
			$this->messenger->noteError( 'Link to module "'.$moduleId.'" failed.' );
		$this->restart( './admin/module/view/'.$moduleId );
	}

	public function uninstall( string $moduleId, bool $verbose = TRUE )
	{
		$config		= $this->env->getConfig();
		$pathTheme	= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		$model		= new Model_Module( $this->env );
		$module		= $model->get( $moduleId );

		$files	= [];
#		try{
			//  --  FILES  --  //
			foreach( $module->files->classes as $class )
				$files[]	= 'classes/'.$class;
			foreach( $module->files->templates as $template )
				$files[]	= $config->get( 'path.templates' ).$template;
			foreach( $module->files->locales as $locale )
				$files[]	= $config->get( 'path.locales' ).$locale;
			foreach( $module->files->scripts as $script )
				$files[]	= $config->get( 'path.scripts' ).$script;
			foreach( $module->files->styles as $style )
				$files[]	= $pathTheme.'css/'.$style;

			//  --  CONFIG  --  //
			$files[]	= 'config/modules/'.$moduleId.'.xml';
			if( file_exists( 'config/modules/'.$moduleId.'.ini' ) )
				$files[]	= 'config/modules/'.$moduleId.'.ini';

			$state	= NULL;
			foreach( $files as $file )
				$state = @unlink( $file );

			if( $state !== FALSE ){
				//  --  SQL  --  //
				$driver	= $this->env->dbc->getDriver();
				$data	= ['prefix' => $config->get( 'database.prefix' )];
				$sql	= "";
				if( $driver && !empty( $module->sql['uninstall@'.$driver] ) )
					$sql	= \CeusMedia\Common\UI\Template::renderString( $module->sql['uninstall@'.$driver], $data );
				else if( !empty( $module->sql['uninstall@*'] ) )
					$sql	= \CeusMedia\Common\UI\Template::renderString( $module->sql['uninstall@*'], $data );
				if( $sql )
					$state = $this->executeSql( $sql );
			}

			if( $state )
				$this->messenger->noteSuccess(  'Module "'.$moduleId.'" successfully removed.' );
			else
				$this->messenger->noteSuccess(  'Module "'.$moduleId.'" successfully removed.' );
#		}
#		catch( Exception $e ){
#			$this->messenger->noteError( 'Failed: '.$e->getMessage() );
#		}
		$this->restart( './admin/module/view/'.$moduleId );
	}

	public function view( string $moduleId )
	{
		$model	= new Model_Module( $this->env );
		$this->addData( 'module', $model->get( $moduleId ) );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->messenger	= $this->env->getMessenger();
	}

	protected function copyModuleFile( string $moduleId, string $fileIn, string $fileOut ): bool
	{
		$pathModules	= $this->getModulesPath();
		$fileIn			= $pathModules.str_replace( '_', '/', $moduleId ).'/'.$fileIn;
		$pathNameIn		= realpath( $fileIn );
		if( !$pathNameIn ){
			$this->messenger->noteFailure( 'Resource "'.$fileIn.'" is missing.' );
			return FALSE;
		}
		if( !file_exists( $pathNameIn ) ){
			$this->messenger->noteFailure( 'Resource "'.$fileIn.'" is not existing.' );
			return FALSE;
		}
		if( !is_readable( $pathNameIn ) ){
			$this->messenger->noteFailure( 'Resource "'.$fileIn.'" is not readable.' );
			return FALSE;
		}
		$pathOut	= dirname( $fileOut );
		self::createPath( $pathOut );
		if( !is_dir( $pathOut ) ){
			$this->messenger->noteFailure( 'Path "'.$pathOut.'" is not creatable.' );
			return FALSE;
		}
		if( file_exists( $fileOut ) ){
			$this->messenger->noteFailure( 'Target "'.$fileOut.'" is already existing.' );
			return FALSE;
		}
		if( !copy( $pathNameIn, $fileOut ) ){
			$this->messenger->noteFailure( 'Link for "'.$fileOut.'" failed.' );
			return FALSE;
		}
		return TRUE;
	}

	/**
	 *	Creates a Path by creating all Path Steps.
	 *	@access		protected
	 *	@param		string		$path				Path to create
	 *	@return		bool
	 */
	protected static function createPath( string $path ): bool
	{
		$dirname	= dirname( $path );
		if( file_exists( $path ) && is_dir( $path ) )
			return TRUE;
		$hasParent	= file_exists( $dirname ) && is_dir( $dirname );
		if( $dirname != "./" && !$hasParent )
			self::createPath( $dirname );
		return mkdir( $path, 02770, TRUE );
	}

	protected function executeSql( string $sql ): bool
	{
		$lines		= explode( "\n", trim( $sql ) );
		$commands	= [];
		$buffer		= [];
		if( !$this->env->has( 'dbc' ) )
			return FALSE;
		$prefix	= $this->env->getConfig()->get( 'database.prefix' );
		while( count( $lines ) ){
			$line = array_shift( $lines );
			if( !trim( $line ) )
				continue;
			$buffer[]	= \CeusMedia\Common\UI\Template::renderString( trim( $line ), ['prefix' => $prefix] );
			if( preg_match( '/;$/', trim( $line ) ) )
			{
				$commands[]	= join( "\n", $buffer );
				$buffer	= [];
			}
			if( !count( $lines ) && $buffer )
				$commands[]	= join( "\n", $buffer ).';';
		}
		$state	= NULL;
		foreach( $commands as $command ){
			error_log( nl2br( $command )."\n", 3, 'a.log' );
			if( $state !== FALSE ){
				try{
					$this->env->getDatabase()->exec( $command );
					$state	= TRUE;
				}
				catch( Exception $e ){
					$state	= FALSE;
					$this->messenger->noteFailure( $e->getMessage() );
				}
			}
		}
		return $state;
	}

	protected function getModulesPath(): string
	{
		$config		= $this->env->getConfig();
		$path		= $config->get( 'module.admin_modules.path' );
		if( $path )
			return $path;
		throw new RuntimeException( 'No module path defined in module configuration' );
	}

	protected function linkModuleFile( string $moduleId, string $fileIn, string $fileOut ): bool
	{
		$path		= $this->getModulesPath();
		$fileIn		= $path.str_replace( '_', '/', $moduleId ).'/'.$fileIn;
		$pathNameIn	= realpath( $fileIn );
		if( !$pathNameIn ){
			$this->messenger->noteFailure( 'Resource "'.$fileIn.'" is missing.' );
			return FALSE;
		}
		if( !file_exists( $pathNameIn ) ){
			$this->messenger->noteFailure( 'Resource "'.$fileIn.'" is not existing.' );
			return FALSE;
		}
		if( !is_readable( $pathNameIn ) ){
			$this->messenger->noteFailure( 'Resource "'.$fileIn.'" is not readable.' );
			return FALSE;
		}
		if( !is_executable( $pathNameIn ) ){
			$this->messenger->noteFailure( 'Resource "'.$fileIn.'" is not executable.' );
			return FALSE;
		}
		$pathOut	= dirname( $fileOut );
		if( !is_dir( $pathOut ) && !self::createPath( $pathOut ) ){
			$this->messenger->noteFailure( 'Path "'.$pathOut.'" is not creatable.' );
			return FALSE;
		}
		if( file_exists( $fileOut ) ){
			$this->messenger->noteFailure( 'Target "'.$fileOut.'" is already existing.' );
			return FALSE;
		}
		if( !symlink( $pathNameIn, $fileOut ) ){
			$this->messenger->noteFailure( 'Link for "'.$fileOut.'" failed.' );
			return FALSE;
		}
		return TRUE;
	}

	protected function unlinkModuleFile( string $moduleId, string $fileName, string $path )
	{
		$fileName	= $path.$fileName;
		if( file_exists( $fileName ) ){
			if( @unlink( $fileName ) )
				$this->messenger->noteSuccess( 'Removed "'.$fileName.'".' );
			else
				$this->messenger->noteFailure( 'Removal failed for "'.$fileName.'".' );
		}
	}
}
