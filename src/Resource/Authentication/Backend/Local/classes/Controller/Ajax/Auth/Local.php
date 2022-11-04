<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Auth_Local extends AjaxController
{
//	protected $config;
	protected $request;
	protected $session;
//	protected $modules;
//	protected $moduleConfig;
//	protected $limiter;
//	protected $logic;

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

	public function passwordStrength()
	{
		$password	= trim( $this->request->get( 'password' ) );
		$result		= 0;
		if( strlen( $password ) ){
			$result	= Alg_Crypt_PasswordStrength::getStrength( $password );
		}
		$this->respondData( $result );
	}

	protected function __onInit()
	{
//		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
//		$this->modules		= $this->env->getModules();
//		$this->logic		= $this->env->getLogic()->get( 'Authentication_Backend_Local' );

//		$this->moduleConfig			= $this->config->getAll( 'module.resource_authentication_backend_local.', TRUE );
//		if( $this->modules->has( 'Resource_Limiter' ) )
//			if( $this->modules->get( 'Resource_Limiter' )->isActive )				// @todo apply this line here and anywhere else
//				$this->limiter	= Logic_Limiter::getInstance( $this->env );
	}
}
