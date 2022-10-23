<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_MetaTags extends Hook
{
	/**
	 *	@param		Environment		$env		Environment object
	 *	@static
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, object $context, $module )
	{
		$helper	= new View_Helper_MetaTags( $env );
		$helper->apply();
	}
}
