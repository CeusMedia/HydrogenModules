<?php
class Logic_Payment_Stripe extends CMF_Hydrogen_Logic{

	protected $cache;
	protected $provider;
	protected $skipCacheOnNextRequest;
	protected $baseUrl;

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

/*	protected function checkIsOwnCard( $cardId ){
		$card	= $this->checkCard( $cardId );
	//	@todo check card against user cards
		return $card;
	}*/

	public function checkUser( $userId ){
		return $this->getUser( $userId );
	}

	public function createMandate( $bankAccountId, $returnUrl ){
		throw new Exception( 'Not implemented yet' );
		//  ...
	}

	public function getUserMandates( $userId ){
		throw new Exception( 'Not implemented yet' );
		//  ...
	}

	public function getBankAccountMandates( $userId, $bankAccountId ){
		throw new Exception( 'Not implemented yet' );
		//  ...
	}

	public function getMandates(){
		throw new Exception( 'Not implemented yet' );
		$cacheKey	= 'stripe_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
		//  ...
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function createChargeFromToken( $orderId, $token ){
		$modelOrder	= new Model_Shop_Order( $this->env );
		$order		= $modelOrder->get( $orderId );
		$charge		= \Stripe\Charge::create( array(
			"amount"		=> $order->priceTaxed * 100,
			"currency"		=> $order->currency,
			"description"	=> "Online-Bestellung am ".date( 'j.n.Y' ),
			"source"		=> $token,
		) );
		return $charge;
	}

	public function createCustomer( $data ){
		throw new Exception( 'Not implemented yet' );
		//  ...
	}

	public function createCustomerFromLocalUser( $localUserId ){
		$modelUser		= new Model_User( $this->env );
		$modelAddress	= new Model_Address( $this->env );
		$user			= $modelUser->get( $localUserId );
		$user	= \Stripe\Customer::create( array(
			'email'			=> $address->email,
			'description'	=> $user->username.' ('.$user->firstname.' '.$user->surname.')',
		) );
		$this->setUserIdForLocalUserId( $user->id, $localUserId );
		return $user;
	}

	/**
	 *	@todo			implement
	 */
	public function getDefaultCurrency( $userId = NULL ){
		$currency	= 'EUR';
		if( $userId ){

		}
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

	public function skipCacheOnNextRequest( $skip ){
		$this->skipCacheOnNextRequest	= (bool) $skip;
	}

	public function uncache( $key ){
		$this->cache->remove( 'stripe_'.$key );
	}

	public function updateCustomer( $user ){
		throw new Exception( 'Not implemented yet' );
		$this->uncache( 'user_'.$user->Id );
		//  ...
	}
}
