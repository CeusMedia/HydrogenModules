<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Storage extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		if( !$this->module->config['active']->value )
			return;
		$pathJs		= $this->env->getConfig()->get( 'path.scripts' );
		$fileSuffix	= $this->module->config['load.minified']->value ? '.min' : '';
		$this->context->js->addUrl( $pathJs.'js.storage'.$fileSuffix.'.js', 3 );
	}
}
