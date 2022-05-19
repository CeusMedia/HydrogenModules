<?php
class Controller_Admin_Payment_Mangopay_Client extends CMF_Hydrogen_Controller
{
	public function edit()
	{
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			try{
				$result	= $this->mangopay->updateClient( $data );
				$this->messenger->noteSuccess( 'Saved.' );
			}
			catch( Exception $e ){
				$this->handleMangopayException( $e );
			}
			$this->restart( NULL, TRUE );
		}
	}

	public function index()
	{
		$this->addData( 'client', $this->mangopay->getClient() );
		$this->addData( 'clientWallets', $this->mangopay->getClientWallets() );
	}

	public function logo( $remove = NULL )
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

	protected function __onInit()
	{
		$this->request		= $this->env->getRequest();
//		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}
}
