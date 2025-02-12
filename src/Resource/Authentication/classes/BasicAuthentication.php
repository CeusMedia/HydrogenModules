<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo		integrate authentication backends
 */
class BasicAuthentication
{
	protected Environment $env;
	protected string $realm;

	public function __construct( Environment $env, string $realm )
	{
		$this->env		= $env;
		$this->realm	= $realm;
	}

	public function authenticate()
	{
		$server		= new Dictionary( $_SERVER );
		$username	= trim( $server->get( 'PHP_AUTH_USER' ) );
		$password	= trim( $server->get( 'PHP_AUTH_PW' ) );

/*		NEW CODE
		if( strlen( $username ) ){
			$logic		= Logic_Authentication::getInstance( $this->env );
			$logic->checkPassword( $username, $password );
			return TRUE;													//  HERE IS THE PROBLEM - how to return userId using OAUTH?
		}*/

		if( strlen( $username ) ){
			$model		= new Model_User( $this->env );
			/** @var Entity_User $user */
			$user		= $model->getByIndex( 'username', $username );
			if( $user ){
				if( class_exists( 'Logic_UserPassword' ) ){
					$logic	= Logic_UserPassword::getInstance( $this->env );
					if( $logic->validateUserPassword( $user, $password ) )
						return $user->userId;
				}
				if( $user->password == md5( $password ) )
					return $user->userId;
			}
		}
		header( 'WWW-Authenticate: Basic realm="'.addslashes( $this->realm ).'"' );
		header( 'HTTP/1.0 401 Unauthorized' );
		print( '<h1>Authentication failed.</h1>' );
		exit;
	}
}
