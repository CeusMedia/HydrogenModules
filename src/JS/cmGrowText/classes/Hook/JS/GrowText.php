<?php

use CeusMedia\HydrogenFramework\Environment\Resource\Page;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_GrowText extends Hook
{
	public function onPageApplyModules(): void
	{
		/** @var Page $context */
		$context	= $this->context;

		$options	= [];
		$script		= '$(".cmGrowText").cmGrowText('.json_encode( $options ).')';
		$context->js->addScriptOnReady( $script );
	}
}
