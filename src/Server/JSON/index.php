<?php

use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;

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
	#HtmlExceptionPage::display( $e );
	die( "Exception: ".$e->getMessage() );
}
