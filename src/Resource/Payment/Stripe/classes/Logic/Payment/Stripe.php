<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;
use Stripe\Stripe as Stripe;
use Stripe\Charge as StripeCharge;
use Stripe\Customer as StripeCustomer;

class Logic_Payment_Stripe extends Logic
{
	protected Dictionary $moduleConfig;
	protected $cache;
	protected Resource_Stripe $provider;
	protected bool $skipCacheOnNextRequest;
	protected ?string $baseUrl			= '';

	public function checkUser( string $userId )
	{
		return $this->getUser( $userId );
	}

	public function createMandate( string $bankAccountId, string $returnUrl )
	{
		throw new Exception( 'Not implemented yet' );
		//  ...
	}

	public function getUserMandates( string $userId )
	{
		throw new Exception( 'Not implemented yet' );
		//  ...
	}

	public function getBankAccountMandates( string $userId, string $bankAccountId )
	{
		throw new Exception( 'Not implemented yet' );
		//  ...
	}

	public function getMandates()
	{
		throw new Exception( 'Not implemented yet' );
		$cacheKey	= 'stripe_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
		//  ...
			$this->cache->set( $cacheKey, $items );
		}
		return $items;
	}

	public function createChargeFromToken( string $orderId, string $token ): StripeCharge
	{
		$modelOrder	= new Model_Shop_Order( $this->env );
		$order		= $modelOrder->get( $orderId );
		$charge		= StripeCharge::create( [
			"amount"		=> $order->priceTaxed * 100,
			"currency"		=> $order->currency,
			"description"	=> "Online-Bestellung am ".date( 'j.n.Y' ),
			"source"		=> $token,
		] );
		return $charge;
	}

	public function createCustomer( $data )
	{
		throw new Exception( 'Not implemented yet' );
		//  ...
	}

	public function createCustomerFromLocalUser( $localUserId )
	{
		$modelUser		= new Model_User( $this->env );
		$modelAddress	= new Model_Address( $this->env );
		$user			= $modelUser->get( $localUserId );
		$user	= StripeCustomer::create( [
			'email'			=> $user->email,
			'description'	=> $user->username.' ('.$user->firstname.' '.$user->surname.')',
		] );
		$this->setUserIdForLocalUserId( $user->id, $localUserId );
		return $user;
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

	public function getUser( string $userId )
	{
		$cacheKey	= 'stripe_user_'.$userId;
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $item = $this->cache->get( $cacheKey ) ) ){
			$item	= StripeCustomer::retrieve( $userId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	public function setUserIdForLocalUserId( int|string $userId, int|string $localUserId )
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( [
			'userId'	=> $localUserId,
			'provider'	=> 'stripe',
		] );
		if( $relation ){
			$modelAccount->edit( $relation->userPaymentAccountId, [
				'paymentAccountId'	=> $userId,
//				'modifiedAt'		=> time(),
			] );
		}
		else{
			$modelAccount->add( [
				'userId'	=> $localUserId,
				'paymentAccountId'	=> $userId,
				'provider'	=> 'stripe',
				'createdAt'	=> time(),
			] );
		}
	}

	public function getUserIdFromLocalUserId( int|string $localUserId, bool $strict = TRUE )
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		$relation		= $modelAccount->getByIndices( [
			'userId'	=> $localUserId,
			'provider'	=> 'stripe',
		] );
		if( !$relation && $strict )
			throw new RuntimeException( 'No payment account available' );
		if( !$relation )
			return NULL;
		return $relation->paymentAccountId;
	}

	public function skipCacheOnNextRequest( bool $skip ): self
	{
		$this->skipCacheOnNextRequest	= $skip;
		return $this;
	}

	public function uncache( string $key )
	{
		return $this->cache->remove( 'stripe_'.$key );
	}

	public function updateCustomer( $user )
	{
		throw new Exception( 'Not implemented yet' );
		$this->uncache( 'user_'.$user->Id );
		//  ...
	}

	protected function __onInit(): void
	{
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_stripe.', TRUE );
		Stripe::setApiKey( $this->moduleConfig->get( 'api.key.secret' ) );

//		print_m( $this->moduleConfig->getAll() );die;
		$this->cache		= $this->env->getCache();
		$this->provider		= Resource_Stripe::getInstance( $this->env );
		$this->baseUrl		= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();
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
			$this->cache->remove( $cacheKey );
			$this->skipCacheOnNextRequest	= FALSE;
		}
	}

/*	protected function checkIsOwnCard( $cardId )
	{
		$card	= $this->checkCard( $cardId );
	//	@todo check card against user cards
		return $card;
	}*/
}
