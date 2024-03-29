<?php
class Controller_Manage_My_Mangopay_Wallet extends Controller_Manage_My_Mangopay_Abstract{

	public function index( $walletId = NULL ): void
	{
		if( $walletId )
			$this->restart( 'view/'.$walletId, TRUE );

		try{
			$this->addData( 'wallets', $this->logic->getUserWallets( $this->userId ) );
		}
		catch( \MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteFailure( 'Exception: '.$e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function view( $walletId, $amount = NULL ): void
	{
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );

		$wallet			= $this->checkWalletIsOwn( $walletId );
		try{
			$this->addData( 'walletId', $walletId );
			$this->addData( 'userId', $this->userId );
			$this->addData( 'wallet', $wallet );

			$transactions	= $this->logic->getWalletTransactions( $walletId );

			$this->addData( 'transactions', $transactions );
		}
		catch( \MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( 'Invalid User ID' );
			$this->restart( NULL, TRUE );
		}

		$cards	= $this->logic->getUserCards( $this->userId );
		foreach( $cards as $nr => $card ){
			if( !$card->Active || $card->Currency !== $wallet->Currency )
				unset( $cards[$nr] );
		}
		$this->addData( 'cards', $cards );

		$bankAccounts	= $this->logic->getUserBankAccounts( $this->userId );
		foreach( $bankAccounts as $nr => $bankAccount ){
			if( !$bankAccount->Active )
				unset( $bankAccounts[$nr] );
		}
		$this->addData( 'bankAccounts', $bankAccounts );
		$this->addData( 'wordsCards', $this->getWords( 'cardTypes', 'manage/my/mangopay/card' ) );
		$this->addData( 'amount', $amount );
	}

/*	public function transfer( $sourceWalletId ): void
	{
		$sourceWallet		= $this->mangopay->Wallets->Get( $sourceWalletId );

		$targetWallets		= [];
		foreach( $this->mangopay->Users->GetWallets( $this->userId ) as $wallet )
			if( $wallet->Id != $sourceWalletId )
				if( $wallet->Currency === $sourceWallet->Currency )
					$targetWallets[$wallet->Id]	= $wallet;

		if( $this->request->has( 'amount' ) ){
			$targetWalletId		= $this->request->get( 'targetWalletId' );
			$amount				= $this->request->get( 'amount' );
			$currency			= $sourceWallet->Currency;

			$transfer			= new \MangoPay\Transfer();
			$transfer->AuthorId			= $this->userId;											//  @todo inset user ID from session
			$transfer->CreditedUserId	= $this->userId;											//  @todo inset user ID from session
			$transfer->CreditedWalletId	= $targetWalletId;
			$transfer->DebitedWalletId	= $sourceWalletId;
			$transfer->DebitedFunds		= new \MangoPay\Money();
			$transfer->DebitedFunds->Amount		= $amount;
			$transfer->DebitedFunds->Currency	= $currency;
			$transfer->Fees				= new \MangoPay\Money();
			$transfer->Fees->Amount		= 0;
			$transfer->Fees->Currency	= $currency;
			$result	= $this->mangopay->Transfers->create( $transfer );
			print_m( $result );
			die;
			$this->restart( 'view/'.$walletId, TRUE );
		}

		$this->addData( 'sourceWalletId', $sourceWalletId );
		$this->addData( 'sourceWallet', $sourceWallet );
		$this->addData( 'targetWallets', $targetWallets );
	}*/
}
