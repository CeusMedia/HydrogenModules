<?php
$fieldset	= '';

function fixVersionBug( $version ){
	return preg_replace( "/-pl?([0-9])/", ".0.\\1", $version );
}

$remote		= $env->getRemote();
if( $remote->getModules() && $remote->getModules()->has( 'Resource_Database' ) ){
	$driver			= $remote->getConfig()->get( 'module.resource_database.access.driver' );
	$versionFrom		= $moduleLocal->versionInstalled;
	$versionTo		= $moduleSource->versionAvailable;

	$sqlKey			= 'update:'.$versionFrom.'->'.$versionTo.'@';

	$list	= array();
	$versionCurrent	= fixVersionBug( $versionFrom );
	$versionTarget	= fixVersionBug( $versionTo );
	foreach( $moduleSource->sql as $key => $sql ){
		$versionStep	= fixVersionBug( $sql->version );
		if( $sql->event === "update" ){																				//  sql part is an update
			if( version_compare( $versionStep, $versionCurrent, '>' ) ){											//  sql part is newer than current version
				if( version_compare( $versionStep, $versionTarget, '<=' ) ){										//  sql part is older or related to new version
					$facts		= array( 'Version: '.$sql->version, 'DBMS: '.$sql->type );							//  collect facts
					$list[]		= UI_HTML_Tag::create( 'dt', join( '<br/>', $facts ) );								//  create definition term
					$list[]		= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'xmp', trim( $sql->sql ) ) );		//  create definition description
				}
			}
		}
	}

	if( $list ){
		$list		= UI_HTML_Tag::create( 'dl', join( $list ), array( 'class' => 'database' ) );
		$legend		= UI_HTML_Tag::create( 'legend', "Datenbank", array( 'class' => 'database' ) );
		$text		= UI_HTML_Tag::create( 'p', $words['update']['textDatabase'] );
		$fieldset	= UI_HTML_Tag::create( 'fieldset', $legend.$text.'<br/>'.$list );
	}
}
return $fieldset;
?>
