<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_UI_Font_Roboto extends CMF_Hydrogen_Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload )
	{
		$config	= $env->getConfig();
		if( $config->get( 'module.ui_font.active' ) ){
			if( $config->get( 'module.ui_font_roboto.active' ) ){
				$url	= $config->get( 'module.ui_font.uri' ).'Roboto/roboto.css';
				$env->getPage()->css->theme->addUrl( $url );
			}
		}
	}
}
