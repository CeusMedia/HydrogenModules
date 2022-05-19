<?php
class Hook_Auth_Oauth extends CMF_Hydrogen_Hook
{
	protected static $configPrefix	= 'module.resource_authentication_backend_oauth.';

	public static function onAuthRegisterBackend( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/oauth' );
		$context->registerBackend( 'Oauth', 'oauth', $words['backend']['title'] );
	}

	public static function onAuthRegisterLoginTab( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words		= (object) $env->getLanguage()->getWords( 'auth/oauth' );						//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/oauth/login', $label, $rank );									//  register main tab
	}
}
