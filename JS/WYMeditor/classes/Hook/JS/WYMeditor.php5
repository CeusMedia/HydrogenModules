<?php
class Hook_JS_WYMeditor extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$options = json_encode( array(
			'containersHtml'	=> '',
			'classesHtml'		=> '',
			'logoHtml'			=> '',
			'statusHtml'		=> '',
		) );
		$script	= 'jQuery("textarea.WYMeditor").wymeditor('.$options.')';
		$context->js->addScriptOnReady( $script );
	}
}
