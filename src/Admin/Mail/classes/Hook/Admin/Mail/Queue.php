<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Admin_Mail_Queue extends Hook
{
	/**
	 *	@return		void
	 */
	public function onRegisterDashboardPanels( ): void
	{
		if( !$this->env->getAcl()->has( 'ajax/admin/mail/queue', 'renderDashboardPanel' ) )
			return;
		$w	= (object) $this->env->getLanguage()->getSection( 'admin/mail/queue', 'dashboard' );
		$this->context->registerPanel( 'admin-mail-queue', [
			'url'			=> 'ajax/admin/mail/queue/renderDashboardPanel',
			'title'			=> $w->heading,
			'heading'		=> $w->heading,
			'icon'			=> 'fa fa-fw fa-envelope',
			'rank'			=> 70,
			'refresh'		=> 10,
		] );
	}
}
