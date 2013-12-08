<?php
$versionCMC	= "trunk";
$versionCMF	= "trunk";
$versionCMM	= "trunk";

require_once 'cmClasses/'.$versionCMC.'/autoload.php5';			//  load cmClasses
require_once 'cmFrameworks/'.$versionCMF.'/autoload.php5';		//  load cmFrameworks
require_once 'cmModules/'.$versionCMM.'/autoload.php5';			//  load cmModules

CMC_Loader::registerNew( 'php5', NULL, 'classes/' );			//  register new autoloader

#try{
	Server::$classEnvironment	= 'Environment';					//  set environment class
	$server	= new Server();											//  start server
	$server->run();													//  and run
#}
#catch( Exception $e ){
#	UI_HTML_Exception_Page::display( $e );
#}
?>
