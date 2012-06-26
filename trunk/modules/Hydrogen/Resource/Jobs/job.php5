<?php
require_once 'cmClasses/trunk/autoload.php5';
require_once 'cmFrameworks/trunk/autoload.php5';
require_once 'cmModules/trunk/autoload.php5';

CMC_Loader::registerNew( 'php5', NULL, 'classes/' );				//  register new autoloader
$request	= new Console_RequestReceiver();
$request	= new ADT_List_Dictionary( $request->getAll() );

$verbose	= $request->has( '--verbose' ) || $request->has( '-v' );
$test		= $request->has( '--test' ) || $request->has( '-t' );

$modes		= array( 'dev', 'test' );

if( class_exists( 'Environment' ) )
	Jobber::$classEnvironment	= 'Environment';
$jobber	= new Jobber();												//  start maintainer
$jobber->loadJobs( $modes );
$jobber->run( $request );
?>