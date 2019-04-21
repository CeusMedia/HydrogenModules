<?php
class Hook_UI_Font_OpenSans extends CMF_Hydrogen_Hook{

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$config	= $env->getConfig();
		if( $config->get( 'module.ui_font.active' ) ){
			if( $config->get( 'module.ui_font_opensans.active' ) ){
				$url	= $config->get( 'module.ui_font.uri' ).'OpenSans/opensans.css';
				$env->getPage()->css->theme->addUrl( $url );
			}
		}
	}
}
