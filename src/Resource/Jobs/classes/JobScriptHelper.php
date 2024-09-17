<?php

use CeusMedia\Common\CLI\RequestReceiver;
use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment\Console as ConsoleEnvironment;

class JobScriptHelper
{
	protected Request $request;
	protected string $configFile	= "config/config.ini";							//  config file
	protected array $errorHandling	= [
		'report'	=> E_ALL,
		'display'	=> TRUE,
		'catch'		=> TRUE,
	];
	protected array $modes			= [
		'live',
		'test',
		'dev',
	];
	protected string $mode;
	protected string $pathClasses	= 'classes/';
	protected bool $verbose			= FALSE;

	public function __construct()
	{
		$this->changeDirIntoApp()->setupEnvironment();
	}

	/**
	 *	@param		int			$number
	 *	@param		string		$message
	 *	@param		?string		$file
	 *	@param		?int		$line
	 *	@param		?array		$context
	 *	@return		bool
	 *	@throws		ErrorException
	 */
	public function handleError( int $number, string $message, ?string $file, ?int $line, ?array $context = NULL ): bool
	{
		if( 0 === error_reporting() )											// error was suppressed with the @-operator
			return FALSE;
		throw new ErrorException( $message, 0, $number, $file, $line );
	}

	public function run(): void
	{
		$this
			->setupErrorHandling()								//  override error handling after request analysis
			->detectAppMode()
			->runJobApp();
	}

	/**
	 *	@param		string		$configFile
	 *	@return		self
	 */
	public function setConfigFile( string $configFile ): self
	{
		$this->configFile	= $configFile;
		return $this;
	}

	/**
	 *	@param		array		$errorHandling
	 *	@return		self
	 */
	public function setErrorHandling( array $errorHandling ): self
	{
		$this->errorHandling	= $errorHandling;
		return $this;
	}

	/**
	 *	@param		string		$mode
	 *	@return		self
	 */
	public function setMode( string $mode ): self
	{
		if( !in_array( $mode, $this->modes ) )
			throw new RangeException( 'Invalid mode: '.$mode );
		$this->mode		= $mode;
		return $this;
	}

	/**
	 *	@param		array		$modes
	 *	@return		self
	 */
	public function setModes( array $modes ): self
	{
		$this->modes		= $modes;
		return $this;
	}

	/**
	 *	@param		string		$pathClasses
	 *	@return		self
	 */
	public function setClassesPath( string $pathClasses ): self
	{
		$this->pathClasses		= $pathClasses;
		return $this;
	}

	/**
	 *	@param		bool		$verbose
	 *	@return		self
	 */
	public function setVerbose( bool $verbose = TRUE ): self
	{
		$this->verbose		= $verbose;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		self
	 */
	protected function changeDirIntoApp(): self
	{
		$path	= $this->detectAppPath();
		if( $path !== getCwd().'/' ){
			if( $this->verbose ){
				print( '- Application: '.$path.PHP_EOL );
				print( '- Current Dir: '.getCwd().'/'.PHP_EOL );
				print( 'Changing into application directory...'.PHP_EOL );
			}
			if( !chdir( $path ) )
				throw new RuntimeException( 'Could not change into application directory ('.$path.')' );
			if( !file_exists( getCwd().'/job.php' ) )
				throw new RuntimeException( 'Change into application directory ('.$path.') failed' );
		}
		return $this;
	}

	/**
	 *	@return		self
	 */
	protected function detectAppMode(): self
	{
		if( !$this->mode ){
			$file	= '.hymn';
			if( file_exists( $file ) ){
				$hymn = JsonFileReader::load( $file );
				$mode = $hymn->application->installMode ?? 'dev';
				if( in_array( $mode, $this->modes ) )
					$this->mode = $mode;
			}
		}
		return $this;
	}

	/**
	 *	@return		string
	 */
	protected function detectAppPath(): string
	{
		if( $this->verbose )
			print( 'Detecting application path...'.PHP_EOL );
		if( isset( $_SERVER['PWD'] ) ){
			if( isset( $_SERVER['PHP_SELF'] ) ){
				$scriptFilename	= $_SERVER['SCRIPT_FILENAME'];
				if( str_starts_with( $scriptFilename, '/' ) )
					return dirname( $scriptFilename ).'/';
				$dir	= preg_replace( '@^\./$@', '', dirname( $_SERVER['PHP_SELF'] ).'/' );
				if( !strlen( trim( $dir ) ) )
					return $_SERVER['PWD'].'/';
				if( $this->verbose )
					print( '- Script Path: '.$dir.PHP_EOL );
				return realpath( $_SERVER['PWD'].'/'.$dir ).'/';
			}
		}
		if( isset( $_SERVER['OLDPWD'] ) )
			return $_SERVER['OLDPWD'].'/';
		throw new RuntimeException( 'Could not determine working directory' );
	}

	/**
	 *	@return		never
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function runJobApp(): never
	{
//		try{
		$jobber	= new Jobber();											//  start job handler
		$jobber->setMode( $this->mode );
//			$jobber->loadJobs( $this->modes, FALSE );							//  load jobs configured in XML or JSON files, allowing JSON to override
		$result	= $jobber->run();							//  execute found jobs
		$code	= 1 === $result ? 0 : -1 * $result;
		exit( $code );
//		}
//		catch( \Exception $e ){
//			$cwd	= dirname( __FILE__ ).'/';
//			$cwd	= getCwd().'/';
//			$p	= $e->getPrevious() ?: $e;
//			print( 'Error:     '.get_class( $p ).' thrown and not caught'.PHP_EOL );
//			print( 'Message:   '.$p->getMessage().PHP_EOL );
//			print( 'Location:  '.str_replace( $cwd, '', $p->getFile() ).' line #'.$p->getLine().PHP_EOL );
////			print( 'File Dir:  '.$cwd.PHP_EOL );
//			print( 'Trace:'.PHP_EOL );
//			print( str_replace( $cwd, '', $p->getTraceAsString() ).PHP_EOL );
//			exit -2;
//		}
	}

	/**
	 *	@return		self
	 */
	protected function setupEnvironment(): self
	{
		Loader::create( 'php', $this->pathClasses )->register();		//  register autoloader for project classes

		$request	= new RequestReceiver();									//
		$this->request	= new Request();
		foreach( $request->getAll() as $key => $value )
			$this->request->set( $key, $value );								//

		if( $request->has( '--verbose' ) || $request->has( '-v' ) )	//
			$this->setVerbose( TRUE );
//		$test	= $request->has( '--test' ) || $request->has( '-t' );			//

		if( class_exists( '\Environment_Console' ) )						//  an individual console environment class is available
			Jobber::$classEnvironment	= '\Environment_Console';				//  set individual console environment class
		if( isset( $this->configFile ) && strlen( trim( $this->configFile ) ) )	//  an alternative config file is set
			ConsoleEnvironment::$configFile	= $this->configFile;				//  set alternative config file
		return $this;
	}

	/**
	 *	@return		self
	 */
	protected function setupErrorHandling(): self
	{
		error_reporting( $this->errorHandling['report'] );
		ini_set( 'display_errors', $this->errorHandling['display'] );
		if( $this->errorHandling['catch'] )
			set_error_handler( [$this, 'handleError'] );
		return $this;
	}
}
