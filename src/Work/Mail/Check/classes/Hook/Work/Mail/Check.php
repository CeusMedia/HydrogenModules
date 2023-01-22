<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Mail_Check extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'work/mail/check' );			//  load words
		$this->context->registerTab( '', $words->tabs['index'], 0 );								//  register main tab
		$this->context->registerTab( 'group', $words->tabs['group'], 1 );							//  register main tab
		$this->context->registerTab( 'import', $words->tabs['import'], 2 );							//  register main tab
		$this->context->registerTab( 'export', $words->tabs['export'], 3 );							//  register main tab
	}
}
