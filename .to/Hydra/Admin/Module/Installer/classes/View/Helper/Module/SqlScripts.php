<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Module_SqlScripts{

	public function fixVersionBug( $version ){
		return preg_replace( "/-pl?([0-9])/", ".0.\\1", $version );
	}

	public function render( $scripts ){
		if( !$scripts )
			return;
		$list	= [];
		foreach( $scripts as $key => $step ){
			$version	= $this->fixVersionBug( $step->version );
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
		return HtmlTag::create( 'table', join( $list ), ['class' => 'database'] );
	}
}
?>
