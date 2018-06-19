<?php
class Hook_Auth_Rest/* extends CMF_Hydrogen_Hook*/{

	static protected $configPrefix	= 'module.resource_authentication_backend_rest.';

	static public function onAuthRegisterBackend( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'enabled' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/rest' );
		$context->registerBackend( 'Rest', 'rest', $words['backend']['title'] );
	}

	static public function onAuthRegisterLoginTab( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'enabled' ) )
			return;
		$words		= (object) $env->getLanguage()->getWords( 'auth/rest' );					//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/rest/login', $label, $rank );								//  register main tab
	}

/*	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$userId		= (int) $env->getSession()->get( 'userId' );								//  get ID of current user (or zero)
		$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
//		$remember	= (bool) $cookie->get( 'auth_remember' );
//		$env->getSession()->set( 'isRemembered', $remember );
		$script		= 'Auth.init('.$userId.', false);';											//  initialize Auth class with user ID
		$env->getPage()->js->addScriptOnReady( $script, 1 );									//  enlist script to be run on ready
	}*/
}
