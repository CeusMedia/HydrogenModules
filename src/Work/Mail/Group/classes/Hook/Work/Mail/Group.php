<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Mail_Group extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'work/mail/group' );			//  load words
		$this->context->registerTab( '', $words->tabs['group'], 0 );								//  register main tab
	//	$this->context->registerTab( 'member', $words->tabs['members'], 1 );						//  register members tab
		$this->context->registerTab( 'server', $words->tabs['servers'], 2 );						//  register servers tab
		$this->context->registerTab( 'role', $words->tabs['roles'], 3 );							//  register roles tab
		$this->context->registerTab( 'message', $words->tabs['messages'], 4 );						//  register messages tab
	}
}
