<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Work_Newsletter_Dashboard extends Controller
{
	/**	@var	Logic_Newsletter_Editor		$logic 		Instance of newsletter editor logic */
	protected Logic_Newsletter_Editor $logic;
	protected PartitionSession $session;
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $moduleConfig;
	protected ?Logic_Limiter $limiter		= NULL;

	public function index(): void
	{
		$this->addData( 'readers', [
			-1	=> count( $this->logic->getReaders( ['status' => -1] ) ),
			0	=> count( $this->logic->getReaders( ['status' => 0] ) ),
			1	=> count( $this->logic->getReaders( ['status' => 1] ) ),
		] );
		$this->addData( 'groups', [
			-1	=> count( $this->logic->getGroups( ['status' => -1] ) ),
			0	=> count( $this->logic->getGroups( ['status' => 0] ) ),
			1	=> count( $this->logic->getGroups( ['status' => 1] ) ),
		] );
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Newsletter_Editor( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'tabbedLinks', $this->moduleConfig->get( 'tabbedLinks' ) );
		if( $this->env->getModules()->has( 'Resource_Limiter' ) )
			$this->limiter	= Logic_Limiter::getInstance( $this->env );
		$this->addData( 'limiter', $this->limiter );
	}
}
