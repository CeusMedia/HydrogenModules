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
		if( !$this->env->getAcl()->has( 'ajax/info/novelty', 'renderDashboardPanel' ) )
			return;
		$words	= $this->env->getLanguage()->getSection( 'info/novelty', 'dashboard' );
		$this->context->registerPanel( 'info-novelty', [
			'title'			=> $words['title'],
			'heading'		=> $words['heading'],
			'url'			=> './ajax/info/novelty/renderDashboardPanel',
			'rank'			=> 0,
			'refresh'		=> 10,
		] );
	}
}
