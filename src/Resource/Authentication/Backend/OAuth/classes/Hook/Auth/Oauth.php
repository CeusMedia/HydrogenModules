<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Auth_Oauth extends Hook
{
	protected static string $configPrefix	= 'module.resource_authentication_backend_oauth.';

	public function onAuthRegisterBackend(): void
	{
		if( !$this->env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words	= $this->env->getLanguage()->getWords( 'auth/oauth' );
		$this->context->registerBackend( 'Oauth', 'oauth', $words['backend']['title'] );
	}

	public function onAuthRegisterLoginTab(): void
	{
		$config	= $this->env->getConfig();
		if( !$config->get( self::$configPrefix.'active' ) )
			return;
		$words		= (object) $this->env->getLanguage()->getWords( 'auth/oauth' );						//  load words
		$rank		= $config->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$this->context->registerTab( 'auth/oauth/login', $label, $rank );									//  register main tab
	}
}
