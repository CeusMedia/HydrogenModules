<?php
class Hook_Admin_Mail_Queue extends CMF_Hydrogen_Hook
{
	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onRegisterDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getAcl()->has( 'admin/mail/queue', 'ajaxRenderDashboardPanel' ) )
			return;
		$context->registerPanel( 'admin-mail-queue', array(
			'url'			=> 'admin/mail/queue/ajaxRenderDashboardPanel',
			'title'			=> 'E-Mail-Queue',
			'heading'		=> 'E-Mail-Queue',
			'icon'			=> 'fa fa-fw fa-envelope',
			'rank'			=> 70,
			'refresh'		=> 10,
		) );
	}
}
