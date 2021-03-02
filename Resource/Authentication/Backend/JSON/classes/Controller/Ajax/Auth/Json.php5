<?php
class Controller_Ajax_Auth_Json extends CMF_Hydrogen_Controller_Ajax
{
	public function usernameExists()
	{
		$username	= trim( $this->request->get( 'username' ) );
		$result		= FALSE;
		if( strlen( $username ) ){
			$data		= array ( 'filters' => array( 'username' => $username ) );
			$result		= $this->env->getServer()->postData( 'user', 'index', NULL, $data );
			$result		= count( $result ) === 1;
		}
		$this->respondData( $result );
	}

	public function emailExists()
	{
		$email	= trim( $this->request->get( 'email' ) );
		$result		= FALSE;
		if( strlen( $email ) ){
			$data		= array ( 'filters' => array( 'email' => $email ) );
			$result		= $this->env->getServer()->postData( 'user', 'index', NULL, $data );
			$result		= count( $result ) === 1;
		}
		$this->respondData( $result );
	}

	public function passwordStrength()
	{
		$password	= trim( $this->request->get( 'password' ) );
		$result		= 0;
		if( strlen( $password ) )
			$result			= Alg_Crypt_PasswordStrength::getStrength( $password );
		$this->respondData( $result );
	}
}