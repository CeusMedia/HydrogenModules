<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Admin_Mail_Attachment extends Hook
{
	/**
	 *	@return		void
	 */
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'admin/mail/attachment' );	//  load words
		$this->context->registerTab( '', $words->tabs['index'], 0 );								//  register main tab
		$this->context->registerTab( 'add', $words->tabs['add'], 1 );								//  register add tab
		$this->context->registerTab( 'folder', $words->tabs['folder'], 2 );							//  register files tab
//		$this->context->registerTab( 'upload', $words->tabs['upload'], 2 );							//  register upload tab
	}
}
