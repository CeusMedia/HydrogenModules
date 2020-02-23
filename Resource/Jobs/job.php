#!/usr/bin/php
<?php

/*  --  CONFIG  --  */
/*  --  change these (default) settings if needed  --  */
/*
$configFile		= "config/config.ini";											//  set an alternative config file
$pathClasses	= 'classes/';
$verbose		= FALSE;
$modes			= array(
	'live',
	'test',
	'dev',
);
$errorHandling	= array(
	'report'	=> E_ALL,
	'display'	=> TRUE,
	'catch'		=> TRUE,
);
*/
$errorHandling	= array(
	'report'	=> E_ALL,
	'display'	=> !TRUE,
	'catch'		=> TRUE,
);


/*  --  JOB SCRIPT  --  */
/*  --  no need to edit below  --  */
$helper	= new JobScriptHelper();
isset( $configFile ) ? $helper->setConfigFile( $configFile ) : NULL;
isset( $pathClasses ) ? $helper->setClassesPath( $pathClasses ) : NULL;
isset( $modes ) ? $helper->setModes( $modes ) : NULL;
isset( $verbose ) ? $helper->setVerbose( $verbose ) : NULL;
isset( $errorHandling ) ? $helper->setErrorHandling( $errorHandling ) : NULL;
$helper->run();


class JobScriptHelper
{
	protected $configFile		= "config/config.ini";							//  config file
	protected $errorHandling	= array(
		'report'	=> E_ALL,
		'display'	=> TRUE,
		'catch'		=> TRUE,
	);
	protected $modes			= array(
		'live',
		'test',
		'dev',
	);
	protected $pathClasses		= 'classes/';
	protected $verbose			= FALSE;

	public function handleError( $errno, $errstr, $errfile, $errline, ?array $errcontext )
	{
		if( error_reporting() === 0 )											// error was suppressed with the @-operator
			return FALSE;
		throw new \ErrorException( $errstr, 0, $errno, $errfile, $errline );
	}

	public function run(){
		$this->changeDirIntoApp()
			->setupEnvironment()
			->setupErrorHandling()												//  override error handling after request analysis
			->runJobApp();
	}

	public function setConfigFile( string $configFile ): self
	{
		$this->configFile	= $configFile;
		return $this;
	}

	public function setErrorHandling( $errorHandling ): self
	{
		$this->errorHandling	= $errorHandling;
		return $this;
	}

	public function setModes( array $modes ): self
	{
		$this->modes		= $modes;
		return $this;
	}

	public function setClassesPath( string $pathClasses ): self
	{
		$this->pathClasses		= $pathClasses;
		return $this;
	}

	public function setVerbose( bool $verbose = TRUE ): self
	{
		$this->verbose		= (bool) $verbose;
		return $this;
	}

	//  --  PROTECTED  --  //
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
				throw new \RuntimeException( 'Could not change into application directory ('.$path.')' );
			if( !file_exists( getCwd().'/job.php' ) )
				throw new \RuntimeException( 'Change into application directory ('.$path.') failed' );
		}
		return $this;
	}

	protected function detectAppPath(): string
	{
		$this->verbose ? print( 'Detecting application path...'.PHP_EOL ) : NULL;
		if( isset( $_SERVER['PWD'] ) ){
			if( isset( $_SERVER['PHP_SELF'] ) ){
				$scriptFilename	= $_SERVER['SCRIPT_FILENAME'];
				if( substr( $scriptFilename, 0, 1 ) === '/' )
					return dirname( $scriptFilename ).'/';
				$dir	= preg_replace( '@^\./$@', '', dirname( $_SERVER['PHP_SELF'] ).'/' );
				if( !strlen( trim( $dir ) ) )
					return $_SERVER['PWD'].'/';
				$this->verbose ? print( '- Script Path: '.$dir.PHP_EOL ) : NULL;
				return realpath( $_SERVER['PWD'].'/'.$dir ).'/';
			}
		}
		if( isset( $_SERVER['OLDPWD'] ) )
			return $_SERVER['OLDPWD'].'/';
		throw new RuntimeException( 'Could not determine working directory' );
	}

	protected function runJobApp()
	{
		try{
			$jobber	= new \Jobber();											//  start job handler
			$jobber->loadJobs( $this->modes, FALSE );							//  load jobs configured in XML or JSON files, allowing JSON to override
			$code	= $jobber->run( $this->request );							//  execute found jobs
			exit( $code );
		}
		catch( \Exception $e ){
			print( $e->getMessage().'@'.$e->getFile().':'.$e->getLine().PHP_EOL );
			exit -2;
		}
	}

	protected function setupEnvironment(): self
	{
		!file_exists( "vendor" ) ? die( 'Please install first, using composer!' ) : NULL;
		require_once 'vendor/autoload.php';
		require_once "vendor/ceus-media/common/compat.php";
		Loader::registerNew( 'php5', NULL, $this->pathClasses );				//  register autoloader for project classes

		$request	= new \CLI_RequestReceiver();								//
		$this->request	= new \ADT_List_Dictionary( $request->getAll() );		//

		if( $request->has( '--verbose' ) || $request->has( '-v' ) )				//
			$this->setVerbose( TRUE );
//		$test	= $request->has( '--test' ) || $request->has( '-t' );			//

		if( class_exists( '\Environment_Console' ) )							//  an individual console environment class is available
			\Jobber::$classEnvironment	= '\Environment_Console';				//  set individual console environment class
		if( isset( $configFile ) && strlen( trim( $configFile ) ) )				//  an alternative config file is set
			\CMF_Hydrogen_Environment_Console::$configFile	= $configFile;		//  set alternative config file
		return $this;
	}

	protected function setupErrorHandling(): self
	{
		error_reporting( $this->errorHandling['report'] );
		ini_set( 'display_errors', $this->errorHandling['display'] );
		if( $this->errorHandling['catch'] )
			set_error_handler( array( $this, 'handleError' ) );
		return $this;
	}
}
