<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_JSON_Client extends Hook
{
	/**
	 *	@todo			make environment resource key configurable
	 *	@todo			allow multiple instances
	 *	@todo			localization of messages
	 *	@todo			allow other auth methods than 'shared secret'
	 */
	public function onEnvInit(): void
	{
		if( !$this->context instanceof \CeusMedia\HydrogenFramework\Environment )
			throw new RuntimeException( 'Expected hook context to be an "Environment"' );

		/** @var Environment $env */
		$env		= $this->context;
		$client		= new Resource_JSON_Client( $env );
		$this->context->set( 'jsonServerClient', $client );
		$config		= $env->getConfig();
		$session	= $env->getSession();
		try{
			$token		= $session->get( 'token' );
			if( $token && !$client->postData( 'auth', 'validateToken' ) )
				$session->set( 'token', $token = NULL );
			if( !$token ) {																				//  client has no token yet
				$session->set( 'ip', getEnv( 'REMOTE_ADDR' ) );											//  store ip address in session
				$data	= array(
					'credentials'	=> array(															//  prepare POST data
						'secret'	=> $config->get( 'module.resource_json_client.auth.secret' ),		//  with known secret
					)
				);
				$token	= $client->postData( 'auth', 'getToken', NULL, $data );							//  request token from server using POST request
				$session->set( 'token', $token );														//  store token in session
			}
		}
		catch( Exception $e ){
			$message	= "Der Server ist momentan nicht erreichbar.";
			$env->getMessenger()->noteFailure( $message );
			return;
		}
	}
}