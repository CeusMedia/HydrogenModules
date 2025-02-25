<?php
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Shop extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/shop' );				//  load words
		$this->context->registerTab( '', $words->tabs['dashboard'], 0 );							//  register orders tab
	}
}