<?php
abstract class Controller_Manage_My_Mangopay extends CMF_Hydrogen_Controller{

	protected $request;
	protected $mangopay;
	protected $messenger;
	protected $session;

	protected $userId		= 15013325;
	protected $currency		= "EUR";
	protected $factorFees	= 0.1;

	protected function __onInit(){
		parent::__onInit();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Resource_Mangopay::getInstance( $this->env );
	}

	protected function checkIsOwnCard( $cardId ){
		$card	= $this->checkCard( $cardId );
	//	@todo check card against user cards
		return $card;
	}

	protected function checkCard( $cardId ){
		try{
			$card	= $this->mangopay->Cards->Get( $cardId );
			return $card;
		}
		catch( \MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
//			$this->messenger->noteNotice( "Exception: ".$e->getMessage( ) );
			$this->messenger->noteError( "Invalid card ID given." );
			$this->restart( NULL, TRUE );
		}
	}

	protected function checkWallet( $walletId ){
		try{
			$wallet	= $this->mangopay->Wallets->Get( $walletId );
			return $wallet;
		}
		catch( Exception $e ){
//			$this->messenger->noteNotice( "Exception: ".$e->getMessage( ) );
			$this->messenger->noteError( "Invalid wallet ID given." );
			$this->restart( NULL, TRUE );
		}
	}

	protected function checkWalletIsOwn( $walletId ){
		$wallet		= $this->checkWallet( $walletId );
		//	@todo check against list of user wallets
		return $wallet;
	}

	protected function handleMangopayResponseException( $e ){
		ob_start();
		print_r( $e->GetErrorDetails() );
		$details	= ob_get_clean();
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}
}
?>
