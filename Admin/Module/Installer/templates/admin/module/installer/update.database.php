<?php
$fieldset	= '';

$remote		= $env->getRemote();
if( $remote->getModules() && $remote->getModules()->has( 'Resource_Database' ) ){
	$driver			= $remote->getConfig()->get( 'module.resource_database.access.driver' );
	$versionFrom	= $moduleLocal->versionInstalled;
	$versionTo		= $moduleSource->versionAvailable;
	$sqlKey			= 'update:'.$versionFrom.'->'.$versionTo.'@';
#	remark( '$versionFrom: '.$versionFrom );
#	remark( '$versionTo: '.$versionTo );
#	remark( '$sqlKey: '.$sqlKey );
	$sql	= array();
	if( !empty( $moduleSource->sql[$sqlKey.$driver] ) ){
		$type		= 'Update<br/>v'.$versionFrom.' &rArr; v'.$versionTo.'<br/>DBMS: '.$driver;
		$content	= trim( $moduleSource->sql[$sqlKey.$driver] );
		if( strlen( $content ) ){
			$sql[]	= UI_HTML_Tag::create( 'dt', $type );
			$sql[]	= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'xmp', trim( $content ) ) );
		}
	}
	if( !empty( $moduleSource->sql[$sqlKey.'*'] ) ){
		$type		= 'Update<br/>v'.$versionFrom.' &rArr; v'.$versionTo.'<br/>DBMS: all';
		$content	= trim( $moduleSource->sql[$sqlKey.'*'] );
		if( strlen( $content ) ){
			$sql[]	= UI_HTML_Tag::create( 'dt', $type );
			$sql[]	= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'xmp', trim( $content ) ) );
		}
	}
	if( $sql ){
		$sql		= UI_HTML_Tag::create( 'dl', join( $sql ), array( 'class' => 'database' ) );
		$legend		= UI_HTML_Tag::create( 'legend', "Datenbank" );
		$fieldset	= UI_HTML_Tag::create( 'fieldset', $legend.$sql );
	}
}
return $fieldset;
?>