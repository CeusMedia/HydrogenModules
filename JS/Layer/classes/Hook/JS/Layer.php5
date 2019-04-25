<?php
class Hook_JS_Layer extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 *	@todo		implement module main switch
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$config	= $env->getConfig()->getAll( 'module.js_layer.' );
//		if( !$config->get( 'active' ) )
//			return;

		$buttonDownload	= $config['button.image.download'] == 'yes' ? 'true' : 'false';
		$buttonInfo		= $config['button.image.info'] == 'yes' ? 'true' : 'false';
		$script	= '
Layer.init();
Layer.buttonImageDownload = '.$buttonDownload.';
Layer.buttonImageInfo = '.$buttonInfo.';
Layer.speedShow = '.(int) $config['speed.show'].';
Layer.speedHide = '.(int) $config['speed.hide'].';
Layer.labelButtonPrev = '.json_encode( $config['button.image.prev.label'] ).';
Layer.labelButtonNext = '.json_encode( $config['button.image.next.label'] ).';
Layer.labelButtonInfo = '.json_encode( $config['button.image.info.label'] ).';
Layer.labelButtonLoad = '.json_encode( $config['button.image.load.label'] ).';
';
		$context->js->addScriptOnReady( $script );
	}
}
