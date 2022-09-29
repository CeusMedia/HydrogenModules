<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Admin_Mail_Attachment extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onRegisterTab( Environment $env, object $context, object $module, array & $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'admin/mail/attachment' );				//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );										//  register main tab
		$context->registerTab( 'add', $words->tabs['add'], 1 );										//  register add tab
		$context->registerTab( 'folder', $words->tabs['folder'], 2 );									//  register files tab
//		$context->registerTab( 'upload', $words->tabs['upload'], 2 );								//  register upload tab
	}
}
