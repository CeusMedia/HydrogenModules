<?php
(include_once 'vendor/autoload.php') or die( 'Install packages using composer, first!' );

use CeusMedia\Common\Loader;
use CeusMedia\Common\UI\HTML\Exception\Page as ExceptionPage;
use CeusMedia\HydrogenFramework\Application\Web\Site as WebSiteApplication;
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

WebEnvironment::$configFile		= $configFile;				//  set (possibly alternative) config file in environment
WebEnvironment::$classRouter	= $classRouter;				//  set (possibly alternative) router class in environment
error_reporting( $errorReporting );
ini_set( 'display_errors', $displayErrors );
date_default_timezone_set( $defaultTimezone );				//  set (possibly alternative) time zone

try{
	Loader::create()->setPath( $pathClasses )->register();	//  register autoloader for project classes
	$app	= new WebSiteApplication();						//  create default website application instance
	$app->run();											//  and run it
}
catch( Throwable $e ){										//  an uncaught exception happened
	class_exists( '\\SentrySdk' ) && \Sentry\captureException( $e );
	http_response_code( 500 );
	(include_once 'templates/error.php') or ExceptionPage::display( $e );
}
