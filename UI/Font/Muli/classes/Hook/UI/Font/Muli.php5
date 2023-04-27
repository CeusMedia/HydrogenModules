<?php
class Hook_UI_Font_Muli extends CMF_Hydrogen_Hook
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
	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, object $context, object $module, array & $payload ): void
	{
		$config	= $env->getConfig();
		$moduleConfig	= $config->getAll( 'module.ui_font_muli.', TRUE );
		if( $config->get( 'module.ui_font.active' ) ){
			if( $moduleConfig->get( 'active' ) ){
				$url    = $config->get( 'module.ui_font.uri' ).'Muli/muli.css';
                $env->getPage()->css->theme->addUrl( $url );
			}
		}
	}
}
