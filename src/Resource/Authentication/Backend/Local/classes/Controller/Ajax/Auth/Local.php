<?php

use CeusMedia\Common\Alg\Crypt\PasswordStrength;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Auth_Local extends AjaxController
{
	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function usernameExists()
	{
		$username	= trim( $this->request->get( 'username' ) );
		$result		= FALSE;
		if( strlen( $username ) ){
			$modelUser		= new Model_User( $this->env );
			$result			= (bool) $modelUser->countByIndex( 'username', $username );
		}
		$this->respondData( $result );
	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function emailExists()
	{
		$email		= trim( $this->request->get( 'email' ) );
		$result		= FALSE;
		if( strlen( $email ) ){
			$modelUser		= new Model_User( $this->env );
			$result			= (bool) $modelUser->countByIndex( 'email', $email );
		}
		$this->respondData( $result );
	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function passwordStrength()
	{
		$password	= trim( $this->request->get( 'password' ) );
		$result		= 0;
		if( strlen( $password ) )
			$result	= PasswordStrength::getStrength( $password );
		$this->respondData( $result );
	}

	protected function __onInit(): void
	{
	}
}
