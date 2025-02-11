<?php

use CeusMedia\Common\Loader;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Application\Web\Site as WebSiteApp;
use CeusMedia\HydrogenFramework\Environment\Router\Recursive as RecursiveRouter;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/**
 * Showcase of a minimal application invocation script.
 *
 * You can configure:
 * - Config File (supporting alternative base configurations)
 * - Class File Extensions & Class File Folder
 * - Router Strategy: in this example, we use the recursive router instead of the default single router
 * - Timezone
 * - Core Error Reporting
 * - Core Error Handling (display uncaught or fatal errors)
 *
 *	See index.php of module App:Site for a better exception handling, using Sentry!
 */

error_reporting( E_ALL );									//  ...

ini_set( 'display_errors', TRUE );

date_default_timezone_set( 'Europe/Berlin' );				//  set time zone

WebEnvironment::$configFile		= 'config.ini';						//  set alternative config file in environment

WebEnvironment::$classRouter	= RecursiveRouter::class;			//  set alternative router class in environment

try{
	Loader::create()												//  create autoloader for project classes
		->setExtensions( 'php' )							//  realize project class extension
		->setPath( 'classes/' )								//  realize project class path
		->register();												//  and activate
	$app	= new WebSiteApp();										//  create default website application instance
	$app->run();													//  and run it
}
catch( Throwable $e ){												//  an uncaught exception happened
	HtmlExceptionPage::display( $e );								//  display report page with call stack
}
