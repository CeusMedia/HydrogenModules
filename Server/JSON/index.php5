<?php
$pathLibs		= "";													//  path to libraries
$versionCMC		= "trunk";												//  path to cmClasses
$versionCMF		= "trunk";												//  path to cmFrameworks
$versionCMM		= "trunk";												//  path to cmModules
$timezone		= "Europe/Berlin";										//  default time zone

require_once $pathLibs.'cmClasses/'.$versionCMC.'/autoload.php5';		//  load cmClasses
require_once $pathLibs.'cmFrameworks/'.$versionCMF.'/autoload.php5';	//  load cmFrameworks
require_once $pathLibs.'cmModules/'.$versionCMM.'/autoload.php5';		//  load cmModules

CMC_Loader::registerNew( 'php5', NULL, 'classes/' );					//  register new autoloader

if( $timezone )
	date_default_timezone_set( $timezone );								//  set default time zone

#try{
	Server::$classEnvironment	= 'Environment';						//  set environment class
	$server	= new Server();												//  start server
	$server->run();														//  and run
#}
#catch( Exception $e ){
#	UI_HTML_Exception_Page::display( $e );
#}
?>
