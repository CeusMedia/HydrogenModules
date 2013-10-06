<?php
//$errorLevel		= E_ALL;										//  enable full reporting
//$errorDisplay		= TRUE;											//  enable error display

//$pathLibraries	= "";											//  path to libraries

$versionCMC			= 'trunk';										//  branch path of cmClasses
$versionCMF			= 'trunk';										//  branch path of cmFrameworks
$versionCMM			= 'trunk';										//  branch path of cmModules

//$classPath		= "classes/";									//  set folder of project classes
//$classExt			= "php,php5";									//  set an alternative project class extension
//$classPrefix		= "My_";										//  set an alternative project class prefix
$configFile			= "config/config.ini";							//  set an alternative config file
$classRouter		= "CMF_Hydrogen_Environment_Router_Recursive";	//  set an alternative router class 


//  -------------------------------  //
//  --  NO NEED TO CHANGE BELOW  --  //
//  -------------------------------  //
if( isset( $errorReporting ) )
	error_reporting( $errorReporting );
if( isset( $displayErrors ) )
	ini_set( 'display_errors', $displayErrors );

if( isset( $errorLevel ) )											//  ...
	error_reporting( $errorLevel );									//  ...
if( isset( $errorDisplay ) )										//  ...
	ini_set( 'display_errors', $errorDisplay );						//  ...


$path	= isset( $pathLibraries ) ? $pathLibraries : "";			//  realize library path
require_once $path.'cmClasses/'.$versionCMC.'/autoload.php5';		//  load cmClasses
require_once $path.'cmFrameworks/'.$versionCMF.'/autoload.php5';	//  load cmFrameworks
require_once $path.'cmModules/'.$versionCMM.'/autoload.php5';		//  load cmModules

if( !empty( $configFile ) )											//  an alternative config file has been set
	CMF_Hydrogen_Environment_Web::$configFile	= $configFile;		//  set alternative config file in environment
if( !empty( $classRouter ) )										//  an alternative router class has been set
	CMF_Hydrogen_Environment_Web::$classRouter	= $classRouter;		//  set alternative router class in environment

try{
	CMC_Loader::registerNew(										//  register autoloader for project classes
		isset( $classExt ) ? $classExt : "php,php5",						//  realize project class extension
		isset( $classPrefix ) ? $classPrefix : NULL,				//  realize project class prefix
		isset( $classPath ) ? $classPath : "classes/"				//  realize project class path
	);
	$app	= new CMF_Hydrogen_Application_Web_Site();				//  create default web site application instance
	$app->run();													//  and run it
}
catch( Exception $e ){												//  an uncatched exception happend
    UI_HTML_Exception_Page::display( $e );							//  display report page with call stack
}
?>
