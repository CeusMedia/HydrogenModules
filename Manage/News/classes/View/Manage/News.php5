<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_News extends View{

	public function add(){}

	public function edit(){}

	public function index(){}

	static public function getEditorClass( $env ){
		$messenger		= $env->getMessenger();
		$modulesConfig	= $env->getConfig()->getAll( 'module.', TRUE );
		$editorClass	= $modulesConfig->get( 'js_tinymce.auto.selector' );
		switch( $modulesConfig->get( 'manage_news.editor' ) ){
			case 'Ace':
				if( $env->getModules()->has( 'JS_Ace' ) )
					$editorClass	= $modulesConfig->get( 'js_ace.auto.selector' );
				else
					$messenger->noteFailure( 'Module "JS_Ace" is not installed. Using "JS_TinyMCE" instead.' );
				break;
			case 'CodeMirror':
				if( $env->getModules()->has( 'JS_CodeMirror' ) )
					$editorClass	= $modulesConfig->get( 'js_codemirror.auto.selector' );
				else
					$messenger->noteFailure( 'Module "JS_CodeMirror" is not installed. Using "JS_TinyMCE" instead.' );
				break;
		}
		return str_replace( 'textarea.', '', $editorClass );
	}
}
?>
