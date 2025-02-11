<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_jQuery_Equalize extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		if( !$this->module->config['auto']->value )
			return;
		if( !( $selector = $this->module->config['auto.selector']->value ) )
			return;
		$params	= json_encode( [
			'equalize'	=> $this->module->config['auto.dimension']->value,
			'reset'		=> $this->module->config['auto.reset']->value
		] );
		$this->context->js->addScriptOnReady( 'jQuery("'.$selector.'").equalize('.$params.');' );
	}
}
