<?php
class Controller_Manage_My_Mangopay_Card extends Controller_Manage_My_Mangopay{

	public function add( $type = NULL ){

		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );

		if( ( $cardType = $this->request->get( 'cardType' ) ) ){
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

			$registration = $this->mangopay->CardRegistrations->Create( $cardRegister );

			$this->env->getSession()->set( 'cardRegisterId', $registration->Id );
			$this->addData( 'registration', $registration );
		}
		$this->addData( 'cardType', $cardType );

//		throw new RuntimeException( 'Not implemented yet' );
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

			$words		= $this->getWords( 'add', 'manage/my/payment/card' );
			$errorCodes	= ADT_List_Dictionary::create( $words )->getAll( 'errorCode-' );
			if( !array_key_exists( $errorCode, $errorCodes ) )
				throw new InvalidArgumentException( 'Unknown error code: '.$errorCode );
			$this->messenger->noteError( 'Error: '.$errorCodes[(string) $errorCode] );
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
			$this->restart( 'view/'.$registration->CardId, TRUE );

/*			$card = $this->mangopay->Cards->Get( $registration->CardId );
			$this->addData( 'card', $card );
			$this->addData( 'cardId', $registration->CardId );*/
		}
		$this->messenger->noteNotice( 'You have to add a credit card first' );
		$this->restart( 'add', TRUE );
	}

	public function deactivate( $cardId ){
		$card	= $this->checkIsOwnCard( $cardId );
		$card->Active	= FALSE;
		$this->mangopay->Cards->Update( $card );
		$this->restart( NULL, TRUE );
	}

	public function handleSecureMode(){
		print_m( $this->request->getAll() );
		die;
	}

	public function index(){
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		try{
			$cacheKey	= 'user_'.$this->userId.'_cards';
			if( is_null( $cards = $this->cache->get( $cacheKey ) ) ){
				$cards	= $this->mangopay->Users->GetCards( $this->userId, $pagination, $sorting );
				$this->cache->set( $cacheKey, $cards );
			}
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
		$card	= $this->checkIsOwnCard( $cardId );
		if( $this->request->has( 'save' ) ){

			$walletId	= $this->request->get( 'walletId' );
			$wallet		= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );						//  @todo handle invalid walled

			$payIn		= new \MangoPay\PayIn();
			$payIn->CreditedWalletId	= $walletId;
			$payIn->AuthorId			= $this->userId;											//  @todo inset user ID from session
			$payIn->DebitedFunds		= new \MangoPay\Money();
			$payIn->Fees				= new \MangoPay\Money();

			$amount	= $this->request->get( 'amount' );
		//	$amount	= $this->checkAmount( $amount, $this->currency );								//  @todo handle amount format and sanity

			$payIn->DebitedFunds->Amount	= $amount;
			$payIn->DebitedFunds->Currency	= $this->currency;

			$payIn->Fees->Amount	= $amount * $this->factorFees;
			$payIn->Fees->Currency	= $this->currency;

			$card	= $this->checkIsOwnCard( $cardId );

			// payment type as CARD
			$payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
			$payIn->PaymentDetails->CardType	= $card->CardType;
			$payIn->PaymentDetails->CardId		= $card->Id;

			// execution type as DIRECT
			$payIn->ExecutionDetails	= new \MangoPay\PayInExecutionDetailsDirect();
			$payIn->ExecutionDetails->SecureModeReturnURL = $this->env->url.'manage/my/mangopay/card/handleSecureMode';

			// create Pay-In
			$createdPayIn = $this->mangopay->PayIns->Create( $payIn );
/*			$resultMessage	= $createdPayIn->ResultCode;
			print_m( $createdPayIn );die;
*/
			// if created Pay-in object has status SUCCEEDED it's mean that all is fine
			$price	= View_Manage_My_Mangopay::formatMoney( (object) array( 'Amount' => $amount, 'Currency' => $this->currency ) );
			if( $createdPayIn->Status !== \MangoPay\PayInStatus::Succeeded ){
				$this->messenger->noteError( 'Paying in %s into Wallet "%s" failed: %s', $price, $wallet->Description, $createdPayIn->ResultMessage );
				if( ( $from = $this->request->get( 'from' ) ) )
					$this->restart( $from );
				$this->restart( 'payin/'.$cardId, TRUE );
			}
			$this->messenger->noteSuccess( 'Payed <strong>%s</strong> into Wallet <strong>%s</strong>.', $price, $wallet->Description );
			if( ( $from = $this->request->get( 'from' ) ) )
				$this->restart( $from );
			$this->restart( NULL, TRUE );
		}

		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$wallets	= $this->mangopay->Users->GetWallets( $this->userId, $pagination, $sorting );

		$this->addData( 'wallets', $wallets );
		$this->addData( 'walletId', $this->request->get( 'walletId' ) );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'card', $this->mangopay->Cards->Get( $cardId ) );
		$this->addData( 'from', $this->request->get( 'from' ) );
//		throw new RuntimeException( 'Not implemented yet' );
	}

	public function payOut(){
		throw new RuntimeException( 'Not implemented yet' );
	}

	public function view( $cardId ){
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );

		$card	= $this->checkIsOwnCard( $cardId );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'card', $card );
	}
}
