<?php
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Font_Signika extends Hook
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
	public static function onPageApplyModules( Environment $env, object $context, object $module, array & $payload ): void
	{
		$config			= $env->getConfig();
		$moduleConfig	= $config->getAll( 'module.ui_font_signika.', TRUE );
		if( $config->get( 'module.ui_font.active' ) ){
			if( $moduleConfig->get( 'active' ) ){
				$url	= $config->get( 'module.ui_font.uri' ).'Signika/signika.css';
				$env->getPage()->css->theme->addUrl( $url );
			}
		}
	}
}
