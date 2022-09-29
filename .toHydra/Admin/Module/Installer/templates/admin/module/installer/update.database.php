<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/**
 *	@todo		after all Hydra instances are update:
 *	@todo		- remove manual scripts table rendering
 *	@todo		- remove css at the buttom
 *	@todo		- remove function fixVersionBug
 */

$fieldset	= '';

function fixVersionBug( $version ){
	return preg_replace( "/-pl?([0-9])/", ".0.\\1", $version );
}

$remote		= $env->getRemote();
if( $remote->getModules() && $remote->getModules()->has( 'Resource_Database' ) ){

	if( class_exists( 'View_Helper_Module_SqlScripts' ) ){
        $helper	= new View_Helper_Module_SqlScripts( $this->env );
        $table	= $helper->render( $sql );
	}
	else{
		$table	= '';
		$list	= [];
		foreach( $sql as $key => $step ){
			$version	= fixVersionBug( $step->version );
			$facts		= ['Version: '.$step->version, 'DBMS: '.$step->type];		//  collect facts
			$facts		= HtmlTag::create( 'b', join( ' | ', $facts ) );				//  render facts
			$mode		= $step->type === 'mysql' ? 'text/x-mysql' : 'text/x-sql';			//  decide SQL dialect by SQL update type
			$code		= htmlentities( trim( $step->sql ), ENT_QUOTES, 'UTF-8' );			//  escape SQL content
			$code		= HtmlTag::create( 'textarea', $code, array(					//  render textarea for CodeMirror
				'class'							=> 'CodeMirror-auto',						//  apply automatic CodeMirror
				'data-codemirror-read-only'		=> 'nocursor',								//  CodeMirror: set readonly
				'data-codemirror-mode'			=> $mode,									//  CodeMirror: set mode to SQL dialect
				'data-codemirror-height'		=> 'auto',									//  CodeMirror: adjust height to content
				'data-codemirror-line-wrapping'	=> 'true',									//  CodeMirror: enable to wrap long lines
			) );
			$cell		= HtmlTag::create( 'td', $facts.$code );						//  render table cell
			$list[]		= HtmlTag::create( 'tr', $cell );								//  append table row
		}
		if( $list )
			$table		= HtmlTag::create( 'table', join( $list ), ['class' => 'database'] );
	}

	if( $table ){
		$legend		= HtmlTag::create( 'legend', "Datenbank", ['class' => 'database'] );
		$text		= HtmlTag::create( 'small', $words['update']['textDatabase'], ['class' => 'muted'] );
		$fieldset	= HtmlTag::create( 'fieldset', $legend.$text.$table );
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
</style>';
?>
