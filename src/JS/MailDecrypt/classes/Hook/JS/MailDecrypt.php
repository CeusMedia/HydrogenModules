<?php

use CeusMedia\HydrogenFramework\Environment\Resource\Page;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_MailDecrypt extends Hook
{
	public function onPageApplyModules(): void
	{
		/** @var Page $context */
		$context	= $this->context;

		$script		= 'MailDecrypt();';
		$context->js->addScriptOnReady( $script, 8 );
	}
}
