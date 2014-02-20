<?php
$verCMC	= 'trunk';
$verCMF	= 'trunk';
$verCMM	= 'trunk';

$configFile		= "config/config.ini";								//  set (an alternative) config file

$modes	= array(
	'live',
	'test',
	'dev',
);

//  --  NO NEED TO CHANGE BELOW  --  //
require_once 'cmClasses/'.$verCMC.'/autoload.php5';						//  load cmClasses
require_once 'cmFrameworks/'.$verCMF.'/autoload.php5';					//  load cmFrameworks
require_once 'cmModules/'.$verCMM.'/autoload.php5';						//  load cmModules

CMC_Loader::registerNew( 'php5', NULL, 'classes/' );					//  register new autoloader
$request	= new Console_RequestReceiver();							//  
$request	= new ADT_List_Dictionary( $request->getAll() );			//  

$verbose	= $request->has( '--verbose' ) || $request->has( '-v' );	//  
$test		= $request->has( '--test' ) || $request->has( '-t' );		//  

if( class_exists( 'Environment' ) )										//  an individual environment class is available
	Jobber::$classEnvironment	= 'Environment';						//  set individual environment class
if( isset( $configFile ) )												//  an alternative config file has been set
	CMF_Hydrogen_Environment_Console::$configFile	= $configFile;
try{
	$jobber	= new Jobber();												//  start job handler
	$jobber->loadJobs( $modes );										//  load jobs configured in XML files
	$jobber->run( $request );											//  execute found jobs
}
catch( Exception $e ){
	die( $e->getMessage() );
}
?>
