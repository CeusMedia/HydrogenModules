<?php
( include_once 'vendor/autoload.php' ) or die( 'Install packages using composer, first!' );

use CeusMedia\Common\UI\HTML\Exception\Page as ExceptionPage;
use CeusMedia\HydrogenFramework\Application\Web\Site as WebEnvironment;
use CeusMedia\HydrogenFramework\Environment\Router\Recursive as RecursiveRouter;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

$errorReporting		= E_ALL;								//  enable full reporting
$displayErrors		= TRUE;									//  enable error display
$pathClasses		= 'classes/';
$configFile			= 'config.ini';							//  set an alternative config file
$classRouter		= RecursiveRouter::class;				//  set an alternative router class
$defaultTimezone	= 'Europe/Berlin';						//  default time zone

//  -------------------------------  //
//  --  NO NEED TO CHANGE BELOW  --  //
//  -------------------------------  //

if( isset( $configFile ) )									//  an alternative config file has been set
	WebEnvironment::$configFile	= $configFile;				//  set alternative config file in environment
if( isset( $classRouter ) )									//  an alternative router class has been set
	WebEnvironment::$classRouter	= $classRouter;			//  set alternative router class in environment
if( isset( $errorReporting ) )
	error_reporting( $errorReporting );
if( isset( $displayErrors ) )
	ini_set( 'display_errors', $displayErrors );
if( isset( $defaultTimezone ) )								//  an alternative time zone is defined
	date_default_timezone_set( $defaultTimezone );			//  set alternative time zone

try{
	require_once "vendor/ceus-media/common/compat.php";		//  load compatibility layer
	Loader::registerNew( 'php5', NULL, $pathClasses );		//  register autoloader for project classes
	$app	= new WebSiteApplication();						//  create default web site application instance
	$app->run();											//  and run it
}
catch( Exception $e ){										//  an uncatched exception happend
	ExceptionPage::display( $e );							//  display report page with call stack
}
