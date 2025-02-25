<?php
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Shop_Bridge extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/shop/bridge' );			//  load words
		$this->context->registerTab( 'bridge', $words->tabs['bridges'], 15 );					//  register orders tab
	}
}