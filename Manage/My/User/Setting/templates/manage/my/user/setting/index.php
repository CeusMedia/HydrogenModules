<?php

$helper	= new View_Helper_UserModuleSettings( $env );
$panel	= $helper->renderPanel();

if( $panel )
	return $panel;

$words	= (object) $words['index'];
return $words->noSettings;
?>
