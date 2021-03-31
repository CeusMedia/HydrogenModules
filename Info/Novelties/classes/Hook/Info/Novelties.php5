<?php
class Hook_Info_Novelties extends CMF_Hydrogen_Hook
{
	public static function onRegisterDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		if( !$env->getAcl()->has( 'info/novelty', 'ajaxRenderDashboardPanel' ) )
			return;
		$context->registerPanel( 'info-novelty', array(
			'title'			=> 'Neuigkeiten',
			'heading'		=> 'Neuigkeiten',
			'url'			=> './info/novelty/ajax/renderDashboardPanel',
			'rank'			=> 0,
			'refresh'		=> 10,
		) );
	}
}
