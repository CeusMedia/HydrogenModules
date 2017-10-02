<?php
class Controller_Manage_My_Mangopay_Card_Payin extends Controller_Manage_My_Mangopay_Abstract{

	protected $words;

	public function __onInit(){
		parent::__onInit();
		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/card' );
		$this->sessionPrefix	= 'manage_my_mangopay_card_';
	}

	protected function followBackLink( $sessionKey ){
		$from = $this->session->get( $this->sessionPrefix.$sessionKey );
		if( $from ){
			$this->session->remove( $this->sessionPrefix.$sessionKey );
			$this->restart( $from );
		}
	}

	protected function handleErrorCode( $errorCode, $goBack = TRUE ){
		$helper		= new View_Helper_Mangopay_Error( $this->env );
		try{
			$helper->setCode( $errorCode );
			$helper->setMode( View_Helper_Mangopay_Error::MODE_HTML );
			$message	= $helper->render();
		}
		catch( Exception $e ){
			$message	= sprintf( 'Unknown error code: %s', $errorCode );
		}
		$this->messenger->noteError( $message );

//		$errorCodes	= ADT_List_Dictionary::create( $this->words )->getAll( 'errorCode-' );
//		if( !array_key_exists( $errorCode, $errorCodes ) )
//			throw new InvalidArgumentException( 'Unknown error code: '.$errorCode );
//		$this->messenger->noteError( $errorCodes[(string) $errorCode] );

		if( $goBack )
			$this->followBackLink( 'payin_from' );
	}

	protected function handlePreAuthorized( $preAuthId, $cardId, $walletId ){
		$preAuth	= $this->mangopay->CardPreAuthorizations->Get( $preAuthId );
		if( !$preAuth ){
			$this->messenger->noteError( 'Invalid Payment Card payInPreAuthorization.' );
			$this->restart( NULL, TRUE );
		}
		$card	= $this->checkIsOwnCard( $cardId );
		$fees	= $this->moduleConfig->getAll( 'fees.payin.' );
		$payIn	= new \MangoPay\PayIn();
		$payIn->PreauthorizationId	= $preAuthId;
		$payIn->AuthorId			= $preAuth->AuthorId;
		$payIn->CreditedUserId		= $preAuth->AuthorId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= $preAuth->DebitedFunds;
		$payIn->Fees				= new \MangoPay\Money();
		$payIn->Fees->Amount		= $preAuth->DebitedFunds->Amount * $fees['percent'] + ( $fees['fix'] * 100 );
		$payIn->Fees->Currency		= $preAuth->DebitedFunds->Currency;

		// payment type as CARD
		$payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsPreAuthorized();
		$payIn->PaymentDetails->CardType	= $card->CardType;
		$payIn->PaymentDetails->CardId		= $card->Id;

		// execution type as DIRECT
		$payIn->ExecutionDetails	= new \MangoPay\PayInExecutionDetailsDirect();
		$payIn->ExecutionDetails->SecureModeReturnURL = $this->env->url.'manage/my/mangopay/card/payin/handleSecureMode';

		// create Pay-In
		$createdPayIn = $this->mangopay->PayIns->Create( $payIn );
		$this->handleStatus( $createdPayIn, $card, $wallet );
	}

	public function handlePreAuthorizedSecureMode( $cardId, $walletId ){
		$preAuthId	= $this->request->get( 'preAuthorizationId' );
		if( !$preAuthId ){
			$this->messenger->noteError( 'No PreAuthorization ID given.' );
			$this->restart( 'payInPreAuthorized', TRUE );
		}

		$preAuth	= $this->mangopay->CardPreAuthorizations->Get( $preAuthId );
		if( !$preAuth ){
			$this->messenger->noteError( 'Invalid PreAuth.' );
			$this->restart( 'payin', TRUE );
		}
		$this->handlePreAuthorized( $preAuthId, $cardId, $walletId );
	}

	public function handleSecureMode(){
		$payInId	= $this->session->get( 'payInId' );
		if( !$payInId ){
			$this->messenger->noteError( 'Payment has already been handled or expired.' );
			$this->restart( 'payin', TRUE );
		}

		$payIn	= $this->mangopay->PayIns->Get( $payInId );
		if( !$payIn ){
			$this->messenger->noteError( 'Invalid Payment.' );
			$this->restart( 'payin', TRUE );
		}

		$price		= View_Manage_My_Mangopay::formatMoney( $payIn->DebitedFunds );
		$walletId	= $payIn->CreditedWalletId;
		$wallet		= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );						//  @todo handle invalid walled
		$cardId		= $payIn->PaymentDetails->CardId;

		$this->cache->remove( 'user_'.$this->userId.'_transactions' );
		$this->session->remove( 'payInId' );

		if( $payIn->Status === \MangoPay\PayInStatus::Failed ){
			$this->messenger->noteError( 'Payment of %s into Wallet "%s" has been cancelled or failed <abbr title="%s">failed</abbr>.', $price, $wallet->Description, $payIn->ResultMessage );
			$this->restart( 'payin/'.$cardId, TRUE );
		}
		else if( $payIn->Status === \MangoPay\PayInStatus::Succeeded ){
			$this->cache->remove( 'user_'.$this->userId.'_wallets' );
			$this->messenger->noteSuccess( 'Payed <strong>%s</strong> into Wallet <strong>%s</strong>.', $price, $wallet->Description );
			$this->followBackLink( 'payin_from' );
			$this->restart( 'manage/my/mangopay/card/view/'.$cardId );
		}
		throw new OutOfBoundsException( 'PayIn status "'.$payIn->Status.'" is not handled.' );
	}

	protected function handleStatus( $payIn, $card, $wallet ){
		$price	= View_Manage_My_Mangopay::formatMoney( $payIn->DebitedFunds );
		if( $payIn->Status === \MangoPay\PayInStatus::Failed ){
			$this->handleErrorCode( $payIn->ResultCode );

			if( ( $from = $this->request->get( 'from' ) ) )
				$this->restart( $from );
			$this->restart( 'payin/'.$card->Id, TRUE );
		}
		else if( $payIn->Status === \MangoPay\PayInStatus::Created ){
			$this->session->set( 'payInId', $payIn->Id );
//				print_m( $createdPayIn );die;
			header( 'Location: '.$payIn->ExecutionDetails->SecureModeRedirectURL );
			exit;
		}
		$this->cache->remove( 'user_'.$this->userId.'_wallets' );
		$this->cache->remove( 'user_'.$this->userId.'_transactions' );
		$this->messenger->noteSuccess( 'Payed <strong>%s</strong> into Wallet <strong>%s</strong>.', $price, $wallet->Description );
		$this->followBackLink( 'payin_from' );
		$this->restart( '..', TRUE );
	}

	public function index( $cardId = NULL, $walletId = NULL ){
		$this->addData( 'walletLocked', (bool) $walletId );
		$card		= $this->checkIsOwnCard( $cardId );
		$fees		= $this->moduleConfig->getAll( 'fees.payin.' );
		$this->saveBackLink( 'from', 'payin_from' );
		if( $this->request->has( 'save' ) ){
			$walletId		= $walletId ? $walletId : $this->request->get( 'walletId' );
			$wallet			= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );						//  @todo handle invalid walled
			$createdPayIn	= $this->logic->createPayInFromCard(
				$this->userId,
				$walletId,
				$cardId,
				round( $this->request->get( 'amount' ) * 100 ),
				$this->env->url.'manage/my/mangopay/card/payin/handleSecureMode'
			);
			$this->handleStatus( $createdPayIn, $card, $wallet );
		}
		$card		= $this->mangopay->Cards->Get( $cardId );
		$wallets	= $this->logic->getUserWalletsByCurrency( $this->userId, $card->Currency, TRUE );

		$this->addData( 'wallets', $wallets );
		$this->addData( 'walletId', $walletId );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'card', $card );
		$this->addData( 'from', $this->request->get( 'from' ) );
//		throw new RuntimeException( 'Not implemented yet' );
	}

	public function preAuthorized( $cardId, $walletId = NULL ){
		$this->addData( 'walletLocked', (bool) $walletId );
		$card	= $this->checkIsOwnCard( $cardId, FALSE, array( '') );
		$this->saveBackLink( 'from', 'payin_from' );
		if( $this->request->has( 'save' ) ){

			$walletId	= $walletId ? $walletId : $this->request->get( 'walletId' );
			$wallet		= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );						//  @todo handle invalid walled
			$amount		= round ( $this->request->get( 'amount' ) * 100 );

			$preAuth	= new \MangoPay\CardPreAuthorization();
			$preAuth->AuthorId		= $this->userId;
			$preAuth->CardId		= $cardId;
			$preAuth->DebitedFunds	= new \MangoPay\Money();
			$preAuth->DebitedFunds->Amount		= $amount;
			$preAuth->DebitedFunds->Currency	= $this->currency;
			$preAuth->SecureModeReturnURL		= $this->env->url.'manage/my/mangopay/card/payin/handlePreAuthorizedSecureMode/'.$cardId.'/'.$walletId;
			$createdPreAuth		= $this->mangopay->CardPreAuthorizations->Create( $preAuth );

			if( $createdPreAuth->Status === \MangoPay\CardPreAuthorizationStatus::Failed ){
//				print_m( $createdPreAuth );die;
				$this->handleErrorCode( $createdPreAuth->ResultCode, FALSE );
				$this->followBackLink( 'payin_from' );
				$this->restart( 'view/'.$cardId, TRUE );
			}
			else if( $createdPreAuth->Status === \MangoPay\CardPreAuthorizationStatus::Created ){
				$this->session->set( 'preAuthId', $createdPreAuth->Id );
				header( 'Location: '.$createdPreAuth->SecureModeRedirectURL );
				exit;
			}
			$this->handlePreAuthorized( $createdPreAuth->Id, $cardId, $walletId );
		}
		$wallets	= $this->logic->getUserWalletsByCurrency( $this->userId, $card->Currency, TRUE );

		$this->addData( 'wallets', $wallets );
		$this->addData( 'walletId', $walletId );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'card', $this->mangopay->Cards->Get( $cardId ) );
		$this->addData( 'from', $this->request->get( 'from' ) );
//		throw new RuntimeException( 'Not implemented yet' );
	}

	protected function saveBackLink( $requestKey, $sessionKey ){
		$from = $this->request->get( $requestKey );
		if( $from )
			$this->session->set( $this->sessionPrefix.$sessionKey, $from );
	}
}
