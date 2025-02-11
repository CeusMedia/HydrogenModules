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

/*  --  JOB SCRIPT  --  */
/*  --  no need to edit below  --  */

require_once __DIR__.'/classes/JobScriptHelper.php';
@include 'vendor/autoload.php';

$helper = new JobScriptHelper();
$helper->changeDirIntoApp();
file_exists( getCwd().'/vendor' ) or die( 'Please install first, using composer!' );
require_once getCwd().'/vendor/autoload.php';
#require_once getCwd().'/vendor/ceus-media/common/src/compat8.php';

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
