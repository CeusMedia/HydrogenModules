<?php
class Controller_Provision extends CMF_Hydrogen_Controller{

	protected $config;
	protected $session;
	protected $moduleConfig;
	protected $resource;

	protected function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->session		= $this->env->getSession();
		$this->moduleConfig	= $this->config->getAll( 'module.resource_provision.', TRUE );
		$this->resource		= new Resource_Provision_Client( $this->env );
	}

	public function index(){
		$this->addData( 'serverUrl', $this->moduleConfig->get( 'server.url' ) );
		$this->addData( 'userId', $this->session->get( 'userId' ) );
	}

	public function status( $userId = NULL ){
//		$userId	= $this->session->get( 'userId' ) ? $this->session->get( 'userId' ) : $userId;
		$userId	= $this->session->get( 'userId' );
		if( !$userId )
			$this->restart( NULL, TRUE );
		try{
			$data	= $this->resource->getUserLicenseKey( $userId );
			$this->addData( 'status', 'data' );
			$this->addData( 'data', $data );
			$this->addData( 'registerLicense', $this->session->get( 'register_license' ) );
		}
		catch( Exception $e ){
			$msg		= 'Der Provision-Server ist zur Zeit nicht erreichbar ('.$e->getMessage().'). Bitte später noch einmal probieren!';
			$this->env->getMessenger()->noteError( $msg );
			$this->redirect( 'provision' );
			return;
		}
		$this->addData( 'serverUrl', $this->moduleConfig->get( 'server.url' ) );
		$this->addData( 'productId', $this->moduleConfig->get( 'productId' ) );
	}
}
