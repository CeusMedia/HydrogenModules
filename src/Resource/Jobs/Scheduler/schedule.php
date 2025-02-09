#!/usr/bin/php
<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\CLI\RequestReceiver;
use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment\Console as ConsoleEnvironment;

$configFile		= "config/config.ini";								//  set (an alternative) config file
$modes			= [
	'live',
	'test',
	'dev',
];

//  --  NO NEED TO CHANGE BELOW  --  //
require_once "vendor/autoload.php";
Loader::create( 'php', 'classes/' )->register();		//  register new autoloader

//$mode			= "live";											//  override configured mode, values: dev|test|live

/*  --  NO NEED TO CHANGE BELOW  --  */
$request	= new RequestReceiver();
$request	= new Dictionary( $request->getAll() );

$loop		= $request->has( '--loop' ) || $request->has( '-l' );
$verbose	= $request->has( '--verbose' ) || $request->has( '-v' );
$test		= $request->has( '--test' ) || $request->has( '-t' );

if( class_exists( '\Environment_Console' ) )					//  an individual console environment class is available
	Jobber::$classEnvironment	= '\Environment_Console';			//  set individual console environment class
//if( class_exists( 'Environment' ) )
//	Maintainer::$classEnvironment	= 'Environment';

///** @8.3_phpstan-ignore-next-line */
if( '' !== ( $configFile ?? '' ) )									//  an alternative config file is set
	ConsoleEnvironment::$configFile   = $configFile;				//  set config file

$m	= new Scheduler();												//  start maintainer
$m->loadJobs( $mode ?? NULL );					        	//  load jobs for configured or called mode
$m->run( $loop, $verbose );											//  run maintainer to handle jobs
