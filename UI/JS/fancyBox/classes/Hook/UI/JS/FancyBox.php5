<?php
class Hook_UI_JS_FancyBox extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$config	= $env->getConfig()->getAll( 'module.ui_js_fancybox.', TRUE );
		if( !$config->get( 'active' ) )
			return;

		$context->js->addModuleFile( 'jquery.fancybox-3.3.5.min.js' );
		$context->css->addCommonStyle( 'jquery.fancybox-3.3.5.min.css' );

		if( $config->get( 'auto' ) ){
			$options	= $config->getAll( 'auto.option.' );
			$options['buttons']	= array();
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
