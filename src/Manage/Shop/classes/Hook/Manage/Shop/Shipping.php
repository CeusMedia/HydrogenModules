<?php
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Shop_Shipping extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/shop' );			//  load words
		$this->context->registerTab( 'shipping', $words->tabs['shipping'], 6 );					//  register report tab
	}
}