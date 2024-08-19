<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\MethodFactory;
use CeusMedia\HydrogenFramework\Logic;
use MangoPay\Address;
use MangoPay\BankAccount;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\ClientLogoUpload;
use MangoPay\FilterTransactions;
use MangoPay\Hook;
use MangoPay\Mandate;
use MangoPay\Money;
use MangoPay\Pagination;
use MangoPay\PayIn;
use MangoPay\PayInExecutionDetailsDirect;
use MangoPay\PayInExecutionDetailsWeb;
use MangoPay\PayInPaymentDetailsBankWire;
use MangoPay\PayInPaymentDetailsCard;
use MangoPay\PayInPaymentDetailsDirectDebit;
use MangoPay\PayInPaymentDetailsDirectDebitDirect;
use MangoPay\Sorting;
use MangoPay\Transfer;
use MangoPay\UserLegal;
use MangoPay\UserNatural;
use MangoPay\Wallet;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class Logic_Payment_Mangopay extends Logic
{
	protected SimpleCacheInterface $cache;
	protected Resource_Mangopay $provider;
	protected bool $skipCacheOnNextRequest	= FALSE;
	protected string $baseUrl;
	protected Dictionary $moduleConfig;

	public static array $typeCurrencies	= [
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
	];

	public function deactivateBankAccount( int|string $userId, int|string $bankAccountId ): void
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
	public function calculateFeesForPayIn( $price, $currency, $type ): float|int
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

	public function checkUser( int|string $userId )
	{
		return $this->getUser( $userId );
	}

	public function createAddress( string $street, $postcode, string $city, string $country, ?string $region = NULL ): Address
	{
		$address = new Address();
		$address->AddressLine1	= $street;
		$address->PostalCode	= $postcode;
		$address->City			= $city;
		$address->Country		= $country;
		if( $region )
			$address->Region		= $region;
		return $address;
	}

	public function createBankAccount( int|string $userId, string $iban, string $bic, string $title, $address = NULL )
	{
		$user	= $this->getUser( $userId );
		$bankAccount = new BankAccount();
		$bankAccount->Type			= "IBAN";
		$bankAccount->Details		= new BankAccountDetailsIBAN();
		$bankAccount->Details->IBAN	= trim( str_replace( ' ', '', $iban ) );
		$bankAccount->Details->BIC	= trim( $bic );
		$bankAccount->OwnerName		= $title;
		if( $address )
			$bankAccount->OwnerAddress	= $address;
		else if( $user instanceof UserNatural )
			$bankAccount->OwnerAddress	= $user->Address;
		else if( $user instanceof UserLegal )
			$bankAccount->OwnerAddress	= $user->LegalRepresentativeAddress;
		$item	= $this->provider->Users->CreateBankAccount( $userId, $bankAccount );
		$this->uncache( 'user_'.$userId.'_bankaccounts' );
		return $item;
	}

	public function createMandate( int|string $bankAccountId, $returnUrl )
	{
		$mandate 	= new Mandate();
		$mandate->BankAccountId	= $bankAccountId;
		$mandate->Culture		= "EN";
		$mandate->ReturnUrl		= $returnUrl;
		return $this->provider->Mandates->Create( $mandate );
	}

	public function getUserMandates( int|string $userId )
	{
		$cacheKey	= 'mangopay_user_'.$userId.'_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$items	= 	$this->provider->Users->GetMandates( $userId );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getBankAccountMandates( int|string $userId, int|string $bankAccountId )
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

	/**
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getMandates(): array
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
	public function createPayInFromBankAccount( int|string $userId, int|string $walletId, int|string $bankAccountId, $currency, $amount )
	{
//		$bankAccount	= $this->getBankAccount( $userId, $bankAccountId );

		$payIn		= new PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new Money();
		$payIn->Fees				= new Money();

		$payIn->Fees->Amount	= $this->calculateFeesForPayIn( $amount );
		$payIn->Fees->Currency	= $currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $currency;

		// payment type as BANKWIRE
		$payIn->PaymentDetails = new PayInPaymentDetailsBankWire();
		$payIn->PaymentDetails->DeclaredDebitedFunds	= $payIn->DebitedFunds;
		$payIn->PaymentDetails->DeclaredFees			= $payIn->Fees;
/*		$payIn->PaymentDetails->BankAccount				= $bankAccount;
		$payIn->PaymentDetails->WireReference			= "BankWire PayIn 1";
*/
		// execution type as DIRECT
		$payIn->ExecutionDetails	= new PayInExecutionDetailsDirect();

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	/**
	 *	@todo		test (not tested since no mandates allowed, yet)
	 */
	public function createPayInFromBankAccountViaDirectDebit( int|string $userId, $mandateId, $currency, $amount )
	{
		$payIn	= new PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new Money();
		$payIn->Fees				= new Money();

		$payIn->Fees->Amount	= $this->calculateFeesForPayIn( $amount );
		$payIn->Fees->Currency	= $currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $currency;

		$payIn->PaymentDetails	= new PayInPaymentDetailsDirectDebitDirect();
		$payIn->PaymentDetails->MandateId	=

		// execution type as DIRECT
		$payIn->ExecutionDetails	= new PayInExecutionDetailsDirect();

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createPayInFromCard( int|string $userId, int|string $walletId, int|string $cardId, $amount, $secureModeReturnUrl )
	{
		$card	= $this->getCardById( $cardId );

		$payIn		= new PayIn();
		$payIn->AuthorId			= $userId;
		$payIn->CreditedWalletId	= $walletId;
		$payIn->DebitedFunds		= new Money();
		$payIn->Fees				= new Money();

	//	$amount	= $this->checkAmount( $amount, $this->currency );								//  @todo handle amount format and sanity

		$payIn->Fees->Amount	= 0;
		$payIn->Fees->Currency	= $card->Currency;

		$payIn->DebitedFunds->Amount	= $amount + $this->calculateFeesForPayIn( $amount );
		$payIn->DebitedFunds->Currency	= $card->Currency;

		// payment type as CARD
		$payIn->PaymentDetails = new PayInPaymentDetailsCard();
		$payIn->PaymentDetails->CardType	= $card->CardType;
		$payIn->PaymentDetails->CardId		= $card->Id;

		// execution type as DIRECT
		$payIn->ExecutionDetails	= new PayInExecutionDetailsDirect();
		$payIn->ExecutionDetails->SecureModeReturnURL = $secureModeReturnUrl;

		// create Pay-In
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createBankPayInViaWeb( $type, int|string $userId, int|string $walletId, $currency, $amount, $returnUrl )
	{
		$user	= $this->checkUser( $userId );
		$payIn	= new PayIn();
		$payIn->CreditedWalletId	= $walletId;
		$payIn->AuthorId			= $userId;
		$payIn->PaymentDetails		= new PayInPaymentDetailsDirectDebit();
		$payIn->DirectDebitType		= $type;
		$payIn->PaymentDetails->DebitedFunds			= new Money();
		$payIn->PaymentDetails->DebitedFunds->Amount	= $amount;
		$payIn->PaymentDetails->DebitedFunds->Currency	= $currency;
		$payIn->PaymentDetails->Fees					= new Money();
		$payIn->PaymentDetails->Fees->Amount			= 0;
		$payIn->PaymentDetails->Fees->Currency			= $currency;
		$payIn->ExecutionDetails			= new PayInExecutionDetailsWeb();
		$payIn->ExecutionDetails->ReturnURL	= $returnUrl;
		$payIn->ExecutionDetails->Culture	= strtoupper( $user->Nationality );
		return $this->provider->PayIns->Create( $payIn );
	}

	public function createCardPayInViaWeb( int|string $userId, int|string $walletId, $cardType, $currency, $amount, $returnUrl )
	{
		$user	= $this->checkUser( $userId );
		$payIn	= new PayIn();
		$payIn->CreditedWalletId			= $walletId;
		$payIn->AuthorId					= $userId;
		$payIn->PaymentType					= "CARD";
		$payIn->ExecutionType				= "WEB";
		$payIn->PaymentDetails				= new PayInPaymentDetailsCard();
		$payIn->PaymentDetails->CardType	= $cardType;
		$payIn->DebitedFunds				= new Money();
		$payIn->DebitedFunds->Currency		= strtoupper( $currency );
		$payIn->DebitedFunds->Amount		= $amount;
		$payIn->Fees						= new Money();
		$payIn->Fees->Currency				= strtoupper( $currency );
		$payIn->Fees->Amount				= 0;
		$payIn->ExecutionDetails			= new PayInExecutionDetailsWeb();
		$payIn->ExecutionDetails->ReturnURL	= $returnUrl;
		$payIn->ExecutionDetails->Culture	= strtoupper( $user->Nationality );
		return $this->provider->PayIns->Create( $payIn );
	}

	/**
	 *	@param		int|string		$localUserId
	 *	@param		$companyData
	 *	@param		$representativeData
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function createLegalUserFromLocalUser( int|string $localUserId, $companyData, $representativeData ): void
	{
		$modelUser		= new Model_User( $this->env );
		$modelAddress	= new Model_Address( $this->env );
		$user			= $modelUser->get( $localUserId );
		$address		= $modelAddress->get( [
			'relationType'	=> 'user',
			'relationId'	=> $this->localUserId,
			'type'			=> Model_Address::TYPE_BILLING,
		] );

		$user = new UserLegal();
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
			$user->LegalRepresentativeAddress = new Address();
			$user->LegalRepresentativeAddress->AddressLine1	= $address->street;
			$user->LegalRepresentativeAddress->City			= $address->city;
			$user->LegalRepresentativeAddress->Region		= $address->region;
			$user->LegalRepresentativeAddress->PostalCode	= $address->postcode;
			$user->LegalRepresentativeAddress->Country		= $address->country;
			$user->HeadquartersAddress = new Address();
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
		$user = new UserLegal();
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
		$user->LegalRepresentativeAddress = new Address();
		$user->LegalRepresentativeAddress->AddressLine1	= $data['representative']['address'];
		$user->LegalRepresentativeAddress->City			= $data['representative']['city'];
		$user->LegalRepresentativeAddress->Region		= $data['representative']['region'];
		$user->LegalRepresentativeAddress->PostalCode	= $data['representative']['postcode'];
		$user->LegalRepresentativeAddress->Country		= $data['representative']['country'];
		$user->HeadquartersAddress = new Address();
		$user->HeadquartersAddress->AddressLine1	= $data['headquarter']['address'];
		$user->HeadquartersAddress->City			= $data['headquarter']['city'];
		$user->HeadquartersAddress->Region			= $data['headquarter']['region'];
		$user->HeadquartersAddress->PostalCode		= $data['headquarter']['postcode'];
		$user->HeadquartersAddress->Country			= $data['headquarter']['country'];
//print_m( $user );die;
		return $this->provider->Users->Create( $user );
	}

	public function updateLegalUser( int|string $userId, $data )
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
		$user->LegalRepresentativeAddress = new Address();
		$user->LegalRepresentativeAddress->AddressLine1	= $data['representative']['address'];
		$user->LegalRepresentativeAddress->City			= $data['representative']['city'];
		$user->LegalRepresentativeAddress->Region		= $data['representative']['region'];
		$user->LegalRepresentativeAddress->PostalCode	= $data['representative']['postcode'];
		$user->LegalRepresentativeAddress->Country		= $data['representative']['country'];
		$user->HeadquartersAddress = new Address();
		$user->HeadquartersAddress->AddressLine1	= $data['headquarter']['address'];
		$user->HeadquartersAddress->City			= $data['headquarter']['city'];
		$user->HeadquartersAddress->Region			= $data['headquarter']['region'];
		$user->HeadquartersAddress->PostalCode		= $data['headquarter']['postcode'];
		$user->HeadquartersAddress->Country			= $data['headquarter']['country'];
//print_m( $user );die;
		return $this->provider->Users->Update( $user );
	}

	public function createNaturalUserFromLocalUser( int|string $localUserId )
	{
		$modelUser		= new Model_User( $this->env );
		$modelAddress	= new Model_Address( $this->env );
		$user			= $modelUser->get( $localUserId );
		$address		= $modelAddress->get( [
			'relationType'	=> 'user',
			'relationId'	=> $this->localUserId,
			'type'			=> Model_Address::TYPE_BILLING,
		] );

		$user	= new UserNatural();
		$user->PersonType			= "NATURAL";
		$user->FirstName			= $user->firstname;
		$user->LastName				= $user->surname;
		$user->Birthday				= 0;
		$user->Nationality			= $user->country;
		$user->CountryOfResidence	= $user->country;
		$user->Email				= $user->email;
		$user->Address 					= new Address();
		$user->Address->AddressLine1	= $user->street.' '.$user->number;
		$user->Address->City			= $user->city;
		$user->Address->PostalCode		= $user->postcode;
		$user->Address->Country			= $user->country;
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

	public function createUserWallet( int|string $userId, $currency )
	{
		$wallet		= new Wallet();
		$wallet->Currency		= $currency;
		$wallet->Owners			= [$userId];
		$wallet->Description	= $currency.' Wallet';
		return $this->provider->Wallets->Create( $wallet );
	}

	public function getBankAccount( int|string $userId, $bankAccountId )
	{
		return $this->getUserBankAccount( $userId, $bankAccountId );
	}

	public function getBankAccounts( int|string $userId )
	{
		return $this->getUserBankAccounts( $userId );
	}

	public function getCardById( int|string $cardId )
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
	 *	@param		int|string|NULL		$userId
	 *	@return		string
	 *	@todo		implement
	 */
	public function getDefaultCurrency( int|string|NULL $userId = NULL ): string
	{
		$currency	= 'EUR';
/*		if( $userId ){
		}*/
		return $currency;
	}

	/**
	 *	@param string $eventType
	 *	@param $resourceId
	 *	@param bool $force
	 *	@return mixed
	 *	@throws ReflectionException
	 */
	public function getEventResource( string $eventType, $resourceId, bool $force = FALSE )
	{
		if( str_starts_with( $eventType, 'PAYIN_NORMAL_' ) )
			$method	= 'getPayin';
		else if( str_starts_with( $eventType, 'PAYOUT_NORMAL_' ) )
			$method	= 'getPayout';
		else if( str_starts_with( $eventType, 'TRANSFER_NORMAL_' ) )
			$method	= 'getTransfer';
		else
			throw new RuntimeException( 'No implementation found for event type '.$eventType );

		if( !method_exists( $this, $method ) )
			throw new BadMethodCallException( 'Method "'.$method.'" is not existing' );
		if( $force )
			$this->skipCacheOnNextRequest( TRUE );
		$factory	= new MethodFactory();
		return $factory->call( $this, $method );
	}

	public function getHook( int|string $hookId )
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

	public function getPayin( int|string $payInId )
	{
		return $this->provider->PayIns->Get( $payInId );
	}

	public function getPayout( int|string $payOutId )
	{
		return $this->provider->PayOuts->Get( $payOutId );
	}

	public function getTransfer( int|string $transferId )
	{
		return $this->provider->Transfers->Get( $transferId );
	}

	public function getUser( int|string $userId )
	{
		$cacheKey	= 'mangopay_user_'.$userId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Users->Get( $userId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserBankAccount( int|string $userId, int|string $bankAccountId )
	{
		$cacheKey	= 'mangopay_user_'.$userId.'_bankaccount_'.$bankAccountId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= $this->provider->Users->GetBankAccount( $userId, $bankAccountId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function getUserBankAccounts( int|string $userId )
	{
		$cacheKey	= 'mangopay_user_'.$userId.'_bankaccounts';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$pagination	= new Pagination();
			$sorting	= new Sorting();
			$sorting->AddField( 'CreationDate', 'DESC' );
			$items		= $this->provider->Users->GetBankAccounts( $userId, $pagination, $sorting );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function getUserCards( int|string $userId, array $conditions = [], array $orders = [], array $limits = [] )
	{
		$pagination	= new Pagination();
		$sorting	= new Sorting();
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

	public function getUserWallet( int|string $userId, int|string $walletId )
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

	public function getUserWallets( int|string $userId, $orders = [], $limits = [] )
	{
		$pagination	= new Pagination();
		$sorting	= new Sorting();
		if( !$orders )
			$sorting->AddField( 'CreationDate', 'DESC' );
		else{
			foreach( $orders as $orderKey => $orderValue )
				$sorting->AddField( $orderKey, strtoupper( $orderValue ) );
		}
		return $this->provider->Users->GetWallets( $userId, $pagination, $sorting );
	}

	public function getUserWalletsByCurrency( int|string $userId, $currency, bool $force = FALSE )
	{
		$pagination	= new Pagination();
		$sorting	= new Sorting();
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

	public function setUserIdForLocalUserId( int|string $userId, int|string $localUserId ): void
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( [
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
		] );
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

	public function getUserIdFromLocalUserId( int|string $localUserId, bool $strict = TRUE )
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( [
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
		] );
		if( !$relation && $strict )
			throw new RuntimeException( 'No payment account available' );
		if( !$relation )
			return NULL;
		return $relation->paymentAccountId;
	}

	/**
	 *	@todo		extend cache key by filters
	 */
	public function getWalletTransactions( int|string $walletId, array $orders = [], array $limits = [] )
	{
		$cacheKey	= 'mangopay_wallet_'.$walletId.'_transactions';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
			$pagination	= $this->provider->getDefaultPagination();
			$sorting	= $this->provider->getDefaultSorting();
	//		$sorting->AddField( 'CreationDate', 'ASC' );
			$filter		= new FilterTransactions();
			$items		= $this->provider->Wallets->GetTransactions( $walletId, $pagination, $filter, $sorting );
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function hasPaymentAccount( int|string $localUserId )
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->countByIndices( [
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
		] );
		return $relation;
	}

	public function setClientLogo( $imageContentBase64 )
	{
		$ClientLogoUpload = new ClientLogoUpload();
		$ClientLogoUpload->File = $imageContentBase64;
		return $this->provider->Clients->UploadLogo( $ClientLogoUpload );
	}

	public function setHook( int|string $id, $eventType, $path, $status = NULL, $tag = NULL )
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
			$hook				= new Hook;
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

	public function transfer( int|string $sourceUserId, int|string $targetUserId, int|string $sourceWalletId, int|string $targetWalletId, $currency, $amount, $fees, $tag = NULL )
	{
		$transfer = new Transfer();
		$transfer->Tag = $tag;
		$transfer->AuthorId = $sourceUserId;
		$transfer->CreditedUserId = $targetUserId;
		$transfer->DebitedFunds = new Money();
		$transfer->DebitedFunds->Currency = $currency;
		$transfer->DebitedFunds->Amount = $amount;
		$transfer->Fees = new Money();
		$transfer->Fees->Currency = $currency;
		$transfer->Fees->Amount = $fees;
		$transfer->DebitedWalletId = $sourceWalletId;
		$transfer->CreditedWalletId = $targetWalletId;
		$result		= $this->provider->Transfers->Create( $transfer );
		return $result;
	}

	public function uncache( $key )
	{
		$this->cache->delete( 'mangopay_'.$key );
	}

	public function updateClient( $data )
	{
		$client	= $this->getClient();
		$copy	= clone( $client );
		$map	= [
			'PrimaryButtonColour'	=> 'colorButton',
			'PrimaryThemeColour'	=> 'colorTheme',
			'TaxNumber'				=> 'taxNumber',
			'PlatformType'			=> 'platformType',
			'PlatformDescription'	=> 'platformDescription',
			'PlatformURL'			=> 'platformUrl',
		];
		foreach( $map as $key => $value )
			if( isset( $data[$value] ) )
				$copy->$key	= $data[$value];

		if( isset( $data['headquarter'] ) && is_array( $data['headquarter'] ) ){
			$map	= [
				'AddressLine1'	=> 'address',
				'City'			=> 'city',
				'Region'		=> 'region',
				'PostalCode'	=> 'postcode',
				'Country'		=> 'country',
			];
			foreach( $map as $key => $value )
				if( isset( $data['headquarter'][$value] ) )
					$copy->HeadquartersAddress->$key	= $data['headquarter'][$value];
		}

		if( isset( $data['emails'] ) && is_array( $data['emails'] ) ){
			$map	= [
				'AdminEmails'	=> 'admin',
				'TechEmails'	=> 'tech',
				'BillingEmails'	=> 'billing',
			];
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

	public function updateUserWallet( int|string $userId, int|string$walletId, $description, $tag = NULL )
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
		$this->cache->delete( 'user_'.$user->Id );
		return $this->provider->Users->UpdateBankAccount( $userId, $bankAccount );
	}*/

	/**
	 *	@link	https://stackoverflow.com/a/174772
	 */
	public static function validateCardNumber( $number, $provider ): ?bool
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

	protected function __onInit(): void
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
	 *	Disables skipping next request afterward.
	 *	To be called right before the next API request.
	 *	@access		protected
	 *	@param		string			$cacheKey			Cache key of entity to possible uncache
	 *	@return		void
	 */
	protected function applyPossibleCacheSkip( string $cacheKey ): void
	{
		if( $this->skipCacheOnNextRequest ){
			$this->cache->delete( $cacheKey );
			$this->skipCacheOnNextRequest	= FALSE;
		}
	}

	protected function checkIsOwnCard( int|string $cardId )
	{
		$card	= $this->checkCard( $cardId );
	//	@todo check card against user cards
		return $card;
	}
}
