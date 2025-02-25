<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_My_User_Oauth2 extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user/oauth2' );		//  load words
		$this->context->registerTab( 'oauth2', $words->module['tab'], 5 );						//  register main tab
	}
}
