<?php

use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_Catalog extends Controller
{
	/**	@var		Logic_Catalog		$logic */
	protected Logic_Catalog $logic;
	protected Messenger $messenger;
	protected Request $request;
	protected PartitionSession $session;

	public function index(): void
	{
		$this->restart( 'article', TRUE );
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
	}
}
