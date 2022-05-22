<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_UI_JS_FancyBox extends CMF_Hydrogen_Hook
{
	/**
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@static
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload )
	{
		$config	= $env->getConfig()->getAll( 'module.ui_js_fancybox.', TRUE );
		if( !$config->get( 'active' ) )
			return;

		$context->js->addModuleFile( 'jquery.fancybox-3.3.5.min.js' );
		$context->css->common->addUrl( 'jquery.fancybox-3.3.5.min.css' );

		if( $config->get( 'auto' ) ){
			$options	= $config->getAll( 'auto.option.' );
			$options['buttons']	= [];
			foreach( $config->getAll( 'auto.option.button.' ) as $button => $enabled  ){
				unset( $options['button.'.$button] );
				$enabled ? ( $options['buttons'][] = $button ) : NULL;
			}
			$script		= vsprintf( 'jQuery(".%s").fancybox(%s);', array(
				$config->get( 'auto.class' ),
				json_encode( $options ),
			) );
			$context->js->addScriptOnReady( $script );
		}
	}
}
