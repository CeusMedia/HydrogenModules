<?php
$timezone		= "Europe/Berlin";										//  default time zone

require_once 'vendor/autoload.php';

Loader::registerNew( 'php5', NULL, 'classes/' );						//  register new autoloader

if( $timezone )
	date_default_timezone_set( $timezone );								//  set default time zone

try{
	Server::$classEnvironment	= 'Environment';						//  set environment class
	$server	= new Server();												//  start server
	$server->run();														//  and run
}
catch( Exception $e ){
	#UI_HTML_Exception_Page::display( $e );
	die( "Exception: ".$e->getMessage() );
}
?>
