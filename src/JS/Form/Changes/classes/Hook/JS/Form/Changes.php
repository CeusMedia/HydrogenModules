<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Form_Changes extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$this->env->getPage()->js->addScriptOnReady( 'UI.Form.Changes.init();', 9 );
	}
}
