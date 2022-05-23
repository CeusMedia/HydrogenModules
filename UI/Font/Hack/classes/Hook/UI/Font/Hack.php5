<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Font_Hack extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload )
	{
		$config	= $env->getConfig();
		$moduleConfig	= $config->getAll( 'module.ui_font_hack.', TRUE );
		if( $config->get( 'module.ui_font.active' ) ){
			if( $moduleConfig->get( 'active' ) ){
				$file	= $moduleConfig->get( 'set' ) === 'extended' ? 'hack-extended' : 'hack';
				$path	= $config->get( 'module.ui_font.uri' ).'Hack/';
				if( $moduleConfig->get( 'source' ) === 'CDN' )
					$path	= $moduleConfig->get( 'URI.CDN' );
				$env->getPage()->css->theme->addUrl( $path.$file.'.min.css' );
			}
		}
	}
}
