<?php
class Controller_Ajax_Auth extends CMF_Hydrogen_Controller_Ajax
{
//	protected $config;
//	protected $request;
	protected $session;
//	protected $logic;
//	protected $moduleConfig;

	public function __onInit()
	{
//		$this->config		= $this->env->getConfig();
//		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
//		$this->logic		= $this->env->getLogic()->get( 'Authentication' );
//		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_authentication.', TRUE );
	}

	public function isAuthenticated()
	{
		$this->respondData( array( 'result' => $this->session->has( 'userId' ) ) );
	}

	/**
	 *	@deprecated		use isAuthenticated instead
	 *	@todo			to be removed
	 */
	public function refreshSession()
	{
		$this->ajaxIsAuthenticated();
	}
}
