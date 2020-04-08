#!/usr/bin/php
<?php
$configFile		= "config/config.ini";								//  set (an alternative) config file
$modes			= array(
	'live',
	'test',
	'dev',
);

//  --  NO NEED TO CHANGE BELOW  --  //
require_once "vendor/autoload.php";
require_once "vendor/ceus-media/common/compat.php";
\Loader::registerNew( 'php5', NULL, 'classes/' );					//  register new autoloader

//$mode			= "live";											//  override configured mode, values: dev|test|live
//$configFile	= "config/config.ini";								//  set an alternative config file

/*  --  NO NEED TO CHANGE BELOW  --  */
$request	= new \Console_RequestReceiver();
$request	= new \ADT_List_Dictionary( $request->getAll() );

$loop		= $request->has( '--loop' ) || $request->has( '-l' );
$verbose	= $request->has( '--verbose' ) || $request->has( '-v' );
$test		= $request->has( '--test' ) || $request->has( '-t' );

if( class_exists( '\Environment_Console' ) )							//  an individual console environment class is available
	\Jobber::$classEnvironment	= '\Environment_Console';				//  set individual console environment class
//if( class_exists( 'Environment' ) )
//	Maintainer::$classEnvironment	= 'Environment';
if( isset( $configFile ) )											//  an alternative config file is set
	\CMF_Hydrogen_Environment_Console::$configFile   = $configFile;	//  set config file

$m	= new \Scheduler();												//  start maintainer
$m->loadJobs( isset( $mode ) ? $mode : NULL );						//  load jobs for configured or called mode
$m->run( $loop, $verbose );											//  run maintainer to handle jobs
?>
