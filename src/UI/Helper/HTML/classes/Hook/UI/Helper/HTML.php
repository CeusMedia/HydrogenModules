<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Helper_HTML extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		new View_Helper_HTML;
	}
}
