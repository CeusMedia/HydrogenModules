<?php
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Shop_Report extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/shop' );			//  load words
		$this->context->registerTab( 'report', $words->tabs['reports'], 8 );					//  register report tab
	}
}