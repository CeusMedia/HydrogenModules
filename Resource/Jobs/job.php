#!/usr/bin/php
<?php

/*  --  CONFIG  --  */
$configFile		= "config/config.ini";								//  set (an alternative) config file
$errorsReport	= E_ALL;
$errorsDisplay	= TRUE;
$errorsStrict	= TRUE;
$verbose		= !TRUE;
$pathClasses	= "classes";
$modes			= array(
	'live',
	'test',
	'dev',
);




/*  --  JOB SCRIPT  --  */
/*  --  no need to edit below  --  */
setupErrorHandling( $errorsReport, $errorsDisplay, $errorsStrict );
changeDirIntoApp( $verbose );
!file_exists( "vendor" ) ? die( 'Please install first, using composer!' ) : NULL;

require_once "vendor/autoload.php";
require_once "vendor/ceus-media/common/compat.php";
\Loader::registerNew( 'php5', NULL, $pathClasses );						//  register new autoloader

$request	= new \Console_RequestReceiver();							//
$request	= new \ADT_List_Dictionary( $request->getAll() );			//

$verbose	= $request->has( '--verbose' ) || $request->has( '-v' );	//
$test		= $request->has( '--test' ) || $request->has( '-t' );		//
setupErrorHandling( $errorsReport, $errorsDisplay, $errorsStrict );		//  override error handling after request analysis

if( class_exists( '\Environment_Console' ) )							//  an individual console environment class is available
	\Jobber::$classEnvironment	= '\Environment_Console';				//  set individual console environment class
if( isset( $configFile ) && strlen( trim( $configFile ) ) )				//  an alternative config file is set
	\CMF_Hydrogen_Environment_Console::$configFile	= $configFile;		//  set alternative config file
try{
	$jobber	= new \Jobber();											//  start job handler
	$jobber->loadJobs( $modes, FALSE );									//  load jobs configured in XML or JSON files, allowing JSON to override
	$jobber->run( $request );											//  execute found jobs
}
catch( \Exception $e ){
	#UI_HTML_Exception_Page::display( $e );
	die( $e->getMessage().'@'.$e->getFile().'@'.$e->getLine() );
}


/*  --  FUNCTIONS  --  */
function changeDirIntoApp( $verbose = FALSE ){
	$path	= detectAppPath( $verbose );
	if( $path === getCwd().'/' )
		return;
	$verbose ? print( '- Application: '.$path.PHP_EOL ) : NULL;
	$verbose ? print( '- Current Dir: '.getCwd().'/'.PHP_EOL ) : NULL;
	$verbose ? print( 'Changing into application directory...'.PHP_EOL ) : NULL;
	if( !chdir( $path ) )
		throw new \RuntimeException( 'Could not change into application directory ('.$path.')' );
	if( !file_exists( getCwd().'/job.php' ) )
		throw new \RuntimeException( 'Change into application directory ('.$path.') failed' );
}
function detectAppPath( $verbose ){
	$verbose ? print( 'Detecting application path...'.PHP_EOL ) : NULL;
	if( isset( $_SERVER['PWD'] ) ){
		if( isset( $_SERVER['PHP_SELF'] ) ){
			$scriptFilename	= $_SERVER['SCRIPT_FILENAME'];
			if( substr( $scriptFilename, 0, 1 ) === '/' )
				return dirname( $scriptFilename ).'/';
			$dir	= preg_replace( '@^\./$@', '', dirname( $_SERVER['PHP_SELF'] ).'/' );
			if( !strlen( trim( $dir ) ) )
				return $_SERVER['PWD'].'/';
			$verbose ? print( '- Script Path: '.$dir.PHP_EOL ) : NULL;
			return realpath( $_SERVER['PWD'].'/'.$dir ).'/';
		}
	}
	if( isset( $_SERVER['OLDPWD'] ) )
		return $_SERVER['OLDPWD'].'/';
	throw new RuntimeException( 'Could not determine working directory' );
}
function handleError( $errno, $errstr, $errfile, $errline, array $errcontext ){
	if( error_reporting() === 0 )									    // error was suppressed with the @-operator
		return FALSE;
	throw new \ErrorException( $errstr, 0, $errno, $errfile, $errline );
}
function setupErrorHandling( $level = E_ALL, $display = TRUE, $strict = TRUE ){
	error_reporting( $level );
	ini_set( 'display_errors', $display );
	if( $strict )
		set_error_handler( 'handleError' );
}
?>
