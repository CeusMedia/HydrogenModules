<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Imagnifier extends Hook
{
	public function onPageApplyModules(): void
	{
		$options	= (object) $this->env->getConfig()->getAll( 'module.js_cmimagnifier.' );
		$script		= 'jQuery("img.cmImagnifier").cmImagnifier('.json_encode( $options ).');';
		$script		= 'jQuery(document).ready(function(){'.$script.'});';
		$this->context->js->addScript( $script );
	}
}
