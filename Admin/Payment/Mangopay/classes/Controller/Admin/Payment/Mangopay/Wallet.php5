<?php
class Controller_Admin_Payment_Mangopay_Wallet extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request		= $this->env->getRequest();
//		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->model		= new Model_Mangopay_Event( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}

	public function index(){
		$this->addData( 'clientWallets', $this->mangopay->getClientWallets() );
		$clientUserId = $this->moduleConfig->get( 'seller.userId' );
		$this->addData( 'projectUserId', $clientUserId );
		if( $clientUserId ){
			$user			= $this->mangopay->getUser( $clientUserId );
			$clientWalletId = $this->moduleConfig->get( 'seller.walletId' );
			$this->addData( 'projectUser', $user );
			$this->addData( 'projectWalletId', $clientWalletId );
			if( $clientWalletId ){
				$wallet	= $this->mangopay->getUserWallets( $clientUserId );
				$this->addData( 'projectWallets', $wallet );
			}
		}
	}

	public function createUser(){
		$data	= array();
		$this->mangopay->createLegalUser();
		$this->restart( NULL, TRUE );
	}
}
