<?php
class View_Helper_Module_SqlScripts{

	public function fixVersionBug( $version ){
		return preg_replace( "/-pl?([0-9])/", ".0.\\1", $version );
	}

	public function render( $scripts ){
		if( !$scripts )
			return;
		$list	= array();
		foreach( $scripts as $key => $step ){
			$version	= $this->fixVersionBug( $step->version );
			$facts		= array( 'Version: '.$step->version, 'DBMS: '.$step->type );		//  collect facts
			$facts		= UI_HTML_Tag::create( 'b', join( ' | ', $facts ) );				//  render facts
			$mode		= $step->type === 'mysql' ? 'text/x-mysql' : 'text/x-sql';			//  decide SQL dialect by SQL update type
			$code		= htmlentities( trim( $step->sql ), ENT_QUOTES, 'UTF-8' );			//  escape SQL content
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
		return UI_HTML_Tag::create( 'table', join( $list ), array( 'class' => 'database' ) );
	}
}
?>
