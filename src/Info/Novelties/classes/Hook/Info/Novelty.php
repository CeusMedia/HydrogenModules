<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Novelty extends Hook
{
	/**
	 *	@return		void
	 */
	public function onRegisterDashboardPanels(): void
	{
		if( !$this->env->getAcl()->has( 'info/novelty', 'ajaxRenderDashboardPanel' ) )
			return;
		$this->context->registerPanel( 'info-novelty', [
			'title'			=> 'Neuigkeiten',
			'heading'		=> 'Neuigkeiten',
			'url'			=> './info/novelty/ajax/renderDashboardPanel',
			'rank'			=> 0,
			'refresh'		=> 10,
		] );
	}
}
