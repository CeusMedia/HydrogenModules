<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Log_Sentry extends Hook
{
	public static function onEnvInitModules( Environment $env, $context, $module, $payload )
	{
		$appConfig		= $this->env->getConfig()->getAll( 'app.', TRUE );
		$moduleConfig	= $env->getConfig()->getAll( 'module.server_log_sentry.', TRUE );
		if( $moduleConfig->get( 'active' ) ){
			Sentry\init( [
				'dsn'				=> $moduleConfig->get( 'dsn' ),
				'attach_stacktrace'	=> TRUE,
				'environment'		=> $appConfig->get( 'environment' ),
				'release'			=> $appConfig->get( 'release' ),
			] );
		}
		return !TRUE;															//  mark hook as unhandled
	}

	public static function onEnvLogException( Environment $env, $context, $module, $payload )
	{
		$data			= (array) $payload;
		$moduleConfig	= $env->getConfig()->getAll( 'module.server_log_sentry.', TRUE );
		if( array_key_exists( 'exception', $data, TRUE ) )
			if( $data['exception'] instanceof Exception )
				if( $moduleConfig->get( 'active' ) )
	    	        Sentry\captureException( $data['exception'] );
		return !TRUE;															//  mark hook as unhandled
	}
}
