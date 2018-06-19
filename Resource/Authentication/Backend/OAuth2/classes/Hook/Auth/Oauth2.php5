<?php
class Hook_Auth_Oauth2/* extends CMF_Hydrogen_Hook*/{

	static protected $configPrefix	= 'module.resource_authentication_backend_oauth2.';

	static public function onAuthRegisterBackend( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'enabled' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/oauth2' );
		$context->registerBackend( 'Oauth2', 'oauth2', $words['backend']['title'] );
	}

	static public function onAuthRegisterLoginTab( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'enabled' ) )
			return;
		$words		= (object) $env->getLanguage()->getWords( 'auth/oauth2' );						//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/oauth2/login', $label, $rank );									//  register main tab
	}
}
