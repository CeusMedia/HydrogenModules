<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Novelties extends Hook
{
	public static function onRegisterDashboardPanels( Environment $env, $context, $module, $payload )
	{
		if( !$env->getAcl()->has( 'info/novelty', 'ajaxRenderDashboardPanel' ) )
			return;
		$context->registerPanel( 'info-novelty', [
			'title'			=> 'Neuigkeiten',
			'heading'		=> 'Neuigkeiten',
			'url'			=> './info/novelty/ajax/renderDashboardPanel',
			'rank'			=> 0,
			'refresh'		=> 10,
		] );
	}
}
