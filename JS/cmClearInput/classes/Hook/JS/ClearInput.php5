<?php
class Hook_JS_ClearInput extends CMF_Hydrogen_Hook{

	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload ){
		$options	= array();												//  @todo add offsets to config and apply here
        $options	= json_encode( $options );								//  encode options to JSON
		$selector	= '.cmClearInput';										//  @todo add default auto selector to config and apply here
		$script		= '$("'.$selector.'").cmClearInput('.$options.');';		//  render script
		$context->js->addScriptOnReady( $script, 9 );						//  enlist script to be run on ready
	}
}
