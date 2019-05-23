<?php
class Hook_JS_SelectBox extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$options	= array();
		$script		= 'jQuery("select.cmSelectBox.above").cmSelectBox('.json_encode( array_merge( $options, array( 'inverted' => TRUE ) ) ).')';
		$context->js->addScriptOnReady( $script );

		$script		= 'jQuery("select.cmSelectBox").not(".above").cmSelectBox('.json_encode( $options ).')';
		$context->js->addScriptOnReady( $script );
	}
}
