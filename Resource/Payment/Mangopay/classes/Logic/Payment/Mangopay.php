<?php

use CeusMedia\Common\Alg\Obj\MethodFactory;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Payment_Mangopay extends Logic
{
	protected $cache;
	protected $provider;
	protected $skipCacheOnNextRequest;
	protected $baseUrl;

	public static $typeCurrencies	= array(
		'CB_VISA_MASTERCARD'	=> [],
		'MAESTRO'				=> ['EUR'],
		'DINERS'				=> ['EUR'],
		'GIROPAY'				=> ['EUR'],
		'IDEAL'					=> ['EUR'],
		'PAYLIB'				=> ['EUR'],
		'SOFORT'				=> ['EUR'],
		'BCMC'					=> ['EUR'],
		'P24'					=> ['PLN'],
		'BANKWIRE'				=> [],
	);

	public function deactivateBankAccount( $userId, $bankAccountId )
	{
		$bankAccount	= $this->getBankAccount( $userId, $bankAccountId );
		$bankAccount->Active = FALSE;
		$result	= $this->provider->Users->UpdateBankAccount( $userId, $bankAccount );
		$this->uncache( 'user_'.$userId.'_bankaccounts' );
		$this->uncache( 'user_'.$userId.'_bankaccount_'.$bankAccountId );
	}

	/**
	 *	@todo		implement type
	 */
	public function calculateFeesForPayIn( $price, $currency, $type )
	{
		switch( $type ){
			case 'CB_VISA_MASTERCARD':
				if( $currency === "EUR" )
					return $price * 0.018 + 18;
				else if( $currency === "GBP" )
					return $price * 0.019 + 20;
				throw new RangeException( sprintf( 'Currency %s is not supported', $currency ) );
			case 'MAESTRO':
			case 'DINERS':
			case 'BCMC':
				if( $currency === "EUR" )
					return $price * 0.025 + 25;
				else if( $currency === "GBP" )
					return $price * 0.025 + 20;
				throw new RangeException( sprintf( 'Currency %s is not supported', $currency ) );
			case 'GIROPAY':
			case 'SOFORT':
				if( $currency === "EUR" )
					return $price * 0.018 + 30;
				throw new RangeException( sprintf( 'Currency %s is not supported', $currency ) );
			case 'IDEAL':
				if( $currency === "EUR" )
					return 80;
				throw new RangeException( sprintf( 'Currency %s is not supported', $currency ) );
			case 'BANKWIRE':
				return $price * 0.005;
			default:
				throw new RangeException( sprintf( 'Payin type %s is not supported', $type ) );
		}
	}

	public function checkUser( $userId )
	{
		return $this->getUser( $userId );
	}

	public function createAddress( $street, $postcode, $city, $country, $region = NULL )
	{
		$address = new \MangoPay\Address();
		$address->AddressLine1	= $street;
		$address->PostalCode	= $postcode;
		$address->City			= $city;
		$address->Country		= $country;
		if( $region )
			$address->Region		= $region;
		return $address;
	}

	public function createBankAccount( $userId, $iban, $bic, $title, $address = NULL )
	{
		$user	= $this->getUser( $userId );
		$bankAccount = new \MangoPay\BankAccount();
		$bankAccount->Type			= "IBAN";
		$bankAccount->Details		= new \MangoPay\BankAccountDetailsIBAN();
		$bankAccount->Details->IBAN	= trim( str_replace( ' ', '', $iban ) );
		$bankAccount->Details->BIC	= trim( $bic );
		$bankAccount->OwnerName		= $title;
		if( $address )
			$bankAccount->OwnerAddress	= $address;
		else if( $user instanceof \MangoPay\UserNatural )
			$bankAccount->OwnerAddress	= $user->Address;
		else if( $user instanceof \MangoPay\UserLegal )
			$bankAccount->OwnerAddress	= $user->LegalRepresentativeAddress;
		$item	= $this->provider->Users->CreateBankAccount( $userId, $bankAccount );
		$this->uncache( 'user_'.$userId.'_bankaccounts' );
		return $item;
	}

	public function createMandate( $bankAccountId, $returnUrl )
	{
		$mandate 	= new \MangoPay\Mandate();
		$mandate->BankAccountId	= $bankAccountId;
		$mandate->Culture		= "EN";
		$mandate->ReturnUrl		= $returnUrl;
		return $this->provider->Mandates->Create( $mandate );
	}

	public function getUserMandates( $userId )
	{
		$cacheKey	= 'mangopay_user_'.$userId.'_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= 	$this->provider->Users->GetMandates( $userId );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getBankAccountMandates( $userId, $bankAccountId )
	{
		$cacheKey	= 'mangopay_user_'.$userId.'_bankaccount_'.$bankAccountId.'_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= 	$this->provider->Users->GetMandatesForBankAccount( $userId, $bankAccountId );
print_m( $items );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getMandates()
	{
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
	public function createPayInFromBankAccount( $userId, $walletId, $bankAccountId, $currency, $amount )
	{
//		$bankAccount	= $this->getBankAccount( $userId, $bankAccountId );

		$payIn		= new \MangoPay\PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new \MangoPay\Money();
		$payIn->Fees				= new \MangoPay\Money();

		$payIn->Fees->Amount	= $this->calculateFeesForPayIn( $amount );
		$payIn->Fees->Currency	= $currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $currency;

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

	/**
	 *	@todo		test (not tested since no mandates allowed, yet)
	 */
	public function createPayInFromBankAccountViaDirectDebit( $userId, $mandateId, $currency, $amount )
	{
		$payIn	= new \MangoPay\PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new \MangoPay\Money();
		$payIn->Fees				= new \MangoPay\Money();

		$payIn->Fees->Amount	= $this->calculateFeesForPayIn( $amount );
		$payIn->Fees->Currency	= $currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $currency;

		$payIn->PaymentDetails	= new \MangoPay\PayInPaymentDetailsDirectDebitDirect();
		$payIn->PaymentDetails->MandateId	=

		// execution type as DIRECT
		$payIn->ExecutionDetails	= new \MangoPay\PayInExecutionDetailsDirect();

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createPayInFromCard( $userId, $walletId, $cardId, $amount, $secureModeReturnUrl )
	{
		$card	= $this->getCardById( $cardId );

		$payIn		= new \MangoPay\PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new \MangoPay\Money();
		$payIn->Fees				= new \MangoPay\Money();

	//	$amount	= $this->checkAmount( $amount, $this->currency );								//  @todo handle amount format and sanity

		$payIn->Fees->Amount	= 0;
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

	public function createBankPayInViaWeb( $type, $userId, $walletId, $currency, $amount, $returnUrl )
	{
		$user	= $this->checkUser( $userId );
		$payIn	= new \MangoPay\PayIn();
		$payIn->CreditedWalletId	= $walletId;
		$payIn->AuthorId			= $userId;
		$payIn->PaymentDetails		= new \MangoPay\PayInPaymentDetailsDirectDebit();
		$payIn->DirectDebitType		= $type;
		$payIn->PaymentDetails->DebitedFunds			= new \MangoPay\Money();
		$payIn->PaymentDetails->DebitedFunds->Amount	= $amount;
		$payIn->PaymentDetails->DebitedFunds->Currency	= $currency;
		$payIn->PaymentDetails->Fees					= new \MangoPay\Money();
		$payIn->PaymentDetails->Fees->Amount			= 0;
		$payIn->PaymentDetails->Fees->Currency			= $currency;
		$payIn->ExecutionDetails			= new \MangoPay\PayInExecutionDetailsWeb();
		$payIn->ExecutionDetails->ReturnURL	= $returnUrl;
		$payIn->ExecutionDetails->Culture	= strtoupper( $user->Nationality );
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createCardPayInViaWeb( $userId, $walletId, $cardType, $currency, $amount, $returnUrl )
	{
		$user	= $this->checkUser( $userId );
		$payIn	= new \MangoPay\PayIn();
		$payIn->CreditedWalletId			= $walletId;
		$payIn->AuthorId					= $userId;
		$payIn->PaymentType					= "CARD";
		$payIn->ExecutionType				= "WEB";
		$payIn->PaymentDetails				= new \MangoPay\PayInPaymentDetailsCard();
		$payIn->PaymentDetails->CardType	= $cardType;
		$payIn->DebitedFunds				= new \MangoPay\Money();
		$payIn->DebitedFunds->Currency		= strtoupper( $currency );
		$payIn->DebitedFunds->Amount		= $amount;
		$payIn->Fees						= new \MangoPay\Money();
		$payIn->Fees->Currency				= strtoupper( $currency );
		$payIn->Fees->Amount				= 0;
		$payIn->ExecutionDetails			= new \MangoPay\PayInExecutionDetailsWeb();
		$payIn->ExecutionDetails->ReturnURL	= $returnUrl;
		$payIn->ExecutionDetails->Culture	= strtoupper( $user->Nationality );
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createLegalUserFromLocalUser( $localUserId, $companyData, $representativeData )
	{
		$modelUser		= new Model_User( $this->env );
		$modelAddress	= new Model_Address( $this->env );
		$user			= $modelUser->get( $localUserId );
		$address		= $modelAddress->get( array(
			'relationType'	=> 'user',
			'relationId'	=> $this->localUserId,
			'type'			=> Model_Address::TYPE_BILLING,
		) );

		$user = new \MangoPay\UserLegal();
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
			$user->LegalRepresentativeAddress = new \MangoPay\Address();
			$user->LegalRepresentativeAddress->AddressLine1	= $address->street;
			$user->LegalRepresentativeAddress->City			= $address->city;
			$user->LegalRepresentativeAddress->Region		= $address->region;
			$user->LegalRepresentativeAddress->PostalCode	= $address->postcode;
			$user->LegalRepresentativeAddress->Country		= $address->country;
			$user->HeadquartersAddress = new \MangoPay\Address();
			$user->HeadquartersAddress->AddressLine1	= $address->street;
			$user->HeadquartersAddress->City			= $address->city;
			$user->HeadquartersAddress->Region			= $address->region;
			$user->HeadquartersAddress->PostalCode		= $address->postcode;
			$user->HeadquartersAddress->Country			= $address->country;
		}
		$user = $this->provider->Users->Create( $user );
	}

	public function createLegalUser( $data )
	{
		$user = new \MangoPay\UserLegal();
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
		$user->LegalRepresentativeAddress = new \MangoPay\Address();
		$user->LegalRepresentativeAddress->AddressLine1	= $data['representative']['address'];
		$user->LegalRepresentativeAddress->City			= $data['representative']['city'];
		$user->LegalRepresentativeAddress->Region		= $data['representative']['region'];
		$user->LegalRepresentativeAddress->PostalCode	= $data['representative']['postcode'];
		$user->LegalRepresentativeAddress->Country		= $data['representative']['country'];
		$user->HeadquartersAddress = new \MangoPay\Address();
		$user->HeadquartersAddress->AddressLine1	= $data['headquarter']['address'];
		$user->HeadquartersAddress->City			= $data['headquarter']['city'];
		$user->HeadquartersAddress->Region			= $data['headquarter']['region'];
		$user->HeadquartersAddress->PostalCode		= $data['headquarter']['postcode'];
		$user->HeadquartersAddress->Country			= $data['headquarter']['country'];
//print_m( $user );die;
		return $this->provider->Users->Create( $user );
	}

	public function updateLegalUser( $userId, $data )
	{
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
		$user->LegalRepresentativeAddress = new \MangoPay\Address();
		$user->LegalRepresentativeAddress->AddressLine1	= $data['representative']['address'];
		$user->LegalRepresentativeAddress->City			= $data['representative']['city'];
		$user->LegalRepresentativeAddress->Region		= $data['representative']['region'];
		$user->LegalRepresentativeAddress->PostalCode	= $data['representative']['postcode'];
		$user->LegalRepresentativeAddress->Country		= $data['representative']['country'];
		$user->HeadquartersAddress = new \MangoPay\Address();
		$user->HeadquartersAddress->AddressLine1	= $data['headquarter']['address'];
		$user->HeadquartersAddress->City			= $data['headquarter']['city'];
		$user->HeadquartersAddress->Region			= $data['headquarter']['region'];
		$user->HeadquartersAddress->PostalCode		= $data['headquarter']['postcode'];
		$user->HeadquartersAddress->Country			= $data['headquarter']['country'];
//print_m( $user );die;
		return $this->provider->Users->Update( $user );
	}

	public function createNaturalUserFromLocalUser( $localUserId )
	{
		$modelUser		= new Model_User( $this->env );
		$modelAddress	= new Model_Address( $this->env );
		$user			= $modelUser->get( $localUserId );
		$address		= $modelAddress->get( array(
			'relationType'	=> 'user',
			'relationId'	=> $this->localUserId,
			'type'			=> Model_Address::TYPE_BILLING,
		) );

		$user	= new \MangoPay\UserNatural();
		$user->PersonType			= "NATURAL";
		$user->FirstName				= $user->firstname;
		$user->LastName				= $user->surname;
		$user->Birthday				= 0;
		$user->Nationality			= $user->country;
		$user->CountryOfResidence	= $user->country;
		$user->Email					= $user->email;
		$user->Address 				= new \MangoPay\Address();
		$user->Address->AddressLine1	= $user->street.' '.$user->number;
		$user->Address->City			= $user->city;
		$user->Address->PostalCode	= $user->postcode;
		$user->Address->Country		= $user->country;
		if( $address ){
			$user->Address->AddressLine1	= $address->street;
			$user->Address->City			= $address->city;
			$user->Address->PostalCode	= $address->postcode;
			$user->Address->Country		= $address->country;
		}
		$user	= $this->provider->Users->Create( $user );
		$this->setUserIdForLocalUserId( $user->Id, $localUserId );
		return $user;
	}

	public function createUserWallet( $userId, $currency )
	{
		$wallet		= new \MangoPay\Wallet();
		$wallet->Currency		= $currency;
		$wallet->Owners			= [$userId];
		$wallet->Description	= $currency.' Wallet';
		return $this->provider->Wallets->Create( $wallet );
	}

	public function getBankAccount( $userId, $bankAccountId )
	{
		return $this->getUserBankAccount( $userId, $bankAccountId );
	}

	public function getBankAccounts( $userId )
	{
		return $this->getUserBankAccounts( $userId );
	}

	public function getCardById( $cardId )
	{
		$cacheKey	= 'mangopay_card_'.$cardId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Cards->Get( $cardId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getClient()
	{
		return $this->provider->Clients->Get();
	}

	public function getClientWallet( $fundsType, $currency )
	{
		return $this->provider->Clients->GetWallet( $fundsType, $currency );
	}

	/**
	 *	@todo			implement
	 */
	public function getDefaultCurrency( $userId = NULL )
	{
		$currency	= 'EUR';
		if( $userId ){

		}
	}

	public function getEventResource( $eventType, $resourceId, $force = FALSE )
	{
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
		$factory	= new MethodFactory();
		return $factory->call( $this, $method, [$resourceId] );
	}

	public function getHook( $hookId )
	{
		$cacheKey	= 'mangopay_hook_'.$hookId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Hooks->Get( $hookId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getHooks( $refresh = FALSE )
	{
		$cacheKey	= 'mangopay_hooks';
		$refresh ? $this->skipCacheOnNextRequest( TRUE ) : NULL;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= $this->provider->Hooks->GetAll();
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getPayin( $payInId )
	{
		return $this->provider->PayIns->Get( $payInId );
	}

	public function getPayout( $payOutId )
	{
		return $this->provider->PayOuts->Get( $payOutId );
	}

	public function getTransfer( $transferId )
	{
		return $this->provider->Transfers->Get( $transferId );
	}

	public function getUser( $userId )
	{
		$cacheKey	= 'mangopay_user_'.$userId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Users->Get( $userId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserBankAccount( $userId, $bankAccountId )
	{
		$cacheKey	= 'mangopay_user_'.$userId.'_bankaccount_'.$bankAccountId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Users->GetBankAccount( $userId, $bankAccountId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserBankAccounts( $userId )
	{
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

	public function getUserCards( $userId, $conditions = [], $orders = [], $limits = [] )
	{
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

	public function getUserWallet( $userId, $walletId )
	{
		$cacheKey	= 'mangopay_user_'.$userId.'_wallet_'.$walletId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Wallets->Get( $walletId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getClientWallets()
	{
		return $this->provider->Clients->GetWallets();
	}

	public function getUserWallets( $userId, $orders = [], $limits = [] )
	{
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

	public function getUserWalletsByCurrency( $userId, $currency, $force = FALSE )
	{
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$all	= $this->provider->Users->GetWallets( $userId, $pagination, $sorting );
		$list	= [];
		foreach( $all as $wallet )
			if( $wallet->Currency === $currency )
				$list[]	= $wallet;

		if( !$list && $force ){
			$wallet	= $this->createUserWallet( $userId, $currency );
			$list[]	= $wallet;
		}
		return $list;
	}

	public function setUserIdForLocalUserId( $userId, $localUserId )
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
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
				'provider'	=> 'mangopay',
				'createdAt'	=> time(),
			) );
		}
	}

	public function getUserIdFromLocalUserId( $localUserId, $strict = TRUE )
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
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
	public function getWalletTransactions( $walletId, $orders = [], $limits = [] )
	{
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

	public function hasPaymentAccount( $localUserId )
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->countByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
		) );
		return $relation;
	}

	public function setClientLogo( $imageContentBase64 )
	{
		$ClientLogoUpload = new \MangoPay\ClientLogoUpload();
		$ClientLogoUpload->File = $imageContentBase64;
		return $this->provider->Clients->UploadLogo( $ClientLogoUpload );
	}

	public function setHook( $id, $eventType, $path, $status = NULL, $tag = NULL )
	{
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
			$hook				= new \MangoPay\Hook;
			$hook->EventType	= $eventType;
			$hook->Url			= $this->baseUrl.trim( $path );
			if( $tag !== NULL )
				$hook->Tag	= trim( $tag );
			return $this->provider->Hooks->Create( $hook );
		}
	}

	public function skipCacheOnNextRequest( $skip )
	{
		$this->skipCacheOnNextRequest	= (bool) $skip;
	}

	public function transfer( $sourceUserId, $targetUserId, $sourceWalletId, $targetWalletId, $currency, $amount, $fees, $tag = NULL )
	{
		$transfer = new \MangoPay\Transfer();
		$transfer->Tag = $tag;
		$transfer->AuthorId = $sourceUserId;
		$transfer->CreditedUserId = $targetUserId;
		$transfer->DebitedFunds = new \MangoPay\Money();
		$transfer->DebitedFunds->Currency = $currency;
		$transfer->DebitedFunds->Amount = $amount;
		$transfer->Fees = new \MangoPay\Money();
		$transfer->Fees->Currency = $currency;
		$transfer->Fees->Amount = $fees;
		$transfer->DebitedWalletId = $sourceWalletId;
		$transfer->CreditedWalletId = $targetWalletId;
		$result		= $this->provider->Transfers->Create( $transfer );
		return $result;
	}

	public function uncache( $key )
	{
		$this->cache->remove( 'mangopay_'.$key );
	}

	public function updateClient( $data )
	{
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

	public function updateUser( $user )
	{
		$this->uncache( 'user_'.$user->Id );
		return $this->provider->Users->Update( $user );
	}

	public function updateUserWallet( $userId, $walletId, $description, $tag = NULL )
	{
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
	public static function validateCardNumber( $number, $provider )
	{
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

	protected function __onInit()
	{
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
//		print_m( $this->moduleConfig->getAll() );die;
		$this->cache		= $this->env->getCache();
		$this->provider		= Resource_Mangopay::getInstance( $this->env );
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
	protected function applyPossibleCacheSkip( $cacheKey )
	{
		if( $this->skipCacheOnNextRequest ){
			$this->cache->remove( $cacheKey );
			$this->skipCacheOnNextRequest	= FALSE;
		}
	}

	protected function checkIsOwnCard( $cardId )
	{
		$card	= $this->checkCard( $cardId );
	//	@todo check card against user cards
		return $card;
	}
}
