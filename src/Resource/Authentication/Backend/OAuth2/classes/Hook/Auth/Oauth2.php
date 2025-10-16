<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Auth_Oauth2 extends Hook
{
	protected static $configPrefix	= 'module.resource_authentication_backend_oauth2.';

	public function onAuthRegisterBackend(): void
	{
		if( !$this->env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words	= $this->env->getLanguage()->getWords( 'auth/oauth2' );
		$this->context->registerBackend( 'Oauth2', 'oauth2', $words['backend']['title'] );
	}

	public function onAuthRegisterLoginTab(): void
	{
		if( !$this->env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
//		if( !$this->env->getConfig()->get( self::$configPrefix.'loginTab' ) )
//			return;
		if( $this->env->getConfig()->get( self::$configPrefix.'loginMode' ) !== 'tab' )
			return;

		$words		= (object) $this->env->getLanguage()->getWords( 'auth/oauth2' );						//  load words
		$rank		= $this->env->getConfig()->get( self::$configPrefix.'login.rank' );
		$label		= $words->login['tab'];
		$this->context->registerTab( 'auth/oauth2/login', $label, $rank );									//  register main tab
	}
}
