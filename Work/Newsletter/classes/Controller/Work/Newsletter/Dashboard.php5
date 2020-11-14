<?php
class Controller_Work_Newsletter_Dashboard extends CMF_Hydrogen_Controller{

	/**	@var	Logic_Newsletter_Editor		$logic 		Instance of newsletter editor logic */
	protected $logic;
	protected $session;
	protected $request;
	protected $messenger;
	protected $moduleConfig;
	protected $limiter;

	public function __onInit(){
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

	public function index(){
		$this->addData( 'readers', array(
			-1	=> count( $this->logic->getReaders( array( 'status' => -1 ) ) ),
			0	=> count( $this->logic->getReaders( array( 'status' => 0 ) ) ),
			1	=> count( $this->logic->getReaders( array( 'status' => 1 ) ) ),
		) );
		$this->addData( 'groups', array(
			-1	=> count( $this->logic->getGroups( array( 'status' => -1 ) ) ),
			0	=> count( $this->logic->getGroups( array( 'status' => 0 ) ) ),
			1	=> count( $this->logic->getGroups( array( 'status' => 1 ) ) ),
		) );
	}
}
?>
