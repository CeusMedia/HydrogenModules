<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Payment_Mangopay_Client extends Controller
{
	protected Dictionary $request;
	protected Logic_Payment_Mangopay $mangopay;
	protected MessengerResource $messenger;
	protected Dictionary $moduleConfig;

	public function edit()
	{
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$this->mangopay->updateClient( $data );
			$this->messenger->noteSuccess( 'Saved.' );
			$this->restart( NULL, TRUE );
		}
	}

	public function index(): void
	{
		$this->addData( 'client', $this->mangopay->getClient() );
		$this->addData( 'clientWallets', $this->mangopay->getClientWallets() );
	}

	public function logo( $remove = NULL ): void
	{
		if( $this->request->has( 'save' ) ){
			$logicUpload	= new Logic_Upload( $this->env );
			$logicUpload->setUpload( $this->request->get( 'logo' ) );
			$logicUpload->checkSize( $logicUpload->getMaxUploadSize() );
			$logicUpload->checkIsImage();
			$logicUpload->checkVirus();
			if( $logicUpload->getError() ){
				$helperError	= new View_Helper_UploadError( $this->env );
				$helperError->setUpload( $logicUpload );
				$this->messenger->noteError( $helperError->render() );
				$this->restart( NULL, TRUE );
			}
			$this->mangopay->setClientLogo( base64_encode( $logicUpload->getContent() ) );
		}
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
//		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}
}
