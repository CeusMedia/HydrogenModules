<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_jQuery extends Hook
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
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$version	= $module->config['version']->value;
		$minified	= $module->config['load.minified']->value;
		if( $minified ){
			$context->addJavaScript( $pathJs.'jquery-'.$version.'.min.js' );
			if( $module->config['load.map']->value ){
				$versions	= ['1.10.2', '1.11.1', '3.3.1'];
				if( in_array( $version, $versions ) )
					$context->js->addUrl( $pathJs.'jquery-'.$version.'.min.map', 9 );
			}
		}
		else
			$context->addJavaScript( $pathJs.'jquery-'.$version.'.js' );

		if( $module->config['migrate']->value ){
			$debug	= $module->config['migrate.debug']->value;
			if( $debug === "off" || $debug === "auto" && $minified )
				$context->addJavaScript( $pathJs.'jquery-migrate-3.0.1.min.js' );
			else
				$context->addJavaScript( $pathJs.'jquery-migrate-3.0.1.js' );
		}
		$context->addBodyClass( 'uses-jQuery' );
	}
}