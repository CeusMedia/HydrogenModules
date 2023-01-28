<?php

use CeusMedia\Common\ADT\Collection\Dictionary;

class Controller_Manage_My_Mangopay_Card extends Controller_Manage_My_Mangopay_Abstract
{
	protected array $words;
	protected string $sessionPrefix;

	public function add(): void
	{
		$this->restart( 'registration', TRUE );
	}

	public function deactivate( $cardId ): void
	{
		$card	= $this->checkIsOwnCard( $cardId );
		$card->Active	= FALSE;
		$this->mangopay->Cards->Update( $card );
		$this->messenger->noteSuccess( 'Card has been removed' );
		$this->cache->remove( 'user_'.$this->userId.'_cards' );
		$this->restart( NULL, TRUE );
	}

	public function index( $cardId = NULL, $refresh = NULL ): void
	{
		if( $cardId )
			$this->restart( 'view/'.$cardId, TRUE );
		try{
			$this->logic->skipCacheOnNextRequest( $refresh );
		}
		catch( \MangoPay\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
			$this->restart( 'manage/my/mangopay' );
		}
		catch( Exception $e ){
			$this->messenger->noteError( "Exception: ".$e->getMessage() );
			$this->restart( 'manage/my/mangopay' );
		}
		$this->addData( 'from', $this->session->get( $this->sessionPrefix.'from' ) );
	}

	public function edit( $cardId ): void
	{
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

	public function payOut(): void
	{
		throw new RuntimeException( 'Not implemented yet' );

		$wallets	= $this->logic->getUserWalletsByCurrency( $this->userId, $card->Currency, TRUE );

		$this->cache->remove( 'user_'.$this->userId.'_wallets' );
		$this->cache->remove( 'user_'.$this->userId.'_transactions' );
	}

	public function view( $cardId ): void
	{

		$card	= $this->checkIsOwnCard( $cardId );
		$this->addData( 'cardId', $cardId );
		$this->addData( 'card', $card );

		$wallets	= $this->logic->getUserWalletsByCurrency( $this->userId, $card->Currency, TRUE );

		$this->addData( 'wallets', $wallets );
		$this->addData( 'walletId', $walletId );
		$this->addData( 'walletLocked', FALSE );

		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/card' );
		$this->sessionPrefix	= 'manage_my_mangopay_card_';

		$cards	= $this->logic->getUserCards( $this->userId );
		foreach( $cards as $nr => $card ){
			if( !$card->Active )
				unset( $cards[$nr] );
		}
		$this->addData( 'cards', $cards );
	}

	protected function handleErrorCode( $errorCode, $goBack = TRUE ): void
	{
		$errorCodes	= Dictionary::create( $this->words )->getAll( 'errorCode-' );
		if( !array_key_exists( $errorCode, $errorCodes ) )
			throw new InvalidArgumentException( 'Unknown error code: '.$errorCode );
		$this->messenger->noteError( $errorCodes[(string) $errorCode] );

		if( $goBack )
			$this->followBackLink( 'payin_from' );
	}
}
