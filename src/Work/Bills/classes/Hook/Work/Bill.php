<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Bill extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'work/bill' );							//  load words
		$this->context->registerTab( '', $words->tabs['list'], 0 );										//  register main tab
		$this->context->registerTab( 'graph', $words->tabs['graph'], 5 );										//  register graph tab
	}
}