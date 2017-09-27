<?php
class Controller_Manage_My_Mangopay_Card extends Controller_Manage_My_Mangopay{

	protected $words;

	public function __onInit(){
		parent::__onInit();
		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/card' );
		$this->sessionPrefix	= 'manage_my_mangopay_card_';
	}

	public function add(){

		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
		$cardType	= $this->request->get( 'cardType' );
		$cardTitle	= $this->request->get( 'title' );
		if( $cardType && strlen( trim( $cardTitle ) ) ){
			$param	= array();
			if( $this->request->get( 'backwardTo' ) )
				$param[]	= 'backwardTo='.$this->request->get( 'backwardTo' );
			if( $this->request->get( 'forwardTo' ) )
				$param[]	= 'forwardTo='.$this->request->get( 'forwardTo' );
			$param			= $param ? '?'.http_build_query( $param, NULL, '&' ) : '';

			$returnUrl	= $this->env->url.'manage/my/mangopay/card/finishCardRegistration';
			$this->addData( 'returnUrl', $returnUrl.$param );

			$cardRegister = new \MangoPay\CardRegistration();
			$cardRegister->UserId	= $this->userId;
			$cardRegister->Currency	= $this->currency;
			$cardRegister->CardType	= $cardType;
			$cardRegister->Tag		= $cardTitle;

			$registration = $this->mangopay->CardRegistrations->Create( $cardRegister );

			$this->env->getSession()->set( 'cardRegisterId', $registration->Id );
			$this->addData( 'registration', $registration );
		}
		$this->addData( 'cardType', $cardType );
		$this->addData( 'cardTitle', $cardTitle );

//		throw new RuntimeException( 'Not implemented yet' );
	}

	public function deactivate( $cardId ){
		$card	= $this->checkIsOwnCard( $cardId );
		$card->Active	= FALSE;
		$this->mangopay->Cards->Update( $card );
		$this->cache->remove( 'user_'.$this->userId.'_cards' );
		$this->restart( NULL, TRUE );
	}

	public function finishCardRegistration(){
		$registrationId	= $this->env->getSession()->get( 'cardRegisterId' );
		$registration	= $this->mangopay->CardRegistrations->Get( $registrationId );

		if( $this->request->has( 'errorCode' ) ){
			$errorCode		= $this->request->get( 'errorCode' );
			$registration->RegistrationData	= 'errorCode='.$errorCode;
			try{
				$registration	= $this->mangopay->CardRegistrations->Update( $registration );
			}
			catch( MangoPay\Libraries\ResponseException $e ){										//  @todo handle this specific exception
				$this->messenger->noteError( 'Error: '.$e->getMessage() );
				$this->restart( 'add', TRUE );
			}
			catch( Exception $e ){
				$this->messenger->noteError( 'Error: '.$e->getMessage() );
				$this->restart( 'add', TRUE );
			}
			$this->handleErrorCode( $errorCode );
			$this->restart( 'add', TRUE );
		}

		if( $this->request->has( 'data' ) ){
			$registration->RegistrationData	= 'data='.$this->request->get( 'data' );
			$registration	= $this->mangopay->CardRegistrations->Update( $registration );

			$isValid	= $registration->Status == \MangoPay\CardRegistrationStatus::Validated;
			$hasCardId	= isset( $registration->CardId );
			if( !$isValid || !$hasCardId ){
				$this->env->getMessenger()->noteError( 'Cannot create card.' );
				$this->restart( 'add', TRUE );
			}

			$this->env->getMessenger()->noteSuccess( 'Credit Card has been created.' );
			$cacheKey	= 'user_'.$this->userId.'_cards';
			$this->cache->remove( $cacheKey );
			$this->restart( 'view/'.$registration->CardId, TRUE );

/*			$card = $this->mangopay->Cards->Get( $registration->CardId );
			$this->addData( 'card', $card );
			$this->addData( 'cardId', $registration->CardId );*/
		}
		$this->messenger->noteNotice( 'You have to add a credit card first' );
		$this->restart( 'add', TRUE );
	}

	protected function handleErrorCode( $errorCode, $goBack = TRUE ){
		$errorCodes	= ADT_List_Dictionary::create( $this->words )->getAll( 'errorCode-' );
		if( !array_key_exists( $errorCode, $errorCodes ) )
			throw new InvalidArgumentException( 'Unknown error code: '.$errorCode );
		$this->messenger->noteError( $errorCodes[(string) $errorCode] );

		if( $goBack )
			$this->followBackLink( 'payin_from' );
	}

	protected function handlePayInPreAuthorized( $preAuthId, $cardId, $walletId ){
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
		$payIn->ExecutionDetails->SecureModeReturnURL = $this->env->url.'manage/my/mangopay/card/handlePayInSecureMode';

		// create Pay-In
		$createdPayIn = $this->mangopay->PayIns->Create( $payIn );
		$this->handlePayInStatus( $createdPayIn, $card, $wallet );
	}

	public function handlePayInPreAuthorizedSecureMode( $cardId, $walletId ){
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
		$this->handlePayInPreAuthorized( $preAuthId, $cardId, $walletId );
	}

	public function handlePayInSecureMode(){
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

	protected function handlePayInStatus( $payIn, $card, $wallet ){
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
		$this->restart( NULL, TRUE );
	}

	public function index( $refresh = NULL ){
		try{
			$this->logic->skipCacheOnNextRequest( $refresh );
			$cards	= $this->logic->getUsersCards( $this->userId );
			$this->addData( 'cards', $cards );
		}
		catch( \MangoPay\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
			$this->restart( 'manage/my/mangopay' );
		}
		catch( Exception $e ){
			$this->messenger->noteError( "Exception: ".$e->getMessage() );
			$this->restart( 'manage/my/mangopay' );
		}
	}

	public function payIn( $cardId ){
		$card		= $this->checkIsOwnCard( $cardId );
		$fees		= $this->moduleConfig->getAll( 'fees.payin.' );
		$this->saveBackLink( 'from', 'payin_from' );
		if( $this->request->has( 'save' ) ){
			$walletId	= $this->request->get( 'walletId' );
			$wallet		= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );						//  @todo handle invalid walled

			$createdPayIn = $this->logic->createPayInFromCard(
				$this->userId,
				$walletId,
				$cardId,
				$this->request->get( 'amount' ),
				$this->env->url.'manage/my/mangopay/card/handlePayInSecureMode'
			);
			$this->handlePayInStatus( $createdPayIn, $card, $wallet );
		}
		$card		= $this->mangopay->Cards->Get( $cardId );
		$wallets	= $this->logic->getUserWalletsByCurrency( $this->userId, $card->Currency, TRUE );

		$this->addData( 'wallets', $wallets );
		$this->addData( 'walletId', $this->request->get( 'walletId' ) );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'card', $card );
		$this->addData( 'from', $this->request->get( 'from' ) );
//		throw new RuntimeException( 'Not implemented yet' );
	}

	protected function saveBackLink( $requestKey, $sessionKey ){
		$from = $this->request->get( $requestKey );
		if( $from )
			$this->session->set( $this->sessionPrefix.$sessionKey, $from );
	}

	protected function followBackLink( $sessionKey ){
		$from = $this->session->get( $this->sessionPrefix.$sessionKey );
		if( $from ){
			$this->session->remove( $this->sessionPrefix.$sessionKey );
			$this->restart( $from );
		}
	}

	public function payInPreAuthorized( $cardId ){
		$card	= $this->checkIsOwnCard( $cardId );
		$this->saveBackLink( 'from', 'payin_from' );
		if( $this->request->has( 'save' ) ){
			$walletId	= $this->request->get( 'walletId' );
			$wallet		= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );						//  @todo handle invalid walled
			$amount		= $this->request->get( 'amount' );

			$preAuth	= new \MangoPay\CardPreAuthorization();
			$preAuth->AuthorId		= $this->userId;
			$preAuth->CardId		= $cardId;
			$preAuth->DebitedFunds	= new \MangoPay\Money();
			$preAuth->DebitedFunds->Amount		= $amount;
			$preAuth->DebitedFunds->Currency	= $this->currency;
			$preAuth->SecureModeReturnURL		= $this->env->url.'manage/my/mangopay/card/handlePayInPreAuthorizedSecureMode/'.$cardId.'/'.$walletId;

			$createdPreAuth		= $this->mangopay->CardPreAuthorizations->Create( $preAuth );

			$price	= View_Manage_My_Mangopay::formatMoney( (object) array( 'Amount' => $amount, 'Currency' => $this->currency ) );
			if( $createdPreAuth->Status === \MangoPay\CardPreAuthorizationStatus::Failed ){
//				print_m( $createdPreAuth );die;
				$this->handleErrorCode( $createdPreAuth->ResultCode );
				$this->followBackLink( 'payin_from' );
				$this->restart( 'view/'.$cardId, TRUE );
			}
			else if( $createdPreAuth->Status === \MangoPay\CardPreAuthorizationStatus::Created ){
				$this->session->set( 'preAuthId', $createdPreAuth->Id );
				header( 'Location: '.$createdPreAuth->SecureModeRedirectURL );
				exit;
			}
			$this->handlePayInPreAuthorized( $createdPreAuth->Id, $cardId, $walletId );
		}
		$wallets	= $this->logic->getUserWalletsByCurrency( $this->userId, $card->Currency, TRUE );

		$this->addData( 'wallets', $wallets );
		$this->addData( 'walletId', $this->request->get( 'walletId' ) );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'card', $this->mangopay->Cards->Get( $cardId ) );
		$this->addData( 'from', $this->request->get( 'from' ) );
//		throw new RuntimeException( 'Not implemented yet' );
	}

	public function payOut(){
		throw new RuntimeException( 'Not implemented yet' );

		$wallets	= $this->logic->getUserWalletsByCurrency( $this->userId, $card->Currency, TRUE );

		$this->cache->remove( 'user_'.$this->userId.'_wallets' );
		$this->cache->remove( 'user_'.$this->userId.'_transactions' );
	}

	public function edit( $cardId ){
		$card	= $this->checkIsOwnCard( $cardId );
		if( $this->request->has( 'save' ) ){
			$tag	= $this->request->get( 'title' );
			if( $tag )
				$card->Tag	= $tag;
			try{
				$this->mangopay->Cards->Update( $card );
				$this->restart( 'view/'.$cardId, TRUE );
			}
			catch( MangoPay\Libraries\ResponseException $e ){										//  @todo handle this specific exception
				$this->messenger->noteError( 'Error #%s (%s): %s', $e->getCode(), get_class( $e ), $e->getMessage() );
				$this->restart( 'edit/'.$cardId, TRUE );
			}
			catch( Exception $e ){
				$this->messenger->noteError( 'Error #%s (%s): %s', $e->getCode(), get_class( $e ), $e->getMessage() );
				$this->restart( 'edit/'.$cardId, TRUE );
			}
		}
		$this->addData( 'card', $card );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
	}

	public function view( $cardId ){

		$card	= $this->checkIsOwnCard( $cardId );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'card', $card );
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
	}
}
