<?php

use CeusMedia\Common\Alg\Crypt\PasswordStrength;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Auth_Rest extends AjaxController
{
	protected Logic_Authentication_Backend_Rest $logic;

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function usernameExists(): void
	{
		$username	= trim( $this->request->get( 'username' ) );
		$result		= $this->logic->checkUsername( $username );
		$this->respondData( $result );
	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function emailExists(): void
	{
		$email		= trim( $this->request->get( 'email' ) );
		$result		= $this->logic->checkEmail( $email );
		$this->respondData( $result );
	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function passwordStrength(): void
	{
		$password	= trim( $this->request->get( 'password' ) );
		$result		= 0;
		if( '' !== $password )
			$result			= PasswordStrength::getStrength( $password );
		$this->respondData( $result );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic	= Logic_Authentication_Backend_Rest::getInstance( $this->env );
	}
}