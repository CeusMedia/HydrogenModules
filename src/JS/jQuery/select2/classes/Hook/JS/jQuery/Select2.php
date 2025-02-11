<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_jQuery_Select2 extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$config		= $this->env->getConfig()->getAll( 'module.js_jquery_select2.', TRUE );
		$version	= $config->get( 'version' );
		$baseUrl	= $this->env->getConfig()->get( 'path.scripts.lib' ).'jquery/select2/'.$version.'/';
		$files		= (object) ['js' => [], 'css' => []];
		$minified	= $config->get( 'load.minified' );
		$full		= $config->get( 'load.full' );
		if( $config->get( 'version' ) === '3.5.4' ){
			$files->css[]	= 'select2.css';
			$files->css[]	= 'select2-bootstrap.css';
			$files->js[]	= $minified ? 'select2.min.js' : 'select2.js';
		}
		else if( $config->get( 'version' ) === '4.0.12' ){
			$suffixMinified	= $config->get( 'load.minified' ) ? '.min' : '';
			$suffixFull		= $config->get( 'load.full' ) ? '.full' : '';
			$files->css[]	= 'dist/css/select2'.$suffixMinified.'.css';
			$files->js[]	= 'dist/js/select2'.$suffixFull.$suffixMinified.'.js';
		}

		foreach( $files->js as $filePath )
			$this->context->js->addUrl( $baseUrl.$filePath );
		foreach( $files->css as $filePath )
			$this->context->css->theme->addUrl( $baseUrl.$filePath );
	}
}
