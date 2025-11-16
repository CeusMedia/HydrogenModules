<?php /** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\HydrogenFramework\Logic;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Stripe\Exception\ApiErrorException as StripeApiErrorException;
use Stripe\Stripe as Stripe;
use Stripe\Charge as StripeCharge;
use Stripe\Customer as StripeCustomer;
use Stripe\Event as StripeEvent;

class Logic_Payment_Stripe extends Logic
{
	protected Dictionary $moduleConfig;
	protected SimpleCacheInterface $cache;
	protected Resource_Stripe $provider;
	protected bool $skipCacheOnNextRequest;
	protected ?string $baseUrl			= '';

	/**
	 *	@param		int|string		$userId
	 *	@return		StripeCustomer
	 *	@throws		StripeApiErrorException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function checkUser( int|string $userId ): StripeCustomer
	{
		return $this->getUser( $userId );
	}

	/**
	 *	@param		int|string		$orderId
	 *	@param		string			$token
	 *	@return		StripeCharge
	 *	@throws		ReflectionException
	 *	@throws		StripeApiErrorException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function createChargeFromToken( int|string $orderId, string $token ): StripeCharge
	{
		$order	= Logic_Shop::getInstance( $this->env )->getOrder( $orderId, TRUE );

		return StripeCharge::create( [
			'amount'			=> $order->priceTaxed * 100,
			'currency'			=> $order->currency,
			'description'		=> "Online-Bestellung am ".date( 'j.n.Y' ),
			'source'			=> $token,
			'receipt_email'		=> $order->customer->addressBilling->email,
			'shipping'			=> [
				'address'		=> [
					'city'			=> $order->customer->addressDelivery->city,
					'country'		=> $order->customer->addressDelivery->country,
					'line1'			=> $order->customer->addressDelivery->street,
					'postal_code'	=> $order->customer->addressDelivery->postcode,
					'state'			=> $order->customer->addressDelivery->region,
				],
				'name'		=> '',
			],
		] );
	}

	/**
	 *	@param		int|string		$localUserId
	 *	@return		StripeCustomer
	 *	@throws		ReflectionException
	 *	@throws		StripeApiErrorException
	 */
	public function createCustomerFromLocalUser( int|string $localUserId ): StripeCustomer
	{
		$modelUser		= new Model_User( $this->env );
		/** @var ?Entity_User $user */
		$user		= $modelUser->get( $localUserId );
		$customer	= StripeCustomer::create( [
			'email'			=> $user->email,
			'description'	=> $user->username.' ('.$user->firstname.' '.$user->surname.')',
		] );
		$this->setUserIdForLocalUserId( $customer->id, $localUserId );
		return $customer;
	}

	/**
	 *	@param		string			$type
	 *	@param		int|string		$eventId
	 *	@return		StripeEvent
	 *	@throws		StripeApiErrorException
	 */
	public function getEventResource( string $type, int|string $eventId ): StripeEvent
	{
		return StripeEvent::retrieve( $eventId );
	}

	/**
	 *	@param		string		$userId
	 *	@return		StripeCustomer
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		StripeApiErrorException
	 */
	public function getUser( string $userId ): StripeCustomer
	{
		$cacheKey	= 'stripe_user_'.$userId;
		$this->applyPossibleCacheSkip( $cacheKey );
		/** @var ?StripeCustomer $item */
		$item		= $this->cache->get( $cacheKey );
		if( NULL === $item ){
			$item	= StripeCustomer::retrieve( $userId );
			$this->cache->set( $cacheKey, $item );
		}
		return $item;
	}

	/**
	 *	@param		int|string		$userId
	 *	@param		int|string		$localUserId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function setUserIdForLocalUserId( int|string $userId, int|string $localUserId ): void
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
				'userId'			=> $localUserId,
				'paymentAccountId'	=> $userId,
				'provider'			=> 'stripe',
				'createdAt'			=> time(),
			] );
		}
	}

	/**
	 *	@param		int|string		$localUserId
	 *	@param		bool			$strict
	 *	@return		string|NULL
	 *	@throws		ReflectionException
	 */
	public function getUserIdFromLocalUserId( int|string $localUserId, bool $strict = TRUE ): ?string
	{
		$modelAccount	= new Model_User_Payment_Account( $this->env );
		/** @var ?Entity_User_Payment_Account $relation */
		$relation		= $modelAccount->getByIndices( [
			'userId'	=> $localUserId,
			'provider'	=> 'stripe',
		] );

		if( NULL !== $relation )
			return $relation->paymentAccountId;
		if( $strict )
			throw new RuntimeException( 'No payment account available' );
		return NULL;
	}

	/**
	 *	@param		bool		$skip
	 *	@return		self
	 */
	public function skipCacheOnNextRequest( bool $skip ): self
	{
		$this->skipCacheOnNextRequest	= $skip;
		return $this;
	}

	/**
	 *	@param		string		$key
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function uncache( string $key ): bool
	{
		return $this->cache->delete( 'stripe_'.$key );
	}


	//  --  TODO  --  //


	/**
	 * @param string $bankAccountId
	 * @param string $returnUrl
	 * @return mixed
	 * @throws NotSupportedException since not implemented, yet
	 */
	public function createMandate( string $bankAccountId, string $returnUrl ): mixed
	{
		throw new NotSupportedException( 'Not implemented yet' );
		//  ...
	}

	/**
	 * @param string $userId
	 * @return mixed
	 * @throws NotSupportedException since not implemented, yet
	 */
	public function getUserMandates( string $userId ): mixed
	{
		throw new NotSupportedException( 'Not implemented yet' );
		//  ...
	}

	/**
	 * @param string $userId
	 * @param string $bankAccountId
	 * @return mixed
	 * @throws NotSupportedException since not implemented, yet
	 */
	public function getBankAccountMandates( string $userId, string $bankAccountId ): mixed
	{
		throw new NotSupportedException( 'Not implemented yet' );
		//  ...
	}

	/**
	 * @return array
	 * @throws NotSupportedException since not implemented, yet
	 */
	public function getMandates(): array
	{
		throw new NotSupportedException( 'Not implemented yet' );
/*		$cacheKey	= 'stripe_mandates';
		$this->applyPossibleCacheSkip( $cacheKey );
		if( is_null( $items = $this->cache->get( $cacheKey ) ) ){
		//  ...
			$this->cache->set( $cacheKey, $items );
		}
		return $items;*/
	}

	/**
	 *	@param		$data
	 *	@return		mixed
	 *	@throws		NotSupportedException	since not implemented, yet
	 */
	public function createCustomer( $data ): mixed
	{
		throw new NotSupportedException( 'Not implemented yet' );
		//  ...
	}

	/**
	 *	@param		int|string|NULL		$userId
	 *	@return		string
	 *	@todo		implement $userId
	 */
	public function getDefaultCurrency( int|string $userId = NULL ): string
	{
		return 'EUR';
	}

	/**
	 *	@param		StripeCustomer			$user
	 *	@throws		NotSupportedException	since not implemented, yet
	 */
	public function updateCustomer( StripeCustomer $user )
	{
		throw new NotSupportedException( 'Not implemented yet' );
//		$this->uncache( 'user_'.$user->Id );
		//  ...
	}


	//  --  PROTECTED  --  //


	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function applyPossibleCacheSkip( string $cacheKey ): void
	{
		if( $this->skipCacheOnNextRequest ){
			$this->cache->delete( $cacheKey );
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
