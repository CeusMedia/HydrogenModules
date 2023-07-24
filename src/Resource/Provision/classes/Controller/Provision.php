<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Provision extends Controller{

	protected $config;
	protected $session;
	protected $resource;

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->session		= $this->env->getSession();
		$this->moduleConfig	= $this->config->getAll( 'module.resource_provision.', TRUE );
		$this->resource		= new Resource_Provision_Client( $this->env );
	}

	public function index(){
		$this->addData( 'serverUrl', $this->moduleConfig->get( 'server.url' ) );
		$this->addData( 'userId', $this->session->get( 'auth_user_id' ) );
	}

	public function status( $userId = NULL ){
//		$userId	= $this->session->get( 'auth_user_id' ) ? $this->session->get( 'auth_user_id' ) : $userId;
		$userId	= $this->session->get( 'auth_user_id' );
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
