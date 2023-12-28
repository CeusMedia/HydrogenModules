<?php
//$errorLevel		= E_ALL;										//  enable full reporting
//$errorDisplay		= TRUE;											//  enable error display

//$pathLibraries	= "";											//  path to libraries

use CeusMedia\Common\Loader;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Application\Web\Site as WebSiteApp;
use CeusMedia\HydrogenFramework\Environment\Router\Recursive as RecursiveRouter;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

$versionCMC			= 'trunk';										//  branch path of cmClasses
$versionCMF			= 'trunk';										//  branch path of cmFrameworks
$versionCMM			= 'trunk';										//  branch path of cmModules

//$classPath		= "classes/";									//  set folder of project classes
//$classExt			= "php,php5";									//  set an alternative project class extension
//$classPrefix		= "My_";										//  set an alternative project class prefix
$configFile			= "config/config.ini";							//  set an alternative config file
$classRouter		= RecursiveRouter::class;						//  set an alternative router class


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
	WebEnvironment::$configFile	= $configFile;						//  set alternative config file in environment
if( !empty( $classRouter ) )										//  an alternative router class has been set
	WebEnvironment::$classRouter	= $classRouter;					//  set alternative router class in environment

try{
	Loader::create()												//  create autoloader for project classes
		->setExtensions( $classExt ?? 'php,php5' )					//  realize project class extension
		->setPrefix( $classPrefix ?? NULL )							//  realize project class prefix
		->setPath( $classPath ?? 'classes/' )						//  realize project class path
		->register();												//  and activate
	$app	= new WebSiteApp();										//  create default website application instance
	$app->run();													//  and run it
}
catch( Exception $e ){												//  an uncaught exception happened
	HtmlExceptionPage::display( $e );								//  display report page with call stack
}
