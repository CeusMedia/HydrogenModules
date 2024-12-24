<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Admin_Mail_Queue extends Hook
{
	/**
	 *	@return		void
	 */
	public function onRegisterDashboardPanels(): void
	{
		if( !$this->env->getAcl()->has( 'ajax/admin/mail/queue', 'renderDashboardPanel' ) )
			return;
		$this->context->registerPanel( 'admin-mail-queue', [
			'url'			=> 'ajax/admin/mail/queue/renderDashboardPanel',
			'title'			=> 'E-Mail-Queue',
			'heading'		=> 'E-Mail-Queue',
			'icon'			=> 'fa fa-fw fa-envelope',
			'rank'			=> 70,
			'refresh'		=> 10,
		] );
	}
}
