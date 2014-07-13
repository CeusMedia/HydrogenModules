<?php

$pathLibs		= "";
$verCMC			= 'trunk';
$verCMF			= 'trunk';
$verCMM			= 'trunk';
$configFile		= "config/config.ini";								//  set (an alternative) config file
$modes			= array(
	'live',
	'test',
	'dev',
);

//  --  NO NEED TO CHANGE BELOW  --  //
require_once $pathLibs.'cmClasses/'.$verCMC.'/autoload.php5';			//  load cmClasses
require_once $pathLibs.'cmFrameworks/'.$verCMF.'/autoload.php5';		//  load cmFrameworks
require_once $pathLibs.'cmModules/'.$verCMM.'/autoload.php5';			//  load cmModules

CMC_Loader::registerNew( 'php5', NULL, 'classes/' );					//  register new autoloader
$request	= new Console_RequestReceiver();							//  
$request	= new ADT_List_Dictionary( $request->getAll() );			//  

$verbose	= $request->has( '--verbose' ) || $request->has( '-v' );	//  
$test		= $request->has( '--test' ) || $request->has( '-t' );		//  

function handleError( $errno, $errstr, $errfile, $errline, array $errcontext ){
    if( error_reporting() === 0 )									    // error was suppressed with the @-operator
        return FALSE;
    throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
}
set_error_handler( 'handleError' );

if( class_exists( 'Environment' ) )										//  an individual environment class is available
	Jobber::$classEnvironment	= 'Environment';						//  set individual environment class
if( isset( $configFile ) )												//  an alternative config file is set
	CMF_Hydrogen_Environment_Console::$configFile	= $configFile;		//  set alternative config file
try{
	$jobber	= new Jobber();												//  start job handler
	$jobber->loadJobs( $modes );										//  load jobs configured in XML files
	$jobber->run( $request );											//  execute found jobs
}
catch( Exception $e ){
	#UI_HTML_Exception_Page::display( $e );
	die( $e->getMessage().'@'.$e->getFile().'@'.$e->getLine() );
}
?>
