<?php
$fieldset	= '';

function fixVersionBug( $version ){
	return preg_replace( "/-pl?([0-9])/", ".0.\\1", $version );
}

$remote		= $env->getRemote();
if( $remote->getModules() && $remote->getModules()->has( 'Resource_Database' ) ){
	$driver			= $remote->getConfig()->get( 'module.resource_database.access.driver' );
	$versionFrom	= $moduleLocal->versionInstalled;
	$versionTo		= $moduleSource->versionAvailable;

	$sqlKey			= 'update:'.$versionFrom.'->'.$versionTo.'@';

	$list	= array();
	foreach( $moduleSource->sql as $key => $sql ){
		if( $sql->event !== "update" )
			continue;
		if( version_compare( fixVersionBug( $sql->from ), fixVersionBug( $versionFrom ) ) < 0 )
			continue;
		if( version_compare( fixVersionBug( $versionTo ), fixVersionBug( $sql->to ) ) < 0 )
			continue;

		$versions	= $sql->event === 'update' ? '<br/>v'.$sql->from.' &rArr; v'.$sql->to : '';
		$label		= ucFirst( $sql->event ).$versions.'<br/>DBMS: '.$sql->type;
		$list[]		= UI_HTML_Tag::create( 'dt', $label );
		$list[]		= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'xmp', trim( $sql->sql ) ) );
	}

	if( $list ){
		$list		= UI_HTML_Tag::create( 'dl', join( $list ), array( 'class' => 'database' ) );
		$legend		= UI_HTML_Tag::create( 'legend', "Datenbank", array( 'class' => 'database' ) );
		$text		= UI_HTML_Tag::create( 'p', $words['update']['textDatabase'] );
		$fieldset	= UI_HTML_Tag::create( 'fieldset', $legend.$text.$list );
	}
}
return $fieldset;
?>
