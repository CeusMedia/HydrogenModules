<?php
class Controller_Admin_Oauth2 extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.admin_oauth2.', TRUE );
		$this->modelProvider	= new Model_Oauth_Provider( $this->env );
	}

	public function add(){
		if( $this->request->isPost() && $this->request->has( 'save' ) ){
			$providerId	= $this->modelProvider->add( $this->request->getAll(), FALSE );
			$this->messenger->noteSuccess( 'Saved.' );
			$this->restart( 'edit/'.$providerId, TRUE );
		}

		$provider	= array();
		foreach( $this->modelProvider->getColumns() as $column )
			if( !in_array( $column, array( 'oauthProviderId', 'createdAt', 'modifiedAt' ) ) )
				$provider[$column]	= $this->request->get( $column );
		$this->addData( 'provider', (object) $provider );
	}

	public function edit( $providerId ){
		$provider	= $this->modelProvider->get( $providerId );
		if( !$provider ){
			$this->messenger->noteError( 'Invalid provider ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'exists', class_exists( $provider->className ) );
		$this->addData( 'providerId', $providerId );
		if( $this->request->isPost() && $this->request->has( 'save' ) ){
			$this->modelProvider->edit( $providerId, $this->request->getAll(), FALSE );
		}
		$this->addData( 'providerId', $providerId );
		$this->addData( 'provider', $provider );
	}

	public function filter( $reset = NULL ){
		if( $reset ){

		}
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$conditions	= array();
		$orders		= array( 'rank' => 'ASC' );
		$providers	= $this->modelProvider->getAll( $conditions, $orders );
		$this->addData( 'providers', $providers );
	}

	public function setStatus( $providerId, $status ){
		$provider	= $this->modelProvider->get( $providerId );
		if( !$provider ){
			$this->messenger->noteError( 'Invalid provider ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->modelProvider->edit( $providerId, array( 'status' => $status ) );
		$this->restart( 'edit/'.$providerId, TRUE );
	}
}
