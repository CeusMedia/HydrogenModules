<?php
class Logic_Payment_Stripe extends CMF_Hydrogen_Logic{

	protected $cache;
	protected $provider;
	protected $skipCacheOnNextRequest;
	protected $baseUrl;

	static public $typeCurrencies	= array(
		'CB_VISA_MASTERCARD'	=> array(),
		'MAESTRO'				=> array( 'EUR' ),
		'DINERS'				=> array( 'EUR' ),
		'GIROPAY'				=> array( 'EUR' ),
		'IDEAL'					=> array( 'EUR' ),
		'PAYLIB'				=> array( 'EUR' ),
		'SOFORT'				=> array( 'EUR' ),
		'BCMC'					=> array( 'EUR' ),
		'P24'					=> array( 'PLN' ),
		'BANKWIRE'				=> array(),
	);

	protected function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_stripe.', TRUE );
		\Stripe\Stripe::setApiKey( $this->moduleConfig->get( 'api.key.secret' ) );

//		print_m( $this->moduleConfig->getAll() );die;
		$this->cache		= $this->env->getCache();
		$this->provider		= Resource_Stripe::getInstance( $this->env );
		$this->baseUrl		= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->baseUrl	= Logic_Frontend::getInstance( $this->env )->getUri();
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

	/**
	 *	@todo		implement type
	 */
	public function calculateFeesForPayIn( $price, $currency ){
		return 0;
	}

	protected function checkIsOwnCard( $cardId ){
		$card	= $this->checkCard( $cardId );
	//	@todo check card against user cards
		return $card;
	}

	public function checkUser( $userId ){
		return $this->getUser( $userId );
	}

	public function createAddress( $street, $postcode, $city, $country, $region = NULL ){
		throw new Exception( 'Not implemented yet' );
		$address = new \Stripe\Address();
		$address->AddressLine1	= $street;
		$address->PostalCode	= $postcode;
		$address->City			= $city;
		$address->Country		= $country;
		if( $region )
			$address->Region		= $region;
		return $address;
	}

	public function createBankAccount( $userId, $iban, $bic, $title, $address = NULL ){
		throw new Exception( 'Not implemented yet' );
		$user	= $this->getUser( $userId );
		$bankAccount = new \Stripe\BankAccount();
		$bankAccount->Type			= "IBAN";
		$bankAccount->Details		= new \Stripe\BankAccountDetailsIBAN();
		$bankAccount->Details->IBAN	= trim( str_replace( ' ', '', $iban ) );
		$bankAccount->Details->BIC	= trim( $bic );
		$bankAccount->OwnerName		= $title;
		if( $address )
			$bankAccount->OwnerAddress	= $address;
		else if( $user instanceof \Stripe\UserNatural )
			$bankAccount->OwnerAddress	= $user->Address;
		else if( $user instanceof \Stripe\UserLegal )
			$bankAccount->OwnerAddress	= $user->LegalRepresentativeAddress;
		$item	= $this->provider->Users->CreateBankAccount( $userId, $bankAccount );
		$this->uncache( 'user_'.$userId.'_bankaccounts' );
		return $item;
	}

	public function createMandate( $bankAccountId, $returnUrl ){
		throw new Exception( 'Not implemented yet' );
		$mandate 	= new \Strope\Mandate();
		$mandate->BankAccountId	= $bankAccountId;
		$mandate->Culture		= "EN";
		$mandate->ReturnUrl		= $returnUrl;
		return $this->provider->Mandates->Create( $mandate );
	}

	public function getUserMandates( $userId ){
		throw new Exception( 'Not implemented yet' );
		$cacheKey	= 'stripe_user_'.$userId.'_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= 	$this->provider->Users->GetMandates( $userId );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getBankAccountMandates( $userId, $bankAccountId ){
		throw new Exception( 'Not implemented yet' );
		$cacheKey	= 'strope_user_'.$userId.'_bankaccount_'.$bankAccountId.'_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= 	$this->provider->Users->GetMandatesForBankAccount( $userId, $bankAccountId );
print_m( $items );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getMandates(){
		throw new Exception( 'Not implemented yet' );
		$cacheKey	= 'stripe_mandates';
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
	public function createPayInFromBankAccount( $userId, $walletId, $bankAccountId, $currency, $amount ){
		throw new Exception( 'Not implemented yet' );
//		$bankAccount	= $this->getBankAccount( $userId, $bankAccountId );

		$payIn		= new \Stripe\PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new \Stripe\Money();
		$payIn->Fees				= new \Stripe\Money();

		$payIn->Fees->Amount	= $this->calculateFeesForPayIn( $amount );
		$payIn->Fees->Currency	= $currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $currency;

		// payment type as BANKWIRE
		$payIn->PaymentDetails = new \Stripe\PayInPaymentDetailsBankWire();
		$payIn->PaymentDetails->DeclaredDebitedFunds	= $payIn->DebitedFunds;
		$payIn->PaymentDetails->DeclaredFees			= $payIn->Fees;
/*		$payIn->PaymentDetails->BankAccount				= $bankAccount;
		$payIn->PaymentDetails->WireReference			= "BankWire PayIn 1";
*/
		// execution type as DIRECT
		$payIn->ExecutionDetails	= new \Stripe\PayInExecutionDetailsDirect();

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	/**
	 *	@todo		test (not tested since no mandates allowed, yet)
	 */
	public function createPayInFromBankAccountViaDirectDebit( $userId, $mandateId, $currency, $amount ){
		throw new Exception( 'Not implemented yet' );

		$payIn	= new \Stripe\PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new \Stripe\Money();
		$payIn->Fees				= new \Stripe\Money();

		$payIn->Fees->Amount	= $this->calculateFeesForPayIn( $amount );
		$payIn->Fees->Currency	= $currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $currency;

		$payIn->PaymentDetails	= new \Stripe\PayInPaymentDetailsDirectDebitDirect();
#		$payIn->PaymentDetails->MandateId	=

		// execution type as DIRECT
		$payIn->ExecutionDetails	= new \Stripe\PayInExecutionDetailsDirect();

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createPayInFromCard( $userId, $walletId, $cardId, $amount, $secureModeReturnUrl ){
		throw new Exception( 'Not implemented yet' );

		$card	= $this->getCardById( $cardId );

		$payIn		= new \Stripe\PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new \Stripe\Money();
		$payIn->Fees				= new \Stripe\Money();

	//	$amount	= $this->checkAmount( $amount, $this->currency );								//  @todo handle amount format and sanity

		$payIn->Fees->Amount	= 0;
		$payIn->Fees->Currency	= $card->Currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $card->Currency;

		// payment type as CARD
		$payIn->PaymentDetails = new \Stripe\PayInPaymentDetailsCard();
		$payIn->PaymentDetails->CardType	= $card->CardType;
		$payIn->PaymentDetails->CardId		= $card->Id;

		// execution type as DIRECT
		$payIn->ExecutionDetails	= new \Stripe\PayInExecutionDetailsDirect();
		$payIn->ExecutionDetails->SecureModeReturnURL = $secureModeReturnUrl;

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createBankPayInViaWeb( $type, $userId, $walletId, $currency, $amount, $returnUrl ){
		throw new Exception( 'Not implemented yet' );
		$user	= $this->checkUser( $userId );
		$payIn	= new \Stripe\PayIn();
		$payIn->CreditedWalletId	= $walletId;
		$payIn->AuthorId			= $userId;
		$payIn->PaymentDetails		= new \Stripe\PayInPaymentDetailsDirectDebit();
		$payIn->DirectDebitType		= $type;
		$payIn->PaymentDetails->DebitedFunds			= new \Stripe\Money();
		$payIn->PaymentDetails->DebitedFunds->Amount	= $amount;
		$payIn->PaymentDetails->DebitedFunds->Currency	= $currency;
		$payIn->PaymentDetails->Fees					= new \Stripe\Money();
		$payIn->PaymentDetails->Fees->Amount			= 0;
		$payIn->PaymentDetails->Fees->Currency			= $currency;
		$payIn->ExecutionDetails			= new \Stripe\PayInExecutionDetailsWeb();
		$payIn->ExecutionDetails->ReturnURL	= $returnUrl;
		$payIn->ExecutionDetails->Culture	= strtoupper( $user->Nationality );
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createCardPayInViaWeb( $userId, $walletId, $cardType, $currency, $amount, $returnUrl ){
		throw new Exception( 'Not implemented yet' );
		$user	= $this->checkUser( $userId );
		$payIn	= new \Stripe\PayIn();
		$payIn->CreditedWalletId			= $walletId;
		$payIn->AuthorId					= $userId;
		$payIn->PaymentType					= "CARD";
		$payIn->ExecutionType				= "WEB";
		$payIn->PaymentDetails				= new \Stripe\PayInPaymentDetailsCard();
		$payIn->PaymentDetails->CardType	= $cardType;
		$payIn->DebitedFunds				= new \Stripe\Money();
		$payIn->DebitedFunds->Currency		= strtoupper( $currency );
		$payIn->DebitedFunds->Amount		= $amount;
		$payIn->Fees						= new \Stripe\Money();
		$payIn->Fees->Currency				= strtoupper( $currency );
		$payIn->Fees->Amount				= 0;
		$payIn->ExecutionDetails			= new \Stripe\PayInExecutionDetailsWeb();
		$payIn->ExecutionDetails->ReturnURL	= $returnUrl;
		$payIn->ExecutionDetails->Culture	= strtoupper( $user->Nationality );
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createLegalUserFromLocalUser( $localUserId, $companyData, $representativeData ){
		throw new Exception( 'Not implemented yet' );
		$modelUser		= new Model_User( $this->env );
		$modelAddress	= new Model_Address( $this->env );
		$user			= $modelUser->get( $localUserId );
		$address		= $modelAddress->get( array(
			'relationType'	=> 'user',
			'relationId'	=> $this->localUserId,
			'type'			=> Model_Address::TYPE_BILLING,
		) );

		$user = new \Stripe\UserLegal();
		$user->LegalPersonType	= "BUSINESS";
		$user->Name				= $companyData['name'];
//		$user->Tag				= NULL;
		$user->Email			= $companyData['email'];
//		$user->CompanyNumber	= NULL;
		$user->LegalRepresentativeBirthday				= 0;
		$user->LegalRepresentativeCountryOfResidence	= $address->country;
		$user->LegalRepresentativeNationality			= $address->country;
		$user->LegalRepresentativeEmail					= $representativeData['email'];
		$user->LegalRepresentativeFirstName				= $representativeData['firstname'];
		$user->LegalRepresentativeLastName				= $representativeData['lastname'];
		if( $address ){
			$user->LegalRepresentativeAddress = new \Stripe\Address();
			$user->LegalRepresentativeAddress->AddressLine1	= $address->street;
			$user->LegalRepresentativeAddress->City			= $address->city;
			$user->LegalRepresentativeAddress->Region		= $address->region;
			$user->LegalRepresentativeAddress->PostalCode	= $address->postcode;
			$user->LegalRepresentativeAddress->Country		= $address->country;
			$user->HeadquartersAddress = new \Stripe\Address();
			$user->HeadquartersAddress->AddressLine1	= $address->street;
			$user->HeadquartersAddress->City			= $address->city;
			$user->HeadquartersAddress->Region			= $address->region;
			$user->HeadquartersAddress->PostalCode		= $address->postcode;
			$user->HeadquartersAddress->Country			= $address->country;
		}
		$user = $this->provider->Users->Create( $user );
	}

	public function createLegalUser( $data ){
		throw new Exception( 'Not implemented yet' );
		$user = new \Stripe\UserLegal();
		$user->LegalPersonType	= $data['company']['type'];
		$user->Name				= $data['company']['name'];
		$user->Email			= $data['company']['email'];
		$user->CompanyNumber	= $data['company']['number'];
//		$user->Tag				= NULL;
		$user->LegalRepresentativeBirthday				= 0;
		$user->LegalRepresentativeCountryOfResidence	= $data['representative']['country'];
		$user->LegalRepresentativeNationality			= $data['representative']['country'];
		$user->LegalRepresentativeEmail					= $data['representative']['email'];
		$user->LegalRepresentativeFirstName				= $data['representative']['firstname'];
		$user->LegalRepresentativeLastName				= $data['representative']['surname'];
		$user->LegalRepresentativeAddress = new \Stripe\Address();
		$user->LegalRepresentativeAddress->AddressLine1	= $data['representative']['address'];
		$user->LegalRepresentativeAddress->City			= $data['representative']['city'];
		$user->LegalRepresentativeAddress->Region		= $data['representative']['region'];
		$user->LegalRepresentativeAddress->PostalCode	= $data['representative']['postcode'];
		$user->LegalRepresentativeAddress->Country		= $data['representative']['country'];
		$user->HeadquartersAddress = new \Stripe\Address();
		$user->HeadquartersAddress->AddressLine1	= $data['headquarter']['address'];
		$user->HeadquartersAddress->City			= $data['headquarter']['city'];
		$user->HeadquartersAddress->Region			= $data['headquarter']['region'];
		$user->HeadquartersAddress->PostalCode		= $data['headquarter']['postcode'];
		$user->HeadquartersAddress->Country			= $data['headquarter']['country'];
//print_m( $user );die;
		return $this->provider->Users->Create( $user );
	}

	public function updateLegalUser( $userId, $data ){
		throw new Exception( 'Not implemented yet' );
		$user	= $this->getUser( $userId );
		$user->LegalPersonType	= $data['company']['type'];
		$user->Name				= $data['company']['name'];
		$user->Email			= $data['company']['email'];
		$user->CompanyNumber	= $data['company']['number'];
//		$user->Tag				= NULL;
		$user->LegalRepresentativeBirthday				= 0;
		$user->LegalRepresentativeCountryOfResidence	= $data['representative']['country'];
		$user->LegalRepresentativeNationality			= $data['representative']['country'];
		$user->LegalRepresentativeEmail					= $data['representative']['email'];
		$user->LegalRepresentativeFirstName				= $data['representative']['firstname'];
		$user->LegalRepresentativeLastName				= $data['representative']['surname'];
		$user->LegalRepresentativeAddress = new \Stripe\Address();
		$user->LegalRepresentativeAddress->AddressLine1	= $data['representative']['address'];
		$user->LegalRepresentativeAddress->City			= $data['representative']['city'];
		$user->LegalRepresentativeAddress->Region		= $data['representative']['region'];
		$user->LegalRepresentativeAddress->PostalCode	= $data['representative']['postcode'];
		$user->LegalRepresentativeAddress->Country		= $data['representative']['country'];
		$user->HeadquartersAddress = new \Stripe\Address();
		$user->HeadquartersAddress->AddressLine1	= $data['headquarter']['address'];
		$user->HeadquartersAddress->City			= $data['headquarter']['city'];
		$user->HeadquartersAddress->Region			= $data['headquarter']['region'];
		$user->HeadquartersAddress->PostalCode		= $data['headquarter']['postcode'];
		$user->HeadquartersAddress->Country			= $data['headquarter']['country'];
//print_m( $user );die;
		return $this->provider->Users->Update( $user );
	}

	public function createUserFromLocalUser( $localUserId ){
		$modelUser		= new Model_User( $this->env );
		$modelAddress	= new Model_Address( $this->env );
		$user			= $modelUser->get( $localUserId );
		$address		= $modelAddress->get( array(
			'relationType'	=> 'user',
			'relationId'	=> $this->localUserId,
			'type'			=> Model_Address::TYPE_BILLING,
		) );

		$data	= array(
				'email'			=> $user->email,
				'description'	=> $user->username.' ('.$user->firstname.' '.$user->surname.')',
		);
		$user	= \Stripe\Customer::create( $data );
		$this->setUserIdForLocalUserId( $user->id, $localUserId );
		return $user;
	}

	public function createUserWallet( $userId, $currency ){
		throw new Exception( 'Not implemented yet' );
		$wallet		= new \Stripe\Wallet();
		$wallet->Currency		= $currency;
		$wallet->Owners			= array( $userId );
		$wallet->Description	= $currency.' Wallet';
		return $this->provider->Wallets->Create( $wallet );
	}

	public function getBankAccount( $userId, $bankAccountId ){
		$user	= $this->getUser( $userId );
		return $user->sources->retrieve( $bankAccountId );
	}

	public function getBankAccounts( $userId ){
		$user	= $this->getUser( $userId );
		return $user->sources->all( array( 'object' => 'bank_account' ) );
	}

	public function getCard( $userId, $cardId ){
		$cacheKey	= 'stripe_user_'.$userId.'_card_'.$cardId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$user	= $this->getUser( $userId );
			$item	= $user->sources->retrieve( $cardId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getCardById( $cardId ){
		throw new Exception( 'Not supported anymore' );
		$cacheKey	= 'stripe_card_'.$cardId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Cards->Get( $cardId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getClient(){
		throw new Exception( 'Not implemented yet' );
		return $this->provider->Clients->Get();
	}

	public function getClientWallet( $fundsType, $currency ){
		throw new Exception( 'Not implemented yet' );
		return $this->provider->Clients->GetWallet( $fundsType, $currency );
	}

	/**
	 *	@todo			implement
	 */
	public function getDefaultCurrency( $userId = NULL ){
		$currency	= 'EUR';
		if( $userId ){

		}
	}

	public function getEventResource( $eventType, $resourceId, $force = FALSE ){
		if( preg_match( '@^PAYIN_NORMAL_@', $eventType ) )
			$method	= 'getPayin';
		else if( preg_match( '@^PAYOUT_NORMAL_@', $eventType ) )
			$method	= 'getPayout';
		else if( preg_match( '@^TRANSFER_NORMAL_@', $eventType ) )
			$method	= 'getTransfer';
		else
			throw new RuntimeException( 'No implementation found for event type '.$eventType );

		if( !method_exists( $this, $method ) )
			throw new BadMethodCallException( 'Method "'.$method.'" is not existing' );
		if( $force )
			$this->skipCacheOnNextRequest( TRUE );
		$factory	= new Alg_Object_MethodFactory();
		return $factory->call( $this, $method, array( $resourceId ) );
	}

	public function getHook( $hookId ){
		$cacheKey	= 'stripe_hook_'.$hookId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Hooks->Get( $hookId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getHooks( $refresh = FALSE ){
		$cacheKey	= 'stripe_hooks';
		$refresh ? $this->skipCacheOnNextRequest( TRUE ) : NULL;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= $this->provider->Hooks->GetAll();
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getPayin( $payInId ){
		throw new Exception( 'Not implemented yet' );
		return $this->provider->PayIns->Get( $payInId );
	}

	public function getPayout( $payOutId ){
		throw new Exception( 'Not implemented yet' );
		return $this->provider->PayOuts->Get( $payOutId );
	}

	public function getTransfer( $transferId ){
		throw new Exception( 'Not implemented yet' );
		return $this->provider->Transfers->Get( $transferId );
	}

	public function getUser( $userId ){
		$cacheKey	= 'stripe_user_'.$userId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= \Stripe\Customer::retrieve( $userId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserBankAccount( $userId, $bankAccountId ){
		$cacheKey	= 'stripe_user_'.$userId.'_bankaccount_'.$bankAccountId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$user	= $this->getUser( $userId );
			$item	= $user->sources->retrieve( $bankAccountId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserBankAccounts( $userId ){
		$cacheKey	= 'stripe_user_'.$userId.'_bankaccounts';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$user	= $this->getUser( $userId );
			$items	= $user->sources->all( array( 'object' => 'bank_account' ) )->data;
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getUserCards( $userId, $conditions = array(), $orders = array(), $limits = array() ){
		throw new Exception( 'Not implemented yet' );
		$pagination	= new \Stripe\Pagination();
		$sorting	= new \Stripe\Sorting();
		if( !$orders )
			$sorting->AddField( 'CreationDate', 'DESC' );
		else{
			foreach( $orders as $orderKey => $orderValue )
				$sorting->AddField( $orderKey, strtoupper( $orderValue ) );
		}
		$cacheKey	= 'stripe_user_'.$userId.'_cards';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= $this->provider->Users->GetCards( $userId, $pagination, $sorting );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getUserWallet( $userId, $walletId ){
		throw new Exception( 'Not supported' );
		$cacheKey	= 'stripe_user_'.$userId.'_wallet_'.$walletId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Wallets->Get( $walletId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getClientWallets(){
		throw new Exception( 'Not supported' );
		return $this->provider->Clients->GetWallets();
	}

	public function getUserWallets( $userId, $orders = array(), $limits = array() ){
		throw new Exception( 'Not supported' );
		$pagination	= new \Stripe\Pagination();
		$sorting	= new \Stripe\Sorting();
		if( !$orders )
			$sorting->AddField( 'CreationDate', 'DESC' );
		else{
			foreach( $orders as $orderKey => $orderValue )
				$sorting->AddField( $orderKey, strtoupper( $orderValue ) );
		}
		return $this->provider->Users->GetWallets( $userId, $pagination, $sorting );
	}

	public function getUserWalletsByCurrency( $userId, $currency, $force = FALSE ){
		throw new Exception( 'Not supported' );
		$pagination	= new \Stripe\Pagination();
		$sorting	= new \Stripe\Sorting();
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

	public function setUserIdForLocalUserId( $userId, $localUserId ){
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'stripe',
		) );
		if( $relation ){
			$modelAccount->edit( $relation->userPaymentAccountId, array(
				'paymentAccountId'	=> $userId,
//				'modifiedAt'		=> time(),
			) );
		}
		else{
			$modelAccount->add( array(
				'userId'	=> $localUserId,
				'paymentAccountId'	=> $userId,
				'provider'	=> 'stripe',
				'createdAt'	=> time(),
			) );
		}
	}

	public function getUserIdFromLocalUserId( $localUserId, $strict = TRUE ){
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'stripe',
		) );
		if( !$relation && $strict )
			throw new RuntimeException( 'No payment account available' );
		if( !$relation )
			return NULL;
		return $relation->paymentAccountId;
	}

	/**
	 *	@todo		extend cache key by filters
	 */
	public function getWalletTransactions( $walletId, $orders = array(), $limits = array() ){
		throw new Exception( 'Not implemented yet' );
		$cacheKey	= 'stripe_wallet_'.$walletId.'_transactions';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$pagination	= $this->provider->getDefaultPagination();
			$sorting	= $this->provider->getDefaultSorting();
	//		$sorting->AddField( 'CreationDate', 'ASC' );
			$filter		= new \Stripe\FilterTransactions();
			$items		= $this->provider->Wallets->GetTransactions( $walletId, $pagination, $filter, $sorting );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function hasPaymentAccount( $localUserId ){
		throw new Exception( 'Not implemented yet' );
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->countByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'stripe',
		) );
		return $relation;
	}

	public function setClientLogo( $imageContentBase64 ){
		throw new Exception( 'Not implemented yet' );
		$ClientLogoUpload = new \Stripe\ClientLogoUpload();
		$ClientLogoUpload->File = $imageContentBase64;
		return $this->provider->Clients->UploadLogo( $ClientLogoUpload );
	}

	public function setHook( $id, $eventType, $path, $status = NULL, $tag = NULL ){
		throw new Exception( 'Not implemented yet' );
		if( $id > 0 ){
			$hook			= $this->provider->Hooks->Get( $id );
			if( $status !== NULL )
				$hook->Status	= $status ? 'ENABLED' : 'DISABLED';
			if( $tag !== NULL )
				$hook->Tag	= trim( $tag );
			$hook->Url			= $this->baseUrl.trim( $path );
			$this->uncache( 'hooks' );
			$this->uncache( 'hook_'.$id );
			return $this->provider->Hooks->Update( $hook );
		}
		else{
			$hook				= new \Stripe\Hook;
			$hook->EventType	= $eventType;
			$hook->Url			= $this->baseUrl.trim( $path );
			if( $tag !== NULL )
				$hook->Tag	= trim( $tag );
			return $this->provider->Hooks->Create( $hook );
		}
	}

	public function skipCacheOnNextRequest( $skip ){
		$this->skipCacheOnNextRequest	= (bool) $skip;
	}

	public function transfer( $sourceUserId, $targetUserId, $sourceWalletId, $targetWalletId, $currency, $amount, $fees, $tag = NULL ){
		throw new Exception( 'Not implemented yet' );
		$transfer = new \Stripe\Transfer();
		$transfer->Tag = $tag;
		$transfer->AuthorId = $sourceUserId;
		$transfer->CreditedUserId = $targetUserId;
		$transfer->DebitedFunds = new \Stripe\Money();
		$transfer->DebitedFunds->Currency = $currency;
		$transfer->DebitedFunds->Amount = $amount;
		$transfer->Fees = new \Stripe\Money();
		$transfer->Fees->Currency = $currency;
		$transfer->Fees->Amount = $fees;
		$transfer->DebitedWalletId = $sourceWalletId;
		$transfer->CreditedWalletId = $targetWalletId;
		$result		= $this->provider->Transfers->Create( $transfer );
		return $result;
	}

	public function uncache( $key ){
		$this->cache->remove( 'stripe_'.$key );
	}

	public function updateClient( $data ){
		throw new Exception( 'Not implemented yet' );
		$client	= $this->getClient();
		$copy	= clone( $client );
		$map	= array(
			'PrimaryButtonColour'	=> 'colorButton',
			'PrimaryThemeColour'	=> 'colorTheme',
			'TaxNumber'				=> 'taxNumber',
			'PlatformType'			=> 'platformType',
			'PlatformDescription'	=> 'platformDescription',
			'PlatformURL'			=> 'platformUrl',
		);
		foreach( $map as $key => $value )
			if( isset( $data[$value] ) )
				$copy->$key	= $data[$value];

		if( isset( $data['headquarter'] ) && is_array( $data['headquarter'] ) ){
			$map	= array(
				'AddressLine1'	=> 'address',
				'City'			=> 'city',
				'Region'		=> 'region',
				'PostalCode'	=> 'postcode',
				'Country'		=> 'country',
			);
			foreach( $map as $key => $value )
				if( isset( $data['headquarter'][$value] ) )
					$copy->HeadquartersAddress->$key	= $data['headquarter'][$value];
		}

		if( isset( $data['emails'] ) && is_array( $data['emails'] ) ){
			$map	= array(
				'AdminEmails'	=> 'admin',
				'TechEmails'	=> 'tech',
				'BillingEmails'	=> 'billing',
			);
			foreach( $map as $key => $value )
				if( isset( $data['emails'][$value] ) )
					$copy->$key	= explode( "\n", $data['emails'][$value] );
		}
		if( $copy !== $client )
			return $this->provider->Clients->Update( $copy );
		return NULL;
	}

	public function updateUser( $user ){
		throw new Exception( 'Not implemented yet' );
		$this->uncache( 'user_'.$user->Id );
		return $this->provider->Users->Update( $user );
	}

	public function updateUserWallet( $userId, $walletId, $description, $tag = NULL ){
		throw new Exception( 'Not implemented yet' );
		$wallet		= $this->getUserWallet( $userId, $walletId );
		$wallet->Description = $description;
		if( $tag !== NULL )
			$wallet->Tag = $tag;
		$this->uncache( 'user_'.$userId.'_wallets' );
		$this->uncache( 'user_'.$userId.'_wallet_'.$walletId );
		return $this->provider->Wallets->Update( $wallet );
	}

/*	not working - only possible update: Active = FALSE
	public function updateUserBankAccount( $userId, $bankAccountId, $tag ){
		$bankAccount	= $this->getUserBankAccount( $userId, $bankAccountId );
		$bankAccount->Tag = $tag;
		$this->cache->remove( 'user_'.$user->Id );
		return $this->provider->Users->UpdateBankAccount( $userId, $bankAccount );
	}*/

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