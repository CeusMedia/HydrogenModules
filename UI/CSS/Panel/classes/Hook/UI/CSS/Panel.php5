<?php
class Hook_UI_CSS_Panel extends CMF_Hydrogen_Hook
{
	/**
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@static
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$options	= $env->getConfig()->getAll( 'module.ui_css_panel.', TRUE );
		if( $options->get( 'active' ) ){
			$context->addBodyClass( 'content-panel-style-'.$options->get( 'style' ) );
			$context->addCommonStyle( 'layout.panels.css' );
		}
	}
}
