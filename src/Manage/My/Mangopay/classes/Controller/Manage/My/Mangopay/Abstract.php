<?php

use CeusMedia\Cache\SimpleCacheInterface;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

abstract class Controller_Manage_My_Mangopay_Abstract extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $session;
	protected Logic_Payment_Mangopay $logic;
	protected Resource_Mangopay $mangopay;
	protected string $userId;
	protected SimpleCacheInterface $cache;

	protected ?string $sessionPrefix	= NULL;

	protected string $currency		= "EUR";
	protected float $factorFees		= 0.1;

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->cache		= $this->env->getCache();
		$this->logic		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->mangopay		= Resource_Mangopay::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_mangopay.', TRUE );

		$modelAccount		= new Model_User_Payment_Account( $this->env );
		$localUserId		= $this->session->get( 'auth_user_id' );

		try{
			if( !$this->logic->hasPaymentAccount( $localUserId ) )
				$this->logic->createNaturalUserFromLocalUser( $localUserId );
			$this->userId	= $this->logic->getUserIdFromLocalUserId( $localUserId );
		}
		catch( RuntimeException $e ){
			$this->messenger->noteFailure( 'Registration on payment provider failed: '.$e->getMessage() );
		}
	}

	public function checkBankAccount( string $bankAccountId, bool $strict = TRUE )
	{
		$item	= $this->logic->getBankAccount( $this->userId, $bankAccountId );
		if( !$item->Active ){
			if( !$strict )
				return FALSE;
			throw new RuntimeException( 'Bank account has been disabled' );
		}
		return $item;
	}

	public function checkIsOwnBankAccount( string $bankAccountId, bool $strict = TRUE )
	{
		$bankAccount	= $this->checkBankAccount( $bankAccountId, $strict );
		if( !$bankAccount )
			return FALSE;
		$bankAccounts	= $this->logic->getUserBankAccounts( $this->userId );
		foreach( $bankAccounts as $item )
			if( $item->Id === $bankAccount->Id )
				return $bankAccount;
		if( !$strict )
			return FALSE;
		throw new DomainException( 'Access to this bank account is denied' );
	}

	protected function checkIsOwnCard( string $cardId, bool $strict = TRUE, array $fallback = [] )
	{
		if( !is_array( $fallback ) || !count( $fallback ) )
			$fallback	= [NULL, TRUE];
		$card	= $this->checkCard( $cardId, $fallback );
	//	@todo check card against user cards
		return $card;
	}

	protected function checkCard( string $cardId, array $fallback = [] )
	{
		try{
			if( !strlen( trim( $cardId ) ) )
				throw new InvalidArgumentException( 'No card ID given' );
			return $this->logic->getCardById( $cardId );
		}
		catch( \MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
		}
		catch( Exception $e ){
//			$this->messenger->noteNotice( "Exception: ".$e->getMessage( ) );
			$this->messenger->noteError( "Invalid card ID given." );
		}
		if( !is_array( $fallback ) || !count( $fallback ) )
			$fallback	= [NULL, TRUE];
		if( count( $fallback ) == 1 )
			$fallback[1]	= FALSE;
		$this->restart( $fallback[0], $fallback[1] );
	}

	protected function checkWallet( string $userId, string $walletId, bool $strict = TRUE )
	{
		if( $strict )
			return $this->logic->getUserWallet( $userId, $walletId );
		try{
			return $this->logic->getUserWallet( $userId, $walletId );
		}
		catch( Exception $e ){
//			$this->messenger->noteNotice( "Exception: ".$e->getMessage( ) );
			$this->messenger->noteError( "Invalid wallet ID given." );
			$this->restart( NULL, TRUE );
		}
	}

	protected function checkWalletIsOwn( string $walletId, bool $strict = TRUE )
	{
		$wallet		= $this->checkWallet( $this->userId, $walletId );
		$wallets	= $this->logic->getUserWallets( $this->userId );
		foreach( $wallets as $item )
			if( $item->Id == $wallet->Id )
				return $wallet;
		if( !$strict )
			return FALSE;
		throw new DomainException( 'Access to this wallet is denied' );
	}

	protected function followBackLink( string $sessionKey )
	{
		$from	= $this->session->get( $this->sessionPrefix.$sessionKey );
		if( !$from )
			return;
		$this->session->remove( $this->sessionPrefix.$sessionKey );
		$this->restart( $from );
	}

	protected function handleMangopayResponseException( $e )
	{
		ob_start();
		print_r( $e->GetErrorDetails()->Errors );
		$details	= ob_get_clean();
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}

	protected function saveBackLink( $requestKey, $sessionKey, $override = FALSE )
	{
		$from		= $this->request->get( $requestKey );
		if( !$from )
			return;
		$current	= $this->session->get( $this->sessionPrefix.$sessionKey );
		if( !$current || $override )
			$this->session->set( $this->sessionPrefix.$sessionKey, $from );
	}
}
