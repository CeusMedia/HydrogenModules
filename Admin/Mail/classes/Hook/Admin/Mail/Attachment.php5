<?php
class Hook_Admin_Mail_Attachment extends CMF_Hydrogen_Hook
{
	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		$words	= (object) $env->getLanguage()->getWords( 'admin/mail/attachment' );				//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );										//  register main tab
		$context->registerTab( 'add', $words->tabs['add'], 1 );										//  register add tab
		$context->registerTab( 'upload', $words->tabs['upload'], 2 );								//  register upload tab
	}
}
