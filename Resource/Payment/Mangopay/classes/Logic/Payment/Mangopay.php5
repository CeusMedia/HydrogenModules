<?php
class Logic_Payment_Mangopay{

	static protected $instance;
	protected $env;
	protected $cache;
	protected $provider;
	protected $skipCacheOnNextRequest;

	protected function __construct( $env ){
		$this->env			= $env;
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
		$this->cache		= $this->env->getCache();
		$this->provider		= Resource_Mangopay::getInstance( $this->env );
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Payment_Mangopay( $env );
		return self::$instance;
	}

	/**
	 *	@link	https://stackoverflow.com/a/174772
	 */
	static public function validateCardNumber( $number, $provider ){
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

	protected function checkIsOwnCard( $cardId ){
		$card	= $this->checkCard( $cardId );
	//	@todo check card against user cards
		return $card;
	}

	protected function getCardById( $cardId ){
		return $this->provider->Cards->Get( $cardId );
	}

	public function getUsersCards( $userId, $conditions = array(), $orders = array(), $limits = array() ){
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		if( !$orders )
			$sorting->AddField( 'CreationDate', 'DESC' );
		else{
			foreach( $orders as $orderKey => $orderValue )
				$sorting->AddField( $orderKey, strtoupper( $orderValue ) );
		}
		$cacheKey	= 'user_'.$userId.'_cards';
		$refresh	= 1;//$this->skipCacheOnNextRequest;
		if( $refresh || is_null( $cards = $this->cache->get( $cacheKey ) ) ){
			$cards	= $this->provider->Users->GetCards( $userId, $pagination, $sorting );
//			$this->skipCacheOnNextRequest	= FALSE;
			$this->cache->set( $cacheKey, $cards );
		}
		return $cards;
	}

	public function calculateFeesForPayIn( $price ){
		return $price * $this->moduleConfig->get( 'fees.payin' ) / 100;
	}

	public function createPayInFromCard( $userId, $walletId, $cardId, $amount, $secureModeReturnUrl ){

		$card	= $this->getCardById( $cardId );

		$payIn		= new \MangoPay\PayIn();
		$payIn->CreditedWalletId	= $walletId;
		$payIn->AuthorId			= $userId;
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

	public function hasPaymentAccount( $localUserId ){
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->countByIndices( array(
			'userId'	=> $localUserId,
			'provider'	=> 'mangopay',
		) );
		return $relation;
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

	public function skipCacheOnNextRequest( $skip ){
		$this->skipCacheOnNextRequest	= (bool) $skip;
	}
}
