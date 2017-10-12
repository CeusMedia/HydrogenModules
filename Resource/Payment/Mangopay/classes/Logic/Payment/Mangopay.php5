<?php
class Logic_Payment_Mangopay extends CMF_Hydrogen_Logic{

	protected $cache;
	protected $provider;
	protected $skipCacheOnNextRequest;

	protected function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
		$this->cache		= $this->env->getCache();
		$this->provider		= Resource_Mangopay::getInstance( $this->env );
	}

	/**
	 *	Removes cache key of next API request if skipping next request is enabled.
	 *	Disables skipping next request afterwards.
	 *	To be called right before the next API request.
	 *	@access		protected
	 *	@param		string			$cacheKey			Cache key of entity to possible uncache
	 *	@return		void
	 */
	protected function applyPossibleCacheSkip( $cacheKey ){
		if( $this->skipCacheOnNextRequest ){
			$this->cache->remove( $cacheKey );
			$this->skipCacheOnNextRequest	= FALSE;
		}
	}

	public function deactivateBankAccount( $userId, $bankAccountId ){
		$bankAccount	= $this->getBankAccount( $userId, $bankAccountId );
		$bankAccount->Active = FALSE;
		$result	= $this->provider->Users->UpdateBankAccount( $userId, $bankAccount );
		$this->cache->remove( 'mangopay_user_'.$userId.'_bankaccounts' );
		$this->cache->remove( 'mangopay_user_'.$userId.'_bankaccount_'.$bankAccountId );
	}

	/**
	 *	@todo		implement type
	 */
	public function calculateFeesForPayIn( $price, $type = NULL ){
		return $price * $this->moduleConfig->get( 'fees.payin' ) / 100;
	}

	protected function checkIsOwnCard( $cardId ){
		$card	= $this->checkCard( $cardId );
	//	@todo check card against user cards
		return $card;
	}

	public function checkUser( $userId ){
		return $this->getUser( $userId );
	}

	public function createBankAccount( $userId, $iban, $bic, $title ){
		$user	= $this->getUser( $userId );
		$bankAccount = new \MangoPay\BankAccount();
		$bankAccount->Type			= "IBAN";
		$bankAccount->Details		= new \MangoPay\BankAccountDetailsIBAN();
		$bankAccount->Details->IBAN	= trim( str_replace( ' ', '', $iban ) );
		$bankAccount->Details->BIC	= trim( $bic );
		$bankAccount->OwnerName		= $title;
		$bankAccount->OwnerAddress	= $user->Address;
		$item	= $this->provider->Users->CreateBankAccount( $userId, $bankAccount );
		$this->cache->remove( 'mangopay_user_'.$userId.'_bankaccounts' );
		return $item;
	}

	public function createMandate( $bankAccountId, $returnUrl ){
		$mandate 	= new \MangoPay\Mandate();
		$mandate->BankAccountId	= $bankAccountId;
		$mandate->Culture		= "EN";
		$mandate->ReturnUrl		= $returnUrl;
		return $this->provider->Mandates->Create( $mandate );
	}

	public function getUserMandates( $userId ){
		$cacheKey	= 'mangopay_user_'.$userId.'_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= 	$this->provider->Users->GetMandates( $userId );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getBankAccountMandates( $userId, $bankAccountId ){
		$cacheKey	= 'mangopay_user_'.$userId.'_bankaccount_'.$bankAccountId.'_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= 	$this->provider->Users->GetMandatesForBankAccount( $userId, $bankAccountId );
print_m( $items );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getMandates(){
		$cacheKey	= 'mangopay_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= 	$this->provider->Mandates->GetAll();
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	/**
	 *	@todo		finish implementation, not working right now
	 */
	public function createPayInFromBankAccount( $userId, $walletId, $bankAccountId, $amount ){
		$bankAccount	= $this->getBankAccount( $userId, $bankAccountId );

		$payIn		= new \MangoPay\PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new \MangoPay\Money();
		$payIn->Fees				= new \MangoPay\Money();

		$payIn->Fees->Amount	= $this->calculateFeesForPayIn( $amount );
		$payIn->Fees->Currency	= "EUR";

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= "EUR";

		// payment type as BANKWIRE
		$payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsBankWire();
		$payIn->PaymentDetails->DeclaredDebitedFunds	= $payIn->DebitedFunds;
		$payIn->PaymentDetails->DeclaredFees			= $payIn->Fees;
/*		$payIn->PaymentDetails->BankAccount				= $bankAccount;
		$payIn->PaymentDetails->WireReference			= "BankWire PayIn 1";
*/
		// execution type as DIRECT
		$payIn->ExecutionDetails	= new \MangoPay\PayInExecutionDetailsDirect();

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createPayInFromCard( $userId, $walletId, $cardId, $amount, $secureModeReturnUrl ){

		$card	= $this->getCardById( $cardId );

		$payIn		= new \MangoPay\PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new \MangoPay\Money();
		$payIn->Fees				= new \MangoPay\Money();

	//	$amount	= $this->checkAmount( $amount, $this->currency );								//  @todo handle amount format and sanity

		$payIn->Fees->Amount	= $this->calculateFeesForPayIn( $amount );
		$payIn->Fees->Currency	= $card->Currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $card->Currency;

		// payment type as CARD
		$payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
		$payIn->PaymentDetails->CardType	= $card->CardType;
		$payIn->PaymentDetails->CardId		= $card->Id;

		// execution type as DIRECT
		$payIn->ExecutionDetails	= new \MangoPay\PayInExecutionDetailsDirect();
		$payIn->ExecutionDetails->SecureModeReturnURL = $secureModeReturnUrl;

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createPayInFromCardViaWeb( $userId, $walletId, $cardType, $currency, $amount, $returnUrl ){
		$user	= $this->checkUser( $userId );
		$payIn = new \MangoPay\PayIn();
		$payIn->CreditedWalletId = $walletId;
		$payIn->AuthorId = $userId;
		$payIn->PaymentType = "CARD";
		$payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
		$payIn->PaymentDetails->CardType = $cardType;
		$payIn->DebitedFunds = new \MangoPay\Money();
		$payIn->DebitedFunds->Currency = strtoupper( $currency );
		$payIn->DebitedFunds->Amount = $amount;
		$payIn->Fees = new \MangoPay\Money();
		$payIn->Fees->Currency = strtoupper( $currency );
		$payIn->Fees->Amount = $this->calculateFeesForPayIn( $amount );
		$payIn->ExecutionType = "WEB";
		$payIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsWeb();
		$payIn->ExecutionDetails->ReturnURL = $returnUrl;
		$payIn->ExecutionDetails->Culture = strtoupper( $user->Nationality );
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createNaturalUserFromLocalUser( $localUserId ){
throw new Exception("createNaturalUserFromLocalUser: ".$localUserId);
		$modelUser		= new Model_User( $this->env );
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$user			= $modelUser->get( $localUserId );

		$account	= new \MangoPay\UserNatural();
		$account->PersonType			= "NATURAL";
		$account->FirstName				= $user->firstname;
		$account->LastName				= $user->surname;
		$account->Birthday				= 0;
		$account->Nationality			= $user->country;
		$account->CountryOfResidence	= $user->country;
		$account->Email					= $user->email;
		$account	= $this->provider->Users->Create( $account );
		$modelAccount->add( array(
			'userId'			=> $localUserId,
			'paymentAccountId'	=> $account->Id,
			'provider'			=> 'mangopay',
		) );
		return $account;
	}


	public function createUserWallet( $userId, $currency ){
		$wallet		= new \MangoPay\Wallet();
		$wallet->Currency		= $currency;
		$wallet->Owners			= array( $userId );
		$wallet->Description	= $currency.' Wallet';
		return $this->provider->Wallets->Create( $wallet );
	}

	public function getBankAccount( $userId, $bankAccountId ){
		return $this->getUserBankAccount( $userId, $bankAccountId );
	}

	public function getBankAccounts( $userId ){
		return $this->getUserBankAccounts( $userId );
	}

	public function getCardById( $cardId ){
		$cacheKey	= 'mangopay_card_'.$cardId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Cards->Get( $cardId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getClientWallet( $fundsType, $currency ){
		return $this->provider->Clients->GetWallet( $fundsType, $currency );
	}

	public function getEventResource( $eventType, $resourceId, $force = FALSE ){
		switch( $eventType ){
			case 'PAYIN_NORMAL_SUCCEEDED':
			case 'PAYIN_NORMAL_FAILED':
				$method	= 'getPayin';
				break;
			case 'PAYOUT_NORMAL_SUCCEEDED':
			case 'PAYOUT_NORMAL_FAILED':
				$method	= 'getPayout';
				break;
			case 'TRANSFER_NORMAL_SUCCEEDED':
			case 'TRANSFER_NORMAL_FAILED':
				$method	= 'getTransfer';
				break;
			default:
				return NULL;
		}
		if( !method_exists( $this, $method ) )
			throw new BadMethodCallException( 'Method "'.$method.'" is not existing' );
		if( $force )
			$this->skipCacheOnNextRequest( TRUE );
		$factory	= new Alg_Object_MethodFactory();
		return $factory->call( $this->mangopay, $method, array( $resourceId ) );
	}

	public function getHook( $hookId ){
		$cacheKey	= 'mangopay_hook_'.$hookId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Hooks->Get( $hookId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getHooks( $refresh = FALSE ){
		$cacheKey	= 'mangopay_hooks';
		$refresh ? $this->skipCacheOnNextRequest( TRUE ) : NULL;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= $this->provider->Hooks->GetAll();
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getPayin( $payInId ){
		return $this->provider->PayIns->Get( $payInId );
	}

	public function getPayout( $payOutId ){
		return $this->provider->PayOuts->Get( $payOutId );
	}

	public function getTransfer( $transferId ){
		return $this->provider->Transfers->Get( $transferId );
	}

	public function getUser( $userId ){
		$cacheKey	= 'mangopay_user_'.$userId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Users->Get( $userId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserBankAccount( $userId, $bankAccountId ){
		$cacheKey	= 'mangopay_user_'.$userId.'_bankaccount_'.$bankAccountId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Users->GetBankAccount( $userId, $bankAccountId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserBankAccounts( $userId ){
		$cacheKey	= 'mangopay_user_'.$userId.'_bankaccounts';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$pagination	= new \MangoPay\Pagination();
			$sorting	= new \MangoPay\Sorting();
			$sorting->AddField( 'CreationDate', 'DESC' );
			$items		= $this->provider->Users->GetBankAccounts( $userId, $pagination, $sorting );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getUserCards( $userId, $conditions = array(), $orders = array(), $limits = array() ){
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		if( !$orders )
			$sorting->AddField( 'CreationDate', 'DESC' );
		else{
			foreach( $orders as $orderKey => $orderValue )
				$sorting->AddField( $orderKey, strtoupper( $orderValue ) );
		}
		$cacheKey	= 'mangopay_user_'.$userId.'_cards';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= $this->provider->Users->GetCards( $userId, $pagination, $sorting );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getUserWallet( $walletId ){
		$cacheKey	= 'mangopay_wallet_'.$walletId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Wallets->Get( $walletId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserWallets( $userId, $orders = array(), $limits = array() ){
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		if( !$orders )
			$sorting->AddField( 'CreationDate', 'DESC' );
		else{
			foreach( $orders as $orderKey => $orderValue )
				$sorting->AddField( $orderKey, strtoupper( $orderValue ) );
		}
		return $this->provider->Users->GetWallets( $userId, $pagination, $sorting );
	}

	public function getUserWalletsByCurrency( $userId, $currency, $force = FALSE ){
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$all	= $this->provider->Users->GetWallets( $userId, $pagination, $sorting );
		$list	= array();
		foreach( $all as $wallet )
			if( $wallet->Currency === $currency )
				$list[]	= $wallet;

		if( !$list && $force ){
			$wallet	= $this->createUserWallet( $userId, $currency );
			$list[]	= $wallet;
		}
		return $list;
	}

	public function getUserIdFromLocalUserId( $localUserId ){
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
		) );
		if( !$relation )
			throw new RuntimeException( 'No payment account available' );
		return $relation->paymentAccountId;
	}

	/**
	 *	@todo		extend cache key by filters
	 */
	public function getWalletTransactions( $walletId, $orders = array(), $limits = array() ){
		$cacheKey	= 'mangopay_wallet_'.$walletId.'_transactions';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$pagination	= $this->provider->getDefaultPagination();
			$sorting	= $this->provider->getDefaultSorting();
	//		$sorting->AddField( 'CreationDate', 'ASC' );
			$filter		= new \MangoPay\FilterTransactions();
			$items		= $this->provider->Wallets->GetTransactions( $walletId, $pagination, $filter, $sorting );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function hasPaymentAccount( $localUserId ){
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->countByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
		) );
		return $relation;
	}

	public function setHook( $id, $eventType, $path, $status = NULL, $tag = NULL ){
		if( $id > 0 ){
			$hook			= $this->provider->Hooks->Get( $id );
			if( $status !== NULL )
				$hook->Status	= $status ? 'ENABLED' : 'DISABLED';
			if( $tag !== NULL )
				$hook->Tag	= trim( $tag );
			$hook->Url			= $this->env->url.trim( $path );
			$this->cache->remove( 'mangopay_hooks' );
			$this->cache->remove( 'mangopay_hook_'.$id );
			return $this->provider->Hooks->Update( $hook );
		}
		else{
			$hook				= new \MangoPay\Hook;
			$hook->EventType	= $eventType;
			$hook->Url			= $this->env->url.trim( $path );
			if( $tag !== NULL )
				$hook->Tag	= trim( $tag );
			return $this->provider->Hooks->Create( $hook );
		}
	}

	public function skipCacheOnNextRequest( $skip ){
		$this->skipCacheOnNextRequest	= (bool) $skip;
	}

	public function updateUser( $user ){
		$this->cache->remove( 'user_'.$user->Id );
		return $this->provider->Users->Update( $user );
	}

	/**
	 *	@link	https://stackoverflow.com/a/174772
	 */
	static public function validateCardNumber( $number, $provider ){
		return TRUE;
		$providerPatterns = array(
			"VISA"			=> "(4\d{12}(?:\d{3})?)",
			"MAESTRO"		=> "((?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?)",
			"MASTERCARD"	=> "(5[1-5]\d{14})",
			"AMEX"			=> "(3[47]\d{13})",
		);
		if( !array_key_exists( $provider, $providerPatterns ) )
			return NULL;
		$pattern	= '#^'.$providerPatterns[$provider].'$#';
		$number		= trim( str_replace( " ", "", $number ) );
		return preg_match( $pattern, $number );
	}
}
