<?php
$errorReporting		= E_ALL;										//  enable full reporting
$displayErrors		= TRUE;											//  enable error display

$pathLibraries		= "";											//  path to libraries

$versionCMC			= 'trunk';										//  branch path of cmClasses
$versionCMF			= 'trunk';										//  branch path of cmFrameworks

$pathClasses		= "classes/";
$configFile			= "config/config.ini";							//  set an alternative config file
$classRouter		= "CMF_Hydrogen_Environment_Router_Recursive";	//  set an alternative router class
$defaultTimezone	= "Europe/Berlin";								//  default time zone

//  -------------------------------  //
//  --  NO NEED TO CHANGE BELOW  --  //
//  -------------------------------  //
if( isset( $errorReporting ) )
	error_reporting( $errorReporting );
if( isset( $displayErrors ) )
	ini_set( 'display_errors', $displayErrors );
if( $defaultTimezone )
	date_default_timezone_set( $defaultTimezone );					//  set default time zone

$path	= isset( $pathLibraries ) ? $pathLibraries : "";			//  realize library path
require_once $path.'cmClasses/'.$versionCMC.'/autoload.php5';		//  load cmClasses
require_once $path.'cmFrameworks/'.$versionCMF.'/autoload.php5';	//  load cmFrameworks

if( !empty( $configFile ) )											//  an alternative config file has been set
	CMF_Hydrogen_Environment_Web::$configFile	= $configFile;		//  set alternative config file in environment
if( !empty( $classRouter ) )										//  an alternative router class has been set
	CMF_Hydrogen_Environment_Web::$classRouter	= $classRouter;		//  set alternative router class in environment

try{
	CMC_Loader::registerNew( 'php5', NULL, $pathClasses );			//  register autoloader for project classes
	$app	= new CMF_Hydrogen_Application_Web_Site();				//  create default web site application instance
	$app->run();													//  and run it
}
catch( Exception $e ){												//  an uncatched exception happend
    UI_HTML_Exception_Page::display( $e );							//  display report page with call stack
}
?>
