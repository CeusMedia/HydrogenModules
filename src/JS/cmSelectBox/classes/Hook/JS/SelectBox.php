<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_SelectBox extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$options	= [];
		$script		= 'jQuery("select.cmSelectBox.above").cmSelectBox('.json_encode( array_merge( $options, ['inverted' => TRUE] ) ).')';
		$this->context->js->addScriptOnReady( $script );

		$script		= 'jQuery("select.cmSelectBox").not(".above").cmSelectBox('.json_encode( $options ).')';
		$this->context->js->addScriptOnReady( $script );
	}
}
