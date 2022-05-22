<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Auth_Oauth2 extends CMF_Hydrogen_Hook
{
	protected static $configPrefix	= 'module.resource_authentication_backend_oauth2.';

	public static function onAuthRegisterBackend( Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/oauth2' );
		$context->registerBackend( 'Oauth2', 'oauth2', $words['backend']['title'] );
	}

	public static function onAuthRegisterLoginTab( Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
//		if( !$env->getConfig()->get( self::$configPrefix.'loginTab' ) )
//			return;
		if( $env->getConfig()->get( self::$configPrefix.'loginMode' ) !== 'tab' )
			return;

		$words		= (object) $env->getLanguage()->getWords( 'auth/oauth2' );						//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/oauth2/login', $label, $rank );									//  register main tab
	}
}
