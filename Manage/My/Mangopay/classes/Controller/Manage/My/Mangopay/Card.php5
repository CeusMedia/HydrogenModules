<?php
class Controller_Manage_My_Mangopay_Card extends Controller_Manage_My_Mangopay_Abstract{

	protected $words;

	public function __onInit(){
		parent::__onInit();
		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/card' );
		$this->sessionPrefix	= 'manage_my_mangopay_card_';
	}

	public function add(){
		$this->restart( 'registration', TRUE );
	}

	public function deactivate( $cardId ){
		$card	= $this->checkIsOwnCard( $cardId );
		$card->Active	= FALSE;
		$this->mangopay->Cards->Update( $card );
		$this->messenger->noteSuccess( 'Card has been removed' );
		$this->cache->remove( 'user_'.$this->userId.'_cards' );
		$this->restart( NULL, TRUE );
	}

	protected function handleErrorCode( $errorCode, $goBack = TRUE ){
		$errorCodes	= ADT_List_Dictionary::create( $this->words )->getAll( 'errorCode-' );
		if( !array_key_exists( $errorCode, $errorCodes ) )
			throw new InvalidArgumentException( 'Unknown error code: '.$errorCode );
		$this->messenger->noteError( $errorCodes[(string) $errorCode] );

		if( $goBack )
			$this->followBackLink( 'payin_from' );
	}

	public function index( $cardId = NULL, $refresh = NULL ){
		if( $cardId )
			$this->restart( 'view/'.$cardId, TRUE );
		try{
			$this->logic->skipCacheOnNextRequest( $refresh );
			$cards	= $this->logic->getUserCards( $this->userId );
			foreach( $cards as $nr => $card ){
				if( !$card->Active )
					unset( $cards[$nr] );
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

/*	public function payIn( $cardId ){
//		$this->restart( 'payin', TRUE );
	}*/

	protected function followBackLink( $sessionKey ){
		$from = $this->session->get( $this->sessionPrefix.$sessionKey );
		if( $from ){
			$this->session->remove( $this->sessionPrefix.$sessionKey );
			$this->restart( $from );
		}
	}

	protected function saveBackLink( $requestKey, $sessionKey ){
		$from = $this->request->get( $requestKey );
		if( $from )
			$this->session->set( $this->sessionPrefix.$sessionKey, $from );
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
