<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Auth_Oauth extends Hook
{
	protected static $configPrefix	= 'module.resource_authentication_backend_oauth.';

	public static function onAuthRegisterBackend( Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/oauth' );
		$context->registerBackend( 'Oauth', 'oauth', $words['backend']['title'] );
	}

	public static function onAuthRegisterLoginTab( Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words		= (object) $env->getLanguage()->getWords( 'auth/oauth' );						//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/oauth/login', $label, $rank );									//  register main tab
	}
}
