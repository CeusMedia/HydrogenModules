<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Catalog extends Controller
{
	/**	@var		Logic_Catalog		$logic */
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;

	public function index()
	{
		$this->restart( 'article', TRUE );
	}

	protected function __onInit()
	{
		$this->logic		= new Logic_Catalog( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
	}
}
