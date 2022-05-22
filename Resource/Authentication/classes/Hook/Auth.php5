<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Auth extends CMF_Hydrogen_Hook
{
	public static function onAppException( Environment $env, $context, $module, $payload = [] )
	{
		$payload	= (object) $payload;
		if( !property_exists( $payload, 'exception' ) )
			throw new Exception( 'No exception data given' );
		if( !( $payload->exception instanceof Exception ) )
			throw new Exception( 'Given exception data is not an exception object' );
		$request	= $env->getRequest();
		$session	= $env->getSession();
		if( $payload->exception->getCode() == 403 ){
			if( !$session->get( 'auth_user_id' ) ){
				$forwardUrl	= $request->get( '__controller' );
				if( $request->get( '__action' ) )
					$forwardUrl	.= '/'.$request->get( '__action' );
				if( $request->get( '__arguments' ) )
					foreach( $request->get( '__arguments' ) as $argument )
						$forwardUrl	.= '/'.$argument;
				$url	= $env->url.'auth/login?from='.$forwardUrl;
				Net_HTTP_Status::sendHeader( 403 );
				if( !$request->isAjax() )
					header( 'Location: '.$url );
				exit;
			}
		}
		return FALSE;
	}

	public static function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		$session	= $env->getSession();
		$userId		= (int) $session->get( 'auth_user_id' );										//  get ID of current user (or zero)
		if( $userId ){
			$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
			$remember	= (bool) $cookie->get( 'auth_remember' );
			$session->set( 'isRemembered', $remember );
			$script		= 'Auth.init('.$userId.','.json_encode( $remember ).');';					//  initialize Auth class with user ID
			$env->getPage()->js->addScriptOnReady( $script, 1 );									//  enlist script to be run on ready
		}
	}

	public static function onEnvInitAcl( Environment $env, $context, $module, $payload )
	{
		$payload	= (object) $payload;
//		$payload->className	= 'CMF_Hydrogen_Environment_Resource_Acl_Database';
		$payload->className	= 'Resource_Acl_Authentication';
		return TRUE;
	}
}
