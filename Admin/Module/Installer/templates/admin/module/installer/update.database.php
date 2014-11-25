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
		if( $sql->event === "update" ){																//  sql part is an update
			if( version_compare( $versionStep, $versionCurrent, '>' ) ){							//  sql part is newer than current version
				if( version_compare( $versionStep, $versionTarget, '<=' ) ){						//  sql part is older or related to new version
					$facts		= array( 'Version: '.$sql->version, 'DBMS: '.$sql->type );			//  collect facts
					$facts		= UI_HTML_Tag::create( 'b', join( ' | ', $facts ) );				//  render facts
					$mode		= $sql->type === 'mysql' ? 'text/x-mysql' : 'text/x-sql';			//  decide SQL dialect by SQL update type
					$code		= htmlentities( trim( $sql->sql ), ENT_QUOTES, 'UTF-8' );			//  escape SQL content
					$code		= UI_HTML_Tag::create( 'textarea', $code, array(					//  render textarea for CodeMirror
						'class'							=> 'CodeMirror-auto',						//  apply automatic CodeMirror
						'data-codemirror-read-only'		=> 'nocursor',								//  CodeMirror: set readonly
						'data-codemirror-mode'			=> $mode,									//  CodeMirror: set mode to SQL dialect
						'data-codemirror-height'		=> 'auto',									//  CodeMirror: adjust height to content
						'data-codemirror-line-wrapping'	=> 'true',									//  CodeMirror: enable to wrap long lines
					) );
					$cell		= UI_HTML_Tag::create( 'td', $facts.$code );						//  render table cell
					$list[]		= UI_HTML_Tag::create( 'tr', $cell );								//  append table row
				}
			}
		}
	}

	if( $list ){
		$list		= UI_HTML_Tag::create( 'table', join( $list ), array( 'class' => 'database' ) );
		$legend		= UI_HTML_Tag::create( 'legend', "Datenbank", array( 'class' => 'database' ) );
		$text		= UI_HTML_Tag::create( 'small', $words['update']['textDatabase'], array( 'class' => 'muted' ) );
		$fieldset	= UI_HTML_Tag::create( 'fieldset', $legend.$text.$list );
	}
}
return $fieldset.'
<style>
table.database {
	border: none !important;
	table-layout: fixed;
	}
table.database td {
	border: none !important;
	}
table.database .CodeMirror {
	height: auto;
	}
table.database .CodeMirror-scroll {
	overflow-y: hidden;
	overflow-x: auto;
	}
</style>
<script>

</script>';
?>
