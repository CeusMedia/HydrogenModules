<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Auth extends AjaxController
{
//	protected $config;
//	protected $request;
	protected $session;
//	protected $logic;
//	protected $moduleConfig;

	public function isAuthenticated()
	{
		$this->respondData( array( 'result' => $this->session->has( 'auth_user_id' ) ) );
	}

	/**
	 *	@deprecated		use isAuthenticated instead
	 *	@todo			to be removed
	 */
	public function refreshSession()
	{
		$this->ajaxIsAuthenticated();
	}

	protected function __onInit()
	{
//		$this->config		= $this->env->getConfig();
//		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
//		$this->logic		= $this->env->getLogic()->get( 'Authentication' );
//		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_authentication.', TRUE );
	}
}
