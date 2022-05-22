<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_UI_Helper_Input_File extends CMF_Hydrogen_Hook
{
	/**
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@static
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload )
	{
		$context->js->addScriptOnReady( 'setupInputFileUpload()' );
	}
}
