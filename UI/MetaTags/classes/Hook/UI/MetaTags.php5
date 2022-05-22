<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_UI_MetaTags extends CMF_Hydrogen_Hook
{
	/**
	 *	@param		Environment		$env		Environment object
	 *	@static
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload )
	{
		$helper	= new View_Helper_MetaTags( $env );
		$helper->apply();
	}
}
