<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Auth_Json extends CMF_Hydrogen_Hook
{
	protected static $configPrefix	= 'module.resource_authentication_backend_json.';

	public static function onAuthRegisterBackend( Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/json' );
		$context->registerBackend( 'Json', 'json', $words['backend']['title'] );
	}

	public static function onAuthRegisterLoginTab( Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words		= (object) $env->getLanguage()->getWords( 'auth/json' );						//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/json/login', $label, $rank );									//  register main tab
	}

/*	public static function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
{
		$userId		= (int) $env->getSession()->get( 'auth_user_id' );														//  get ID of current user (or zero)
		$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
//		$remember	= (bool) $cookie->get( 'auth_remember' );
//		$env->getSession()->set( 'isRemembered', $remember );
		$script		= 'Auth.init('.$userId.', false );';											//  initialize Auth class with user ID
		$env->getPage()->js->addScriptOnReady( $script, 1 );										//  enlist script to be run on ready
	}*/
}
