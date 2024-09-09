<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_MetaTags extends Hook
{
	public function onPageApplyModules(): void
	{
		$helper	= new View_Helper_MetaTags( $this->env );
		$helper->apply();
	}
}
