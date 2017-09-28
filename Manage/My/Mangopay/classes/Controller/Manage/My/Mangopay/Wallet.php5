<?php
class Controller_Manage_My_Mangopay_Wallet extends Controller_Manage_My_Mangopay{

	public function index(){
		$pagination	= $this->mangopay->getDefaultPagination();
		$sorting	= $this->mangopay->getDefaultSorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		try{
			$this->addData( 'wallets', $this->mangopay->Users->GetWallets( $this->userId, $pagination, $sorting ));
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

	public function payIn( $walletId, $type = NULL ){
		$wallet		= $this->checkWalletIsOwn( $walletId );
		$this->addData( 'walletId', $walletId );
		$this->addData( 'wallet', $wallet );
		$this->addData( 'type', $type );

		if( $type ){
			switch( $type ){
				case 'bankwire':
					if( $this->request->has( 'amount' ) && $this->request->get( 'currency' ) ){
						$payIn		= new \MangoPay\PayIn();
						$payIn->CreditedWalletId		= $walletId;
						$payIn->AuthorId				= $this->userId;											//  @todo inset user ID from session

						$amount	= $this->request->get( 'amount' );
					//	$amount	= $this->checkAmount( $amount, $this->currency );									//  @todo handle amount format and sanity

						// payment type as CARD
						$payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsBankWire();
						$payIn->PaymentDetails->DeclaredDebitedFunds			= new \MangoPay\Money();
						$payIn->PaymentDetails->DeclaredDebitedFunds->Amount	= $amount;
						$payIn->PaymentDetails->DeclaredDebitedFunds->Currency	= $this->currency;
						$payIn->PaymentDetails->DeclaredFees					= new \MangoPay\Money();
						$payIn->PaymentDetails->DeclaredFees->Amount			= $amount * $this->factorFees;
						$payIn->PaymentDetails->DeclaredFees->Currency			= $this->currency;
						$payIn->ExecutionDetails	= new \MangoPay\PayInExecutionDetailsDirect();
						$createPayIn = $this->mangopay->PayIns->Create( $payIn );

						$this->addData( 'payin', $createPayIn );
						$this->addData( 'amount', $amount );
						$this->addData( 'currency', $this->currency );
					}
					break;
				case 'card':
					$cardId	= $this->request->get( 'cardId' );
					if( $cardId ){
						$this->checkIsOwnCard( $cardId );
						$this->restart( './manage/my/mangopay/card/payin/preAuthorized/'.$cardId.'?walletId='.$walletId.'&from=manage/my/mangopay/wallet/view/'.$walletId );
					}
					$pagination	= new \MangoPay\Pagination();
					$sorting	= new \MangoPay\Sorting();
					$sorting->AddField( 'CreationDate', 'DESC' );
					$cards	= $this->mangopay->Users->GetCards( $this->userId, $pagination, $sorting );
					$this->addData( 'cards', $cards );
					break;
				default:
					throw new InvalidArgumentException( 'Unsupported pay in type: %s', $type );
			}
		}
	}

	public function payInViaBankwire( $walletId ){
		$wallet		= $this->checkWalletIsOwn( $walletId );
		$this->addData( 'walletId', $walletId );
		$this->addData( 'wallet', $wallet );

	}

	public function payOut( $walletId ){
		$wallet		= $this->checkWalletIsOwn( $walletId );

		if( $this->request->has( 'save' ) ){
			$amount	= $this->request->get( 'amount' );
			if( $amount < 100 ){
				$this->messenger->noteError( 'Amount must be larger than 1' );
				$this->restart( 'payOut/'.$walletId, TRUE );
			}
			//  payout
		}


		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$this->addData( 'bankAccounts', $this->mangopay->Users->GetBankAccounts( $this->userId, $pagination, $sorting ));

		$this->addData( 'wallet', $wallet );
		$this->addData( 'walletId', $walletId );
	}

	public function view( $walletId ){
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );

		$wallet			= $this->checkWalletIsOwn( $walletId );
		try{
			$this->addData( 'walletId', $walletId );
			$this->addData( 'userId', $userId );
			$this->addData( 'wallet', $wallet );

			$pagination	= $this->mangopay->getDefaultPagination();
			$sorting	= $this->mangopay->getDefaultSorting();
//			$sorting->AddField( 'CreationDate', 'ASC' );
			$filter		= new \MangoPay\FilterTransactions();
			$transactions	= $this->mangopay->Wallets->GetTransactions( $walletId, $pagination, $filter, $sorting );
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
	}

	public function transfer( $sourceWalletId ){
		$sourceWallet		= $this->mangopay->Wallets->Get( $sourceWalletId );

		$targetWallets		= array();
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
	}
}
