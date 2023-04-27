<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_ClearInput extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$options	= [];															//  @todo add offsets to config and apply here
		$options	= json_encode( $options );										//  encode options to JSON
		$selector	= '.cmClearInput';												//  @todo add default auto selector to config and apply here
		$script		= 'jQuery("'.$selector.'").cmClearInput('.$options.');';		//  render script
		$this->context->js->addScriptOnReady( $script, 9 );							//  enlist script to be run on ready
	}
}
