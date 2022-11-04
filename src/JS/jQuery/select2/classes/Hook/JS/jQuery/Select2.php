<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_jQuery_Select2 extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		$config		= $env->getConfig()->getAll( 'module.js_jquery_select2.', TRUE );
		$version	= $config->get( 'version' );
		$baseUrl	= $env->getConfig()->get( 'path.scripts.lib' ).'jquery/select2/'.$version.'/';
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
			$context->js->addUrl( $baseUrl.$filePath );
		foreach( $files->css as $filePath )
			$context->css->theme->addUrl( $baseUrl.$filePath );
	}
}
