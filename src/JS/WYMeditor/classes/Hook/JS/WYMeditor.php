<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_WYMeditor extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		$options = json_encode( [
			'containersHtml'	=> '',
			'classesHtml'		=> '',
			'logoHtml'			=> '',
			'statusHtml'		=> '',
		] );
		$script	= 'jQuery("textarea.WYMeditor").wymeditor('.$options.')';
		$context->js->addScriptOnReady( $script );
	}
}
