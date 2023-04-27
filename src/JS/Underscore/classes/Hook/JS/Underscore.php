<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Underscore extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$this->context->addBodyClass( 'uses-underscore' );
	}
}
