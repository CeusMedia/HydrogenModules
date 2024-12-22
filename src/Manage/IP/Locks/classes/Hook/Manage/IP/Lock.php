<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_IP_Lock extends Hook
{
	public function onRegisterTab(): void
	{
		$words  = (object) $this->env->getLanguage()->getWords( 'manage/ip/lock' );				//  load words
		$this->context->registerTab( '', $words->tabs['index'], 0 );									//  register main tab
		$this->context->registerTab( 'filter', $words->tabs['filter'], 3 );								//  register filter tab
		$this->context->registerTab( 'reason', $words->tabs['reason'], 4 );								//  register reason tab
		$this->context->registerTab( 'transport', $words->tabs['transport'], 8 );						//  register transport tab
	}
}
