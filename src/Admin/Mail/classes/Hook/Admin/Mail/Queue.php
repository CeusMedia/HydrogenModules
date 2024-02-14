<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Admin_Mail_Queue extends Hook
{
	/**
	 *	@return		void
	 */
	public function onRegisterDashboardPanels(): void
	{
		if( !$this->env->getAcl()->has( 'admin/mail/queue', 'ajaxRenderDashboardPanel' ) )
			return;
		$this->context->registerPanel( 'admin-mail-queue', [
			'url'			=> 'admin/mail/queue/ajaxRenderDashboardPanel',
			'title'			=> 'E-Mail-Queue',
			'heading'		=> 'E-Mail-Queue',
			'icon'			=> 'fa fa-fw fa-envelope',
			'rank'			=> 70,
			'refresh'		=> 10,
		] );
	}
}
