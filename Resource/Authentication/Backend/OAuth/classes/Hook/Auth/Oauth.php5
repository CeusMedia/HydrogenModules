<?php
class Hook_Auth_Oauth/* extends CMF_Hydrogen_Hook*/{

	static protected $configPrefix	= 'module.resource_authentication_backend_oauth.';

	static public function onAuthRegisterBackend( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'enabled' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/oauth' );
		$context->registerBackend( 'Oauth', 'oauth', $words['backend']['title'] );
	}

	static public function onAuthRegisterLoginTab( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'enabled' ) )
			return;
		$words		= (object) $env->getLanguage()->getWords( 'auth/oauth' );						//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/oauth/login', $label, $rank );									//  register main tab
	}
}
