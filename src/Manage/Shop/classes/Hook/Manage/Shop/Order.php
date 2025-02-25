<?php
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Shop_Order extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/shop' );				//  load words
		$this->context->registerTab( 'order', $words->tabs['orders'], 1 );							//  register orders tab
//		$this->context->registerTab( 'shipping', $words->tabs['shipping'], 5 );						//  register shipping tab
	}
}