<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_WYMeditor extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$options = json_encode( [
			'containersHtml'	=> '',
			'classesHtml'		=> '',
			'logoHtml'			=> '',
			'statusHtml'		=> '',
		] );
		$script	= 'jQuery("textarea.WYMeditor").wymeditor('.$options.')';
		$this->context->js->addScriptOnReady( $script );
	}
}
