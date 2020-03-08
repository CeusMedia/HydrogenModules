<?php
class Hook_JS_Imagnifier extends CMF_Hydrogen_Hook
{
	public static function onPageApplyModules( $env, $context, $module, $payload ){
		$options	= (object) $env->getConfig()->getAll( 'module.js_cmimagnifier.' );
		$script		= '$("img.cmImagnifier").cmImagnifier('.json_encode( $options ).');';
		$script		= '$(document).ready(function(){'.$script.'});';
		$context->js->addScript( $script );
	}
}
