<?php
class Controller_Manage_My_Mangopay_Card_Registration extends Controller_Manage_My_Mangopay{

	protected $words;

	public function __onInit(){
		parent::__onInit();
		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/card' );
		$this->sessionPrefix	= 'manage_my_mangopay_card_';
	}

	public function ajaxValidateCardNumber(){
		$number		= $this->request->get( 'cardNumber' );
		$provider	= $this->request->get( 'cardProvider' );
		$result		= $this->logic->validateCardNumber( $number, $provider );
		print( json_encode( array(
			'status'	=> 'data',
			'data'		=> $result
		) ) );
		exit;
	}

	public function index(){
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
		$cards	= $this->logic->getUsersCards( $this->userId );
		$this->addData( 'cards', $cards );
		$cardType	= $this->request->get( 'cardType' );
		if( $cardType ){
			$param	= array();
			if( $this->request->get( 'backwardTo' ) )
				$param[]	= 'backwardTo='.$this->request->get( 'backwardTo' );
			if( $this->request->get( 'forwardTo' ) )
				$param[]	= 'forwardTo='.$this->request->get( 'forwardTo' );
			$param			= $param ? '?'.http_build_query( $param, NULL, '&' ) : '';

			$returnUrl	= $this->env->url.'manage/my/mangopay/card/registration/finish';
			$this->addData( 'returnUrl', $returnUrl.$param );

			$cardRegister = new \MangoPay\CardRegistration();
			$cardRegister->UserId	= $this->userId;
			$cardRegister->Currency	= $this->currency;
			$cardRegister->CardType	= $cardType;

			try{
				$registration = $this->mangopay->CardRegistrations->Create( $cardRegister );
			}
			catch( Exception $e ){
				$this->handleMangopayResponseException( $e );
			}

			$this->env->getSession()->set( 'cardRegisterId', $registration->Id );
			$this->addData( 'registration', $registration );
		}
		$this->addData( 'cardType', $cardType );
//		$this->addData( 'cardTitle', $cardTitle );
		$this->addData( 'cardProvider', $this->request->get( 'cardProvider' ) );
	}

	public function finish(){
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
				$this->restart( NULL, TRUE );
			}
			catch( Exception $e ){
				$this->messenger->noteError( 'Error: '.$e->getMessage() );
				$this->restart( NULL, TRUE );
			}
			$this->handleErrorCode( $errorCode );
			$this->restart( NULL, TRUE );
		}

		if( $this->request->has( 'data' ) ){
			$registration->RegistrationData	= 'data='.$this->request->get( 'data' );
			$registration	= $this->mangopay->CardRegistrations->Update( $registration );

			$isValid	= $registration->Status == \MangoPay\CardRegistrationStatus::Validated;
			$hasCardId	= isset( $registration->CardId );
			if( !$isValid || !$hasCardId ){
				$this->env->getMessenger()->noteError( 'Cannot create card.' );
				$this->restart( NULL, TRUE );
			}
/*			$card	= $this->checkIsOwnCard( $registration->CardId );
			$card->Tag	= $registration->Tag;
			$this->mangopay->Cards->Update( $card );*/
			$this->env->getMessenger()->noteSuccess( 'Credit Card has been created.' );
			$cacheKey	= 'user_'.$this->userId.'_cards';
			$this->cache->remove( $cacheKey );
			$this->restart( '../view/'.$registration->CardId, TRUE );

/*			$card = $this->mangopay->Cards->Get( $registration->CardId );
			$this->addData( 'card', $card );
			$this->addData( 'cardId', $registration->CardId );*/
		}
		$this->messenger->noteNotice( 'You have to add a credit card first' );
		$this->restart( NULL, TRUE );
	}

	protected function handleErrorCode( $errorCode ){
		$errorCodes	= ADT_List_Dictionary::create( $this->words )->getAll( 'errorCode-' );
		if( !array_key_exists( $errorCode, $errorCodes ) )
			throw new InvalidArgumentException( 'Unknown error code: '.$errorCode );
		$this->messenger->noteError( $errorCodes[(string) $errorCode] );
	}
}
