<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Panel extends Hook
{
	public function onPageApplyModules(): void
	{
		$this->context->js->addScriptOnReady( '$(".panel.collapsable").cmCollapsePanel();' );
	}
}