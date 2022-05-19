<?php
class Hook_JS_ClearInput extends CMF_Hydrogen_Hook
{
	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		$options	= [];													//  @todo add offsets to config and apply here
		$options	= json_encode( $options );									//  encode options to JSON
		$selector	= '.cmClearInput';											//  @todo add default auto selector to config and apply here
		$script		= 'jQuery("'.$selector.'").cmClearInput('.$options.');';	//  render script
		$context->js->addScriptOnReady( $script, 9 );							//  enlist script to be run on ready
	}
}
