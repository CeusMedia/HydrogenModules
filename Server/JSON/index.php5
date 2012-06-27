<?php
require_once 'cmClasses/trunk/autoload.php5';
require_once 'cmFrameworks/trunk/autoload.php5';

CMC_Loader::registerNew( 'php5', NULL, 'classes/' );			//  register new autoloader

Server::$classEnvironment	= 'Environment';
		
$server	= new Server();											//  start server
$server->run();
?>
