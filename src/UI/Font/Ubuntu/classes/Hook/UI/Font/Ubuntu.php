<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Font_Ubuntu extends Hook
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
		if( $config->get( 'module.ui_font.active' ) ){
			if( $config->get( 'module.ui_font_ubuntu.active' ) ){
				$url	= $config->get( 'module.ui_font.uri' ).'Ubuntu/ubuntu.css';
				$env->getPage()->css->theme->addUrl( $url );
			}
		}
	}
}
