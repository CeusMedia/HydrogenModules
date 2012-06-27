<?php
require_once 'cmClasses/trunk/autoload.php5';					//  load cmClasses
require_once 'cmFrameworks/trunk/autoload.php5';				//  load cmFrameworks
#require_once 'cmModules/trunk/autoload.php5';					//  load cmModules

CMC_Loader::registerNew( 'php5', NULL, 'classes/' );			//  register new autoloader

Server::$classEnvironment	= 'Environment';					//  set environment class
		
$server	= new Server();											//  start server
$server->run();													//  and run
?>