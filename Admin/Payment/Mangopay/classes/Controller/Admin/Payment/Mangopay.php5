<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Payment_Mangopay extends Controller{

	protected $messenger;

	protected function __onInit(){
		$this->messenger	= $this->env->getMessenger();
	}

	public function index(){
		$this->restart( 'client', TRUE );
	}


	protected function handleMangopayResponseException( $e ){
		ob_start();
		print_r( $e->GetErrorDetails()->Errors );
		$details	= ob_get_clean();
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}
}
