<?php

use CeusMedia\Common\Alg\Crypt\PasswordStrength;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Auth_Json extends AjaxController
{
	protected Resource_JSON_Client $client;

	public function usernameExists(): void
	{
		$username	= trim( $this->request->get( 'username' ) );
		$result		= FALSE;
		if( strlen( $username ) ){
			$data		= array ( 'filters' => ['username' => $username] );
			$result		= $this->client->postData( 'user', 'index', NULL, $data );
			$result		= count( $result ) === 1;
		}
		$this->respondData( $result );
	}

	public function emailExists(): void
	{
		$email	= trim( $this->request->get( 'email' ) );
		$result		= FALSE;
		if( strlen( $email ) ){
			$data		= array ( 'filters' => ['email' => $email] );
			$result		= $this->client->postData( 'user', 'index', NULL, $data );
			$result		= count( $result ) === 1;
		}
		$this->respondData( $result );
	}

	public function passwordStrength(): void
	{
		$password	= trim( $this->request->get( 'password' ) );
		$result		= 0;
		if( strlen( $password ) )
			$result			= PasswordStrength::getStrength( $password );
		$this->respondData( $result );
	}

	protected function __onInit(): void
	{
		$clientEnvKey	= $this->env->getConfig()->get( 'module.resource_json_client.envKey' );
		$this->client	= $this->env->get( $clientEnvKey );
	}
}
