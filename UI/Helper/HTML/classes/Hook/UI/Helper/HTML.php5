<?php
class Hook_UI_Helper_HTML extends CMF_Hydrogen_Hook
{
	/**
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@static
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		new View_Helper_HTML;
	}
}
