#!/usr/bin/php
<?php

/*  --  CONFIG  --  */
/*  --  change these (default) settings if needed  --  */
/*
$configFile		= "config.ini";											//  set an alternative config file
$pathClasses	= 'classes/';
$verbose		= FALSE;
$modes			= [
	'live',
	'test',
	'dev',
];
$mode			= 'dev';
$errorHandling	= [
	'report'	=> E_ALL,
	'display'	=> TRUE,
	'catch'		=> TRUE,
];
*/
$errorHandling	= [
	'report'	=> E_ALL,
	'display'	=> !TRUE,
	'catch'		=> TRUE,
];

file_exists( __DIR__.'/vendor' ) or die( 'Please install first, using composer!' );
require_once __DIR__.'/vendor/autoload.php';
#require_once __DIR__.'/vendor/ceus-media/common/src/compat8.php';
require_once __DIR__.'/classes/JobScriptHelper.php';


/*  --  JOB SCRIPT  --  */
/*  --  no need to edit below  --  */
$helper	= new JobScriptHelper();

if( isset( $configFile ) )
	$helper->setConfigFile( $configFile );
if( isset( $pathClasses ) )
	$helper->setClassesPath( $pathClasses );
if( isset( $modes ) )
	$helper->setModes( $modes );
if( isset( $mode ) )
	$helper->setMode( $mode );
if( isset( $verbose ) )
	$helper->setVerbose( $verbose );
if( isset( $errorHandling ) )
	$helper->setErrorHandling( $errorHandling );

$helper->run();
