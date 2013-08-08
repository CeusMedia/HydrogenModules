<?php
#$errorReporting		= E_ALL;
#$displayErrors		= TRUE;

$versionCMC			= 'trunk';											//  branch path of cmClasses
$versionCMF			= 'trunk';											//  branch path of cmFrameworks
$versionCMM			= 'trunk';											//  branch path of cmModules

$pathLibraries		= "";#"lib/";										//  path to libraries

$pathClasses		= "classes/";
$configFile			= "config/config.ini";								//  set (an alternative) config file
$classRouter		= "CMF_Hydrogen_Environment_Router_Recursive";
#$classEnvironment	= "CMF_Hydrogen_Environment_Web";


//  --  NO NEED TO CHANGE BELOW  --  //

if( isset( $errorReporting ) )
	error_reporting( $errorReporting );
if( isset( $displayErrors ) )
	ini_set( 'display_errors', $displayErrors );

require_once $pathLibraries.'cmClasses/'.$versionCMC.'/autoload.php5';		//  load cmClasses
require_once $pathLibraries.'cmFrameworks/'.$versionCMF.'/autoload.php5';	//  load cmFrameworks
require_once $pathLibraries.'cmModules/'.$versionCMM.'/autoload.php5';		//  load cmModules

if( !empty( $configFile ) )													//  an alternative config file has been set
	CMF_Hydrogen_Environment_Web::$configFile	= $configFile;
if( !empty( $classRouter ) )
	CMF_Hydrogen_Environment_Web::$classRouter	= $classRouter;

try{
	CMC_Loader::registerNew( 'php5', NULL, $pathClasses );						//  register new autoloader
	$app	= new CMF_Hydrogen_Application_Web_Site();
	$app->run();
}
catch( Exception $e ){
    UI_HTML_Exception_Page::display( $e );
}
?>
