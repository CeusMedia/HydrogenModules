<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Catalog_Bookstore extends Controller
{
	/**	@var		Logic_Catalog_Bookstore		$logic */
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;

	public function index(){
		$this->restart( 'article', TRUE );
	}

	protected function __onInit()
	{
		$this->logic		= new Logic_Catalog_Bookstore( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
	}
}
