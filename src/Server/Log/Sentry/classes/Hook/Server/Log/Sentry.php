<?php /** @noinspection PhpUnused */

use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Log_Sentry extends Hook
{
	public function onEnvInitModules(): bool
	{
		$appConfig		= $this->env->getConfig()->getAll( 'app.', TRUE );
		$moduleConfig	= $this->module->getConfigAsDictionary();
		if( $moduleConfig->get( 'active' ) ){
			Sentry\init( [
				'dsn'				=> $moduleConfig->get( 'dsn' ),
				'attach_stacktrace'	=> TRUE,
				'environment'		=> $appConfig->get( 'environment' ),
				'release'			=> $appConfig->get( 'release' ),
			] );
		}
		return FALSE;															//  mark hook as unhandled
	}

	public function onEnvLogException(): bool
	{
		$data			= $this->payload;
		$moduleConfig	= $this->module->getConfigAsDictionary();
		if( array_key_exists( 'exception', $this->payload ) )
			if( $this->payload['exception'] instanceof Exception )
				if( $moduleConfig->get( 'active' ) )
					Sentry\captureException( $this->payload['exception'] );
		return FALSE;															//  mark hook as unhandled
	}
}
