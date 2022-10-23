<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Helper_HTML extends Hook
{
	/**
	 *	@param		Environment		$env		Environment object
	 *	@static
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( Environment $env, $context, $module, $payload )
	{
		new View_Helper_HTML;
	}
}
