<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Deprecation;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Shop extends Logic
{
	/**	@var	Logic_ShopBridge			$bridge */
	protected Logic_ShopBridge $bridge;

	/**	@var	Model_Shop_Cart				$modelCart */
	protected Model_Shop_Cart $modelCart;

	/**	@var	Model_User					$modelUser */
	protected Model_User $modelUser;

	/**	@var	Model_Address				$modelAddress */
	protected Model_Address $modelAddress;

	/**	@var	Model_Shop_Order			$modelOrder */
	protected Model_Shop_Order $modelOrder;

	/**	@var	Model_Shop_Order_Position	$modelOrderPosition */
	protected Model_Shop_Order_Position $modelOrderPosition;

	/** @var	Dictionary					$moduleConfig */
	protected Dictionary $moduleConfig;

	/**	@var	Logic_Shop_Shipping|NULL	$logicShipping			Instance of shipping logic if module is installed */
	protected ?Logic_Shop_Shipping $logicShipping;

	/**	@var	Logic_Shop_Payment|NULL		$logicPayment			Instance of payment logic if module is installed */
	protected ?Logic_Shop_Payment $logicPayment;

	protected bool $usePayment				= FALSE;
	protected bool $useShipping				= FALSE;

	/**
	 * @deprecated	get Model_Shop_Order::priceTaxed instead
	 */
	public function calculateOrderTotalPrice( int|string $orderId ): float
	{
		$order	= $this->modelOrder->get( $orderId );
		if( !$order )
			throw new InvalidArgumentException( 'Invalid order ID' );								//  else quit with exception
		return $order->priceTaxed;
	}

	public function countArticleInCart( int|string $bridgeId, int|string $articleId ): int
	{
		if( is_array( ( $positions = $this->modelCart->get( 'positions' ) ) ) )
			foreach( $positions as $position )
				if( $position->bridgeId == $bridgeId && $position->articleId == $articleId )
					return (int) $position->quantity;
		return 0;
	}

	public function countArticlesInCart( bool $countEach = FALSE ): int
	{
		$number	= 0;
		if( is_array( ( $positions = $this->modelCart->get( 'positions' ) ) ) ){
			if( !$countEach )
				return count( $positions );
			foreach( $positions as $position )
				$number	+= $position->quantity;
		}
		return $number;
	}

	public function countOrders( array $conditions ): int
	{
		return $this->modelOrder->count( $conditions );
	}

	public function getAccountCustomer( int|string $userId ): object
	{
		$user	= $this->modelUser->get( $userId );
		if( !$user )
			throw new RangeException( 'No customer found for user ID '.$userId );
		$user->addressBilling	= $this->modelAddress->getByIndices( [
			'relationType'	=> 'user',
			'relationId'	=> $userId,
			'type'			=> Model_Address::TYPE_BILLING,
		] );
		$user->addressDelivery	= $this->modelAddress->getByIndices( [
			'relationType'	=> 'user',
			'relationId'	=> $userId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		] );
		return $user;
	}

	public function getBillingAddressFromCart(): ?object
	{
		$address		= NULL;
		if( $this->modelCart->get( 'userId' ) ){
			$address	= $this->modelAddress->getByIndices( array(
				'relationId'	=> $this->modelCart->get( 'userId' ),
				'relationType'	=> 'user',
				'type'			=> Model_Address::TYPE_BILLING,
			) );
		}
		return $address;
	}

	public function getDeliveryAddressFromCart(): ?object
	{
		$address		= NULL;
		if( $this->modelCart->get( 'userId' ) ){
			$address	= $this->modelAddress->getByIndices( [
				'relationId'	=> $this->modelCart->get( 'userId' ),
				'relationType'	=> 'user',
				'type'			=> Model_Address::TYPE_DELIVERY,
			] );
		}
		return $address;
	}

	public function getPaymentFeesFromCart(): ?float
	{
		if( !$this->usePayment )
			return NULL;

		$backend	= $this->modelCart->get( 'paymentMethod' );
		$address	= $this->getDeliveryAddressFromCart();
		return $this->logicPayment->getPrice( $this->modelCart->getTotal(), $backend, $address->country );
	}

	/**
	 *	@deprecated
	 */
	public function getGuestCustomer( int|string $customerId ): object
	{
		throw new RuntimeException( 'Method Logic_Shop::getGuestCustomer is deprecated' );
		$model		= new Model_Shop_Customer( $this->env );
		$customer	= $model->get( $customerId );
		if( !$customer )
			throw new RangeException( 'Invalid customer ID: '.$customerId );
		$customer->addressBilling	= $this->modelAddress->getByIndices( [
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_BILLING,
		] );
		$customer->addressDelivery	= $this->modelAddress->getByIndices( [
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		] );
		if( $customer->addressDelivery ){
			$customer->userId		= 0;
			$customer->gender		= 0;
			$customer->email		= $customer->addressDelivery->email;
			$customer->firstname	= $customer->addressDelivery->firstname;
			$customer->surname		= $customer->addressDelivery->surname;
			$customer->username		= $customer->addressDelivery->firstname.' '.$customer->addressDelivery->surname;
		}
		return $customer;
	}

	public function getOrder( int|string $orderId, bool $extended = FALSE ): object
	{
		$order	= $this->modelOrder->get( $orderId );
		if( !$order )
			throw new RangeException( 'Invalid order ID: '.$orderId );
		if( $extended ){
			$order->customer	= $this->getOrderCustomer( $orderId );
			$order->positions	= $this->getOrderPositions( $orderId, TRUE );
			$order->shipping	= $this->getOrderShipping( $orderId );
			$order->options		= $this->getOrderOptions( $orderId );
			$order->payment		= $this->getOrderPaymentFees( $orderId );
			$order->taxes		= $this->getOrderTaxes( $orderId );
		}
		return $order;
	}

	public function getOrderCustomer( int|string $orderId ): object
	{
		$order	= $this->modelOrder->get( $orderId );
		if( !$order )
			throw new RangeException( 'Invalid order ID: '.$orderId );
		if( !$order->userId )
			throw new Exception( 'No user assigned to order' );
		return $this->getAccountCustomer( $order->userId );
	}

	/**
	 *	@todo		to be implemented: use Model_Shop_Shipping_Option
	 */
	public function getOrderOptions( string $orderId ): object
	{
		return (object) [];
	}

	public function getOrderPosition( int|string $positionId, bool $extended = FALSE ): ?object
	{
		$position	= $this->modelOrderPosition->get( $positionId );
		if( $extended ){
			$source		= $this->bridge->getBridgeObject( (int) $position->bridgeId );				//  get bridge source of article
			$position->article	= $source->get( $position->articleId, $position->quantity );		//  get article data
		}
		return $position;
	}

	public function getOrderPositions( int|string $orderId, bool $extended = FALSE ): array
	{
		$positions	= $this->modelOrderPosition->getAllByIndex( 'orderId', $orderId );
		if( $extended ){
			foreach( $positions as $position ){
				$source		= $this->bridge->getBridgeObject( (int) $position->bridgeId );			//  get bridge source of article
				$position->article	= $source->get( $position->articleId, $position->quantity );	//  get article data
			}
		}
		return $positions;
	}

	public function getOrders( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelOrder->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		make tax rate configurable - store rate on shipping price or service
	 */
	public function getOrderShipping( int|string $orderId ): object
	{
		$taxIncluded	= $this->env->getConfig()->get( 'module.shop.tax.included' );
		$facts			= (object) [
			'price'			=> .0,
			'tax'			=> .0,
			'priceTaxed'	=> .0,
			'taxRate'		=> 19,				//  @todo: make configurable
		];
		if( !$this->useShipping )
			return $facts;

		$customer		= $this->getOrderCustomer( $orderId );
		if( $customer && $customer->addressDelivery ){
			$weight			= 0;
			$positions		= $this->getOrderPositions( $orderId, TRUE );
			foreach( $positions as $position )
				$weight	+= $position->article->weight->all;
			$facts->price	= $this->logicShipping->getPriceFromCountryCodeAndWeight(
				$customer->addressDelivery->country,
				$weight
			);
			$facts->tax			= $facts->price * ( $facts->taxRate / 100 );
			$facts->priceTaxed	= $facts->price + $facts->tax;
			if( $taxIncluded ){
				$facts->priceTaxed	= $facts->price;
				$facts->price		-= $facts->tax;
			}
		}
		return $facts;
	}

	/**
	 *	@todo		make tax rate configurable - store rate on shipping price or service
	 */
	public function getOrderPaymentFees( int|string $orderId ): object
	{
		$facts			= (object) [
			'price'			=> .0,
			'tax'			=> .0,
			'priceTaxed'	=> .0,
			'taxRate'		=> 19,				//  @todo: make configurable
		];
		if( !$this->usePayment )
			return $facts;

		$taxIncluded	= $this->env->getConfig()->get( 'module.shop.tax.included' );

		$order	= $this->getOrder( $orderId );

		$price		= .0;
		$priceTaxed	= .0;
		$tax		= .0;

		$positions	= $this->getOrderPositions( $orderId, TRUE );
		foreach( $positions as $position ){
			$price		+= $position->article->price->all;
			$priceTaxed	+= $position->article->price->all + $position->article->tax->all;
			$tax		+= $position->article->tax->all;
		}

		if( $this->useShipping ){
			$shipping	= $this->getOrderShipping( $orderId );
			$price		+= $shipping->price;
			$priceTaxed	+= $shipping->priceTaxed;
			$tax		+= $shipping->tax;
		}

		$backend	= $this->getOrder( $orderId )->paymentMethod;
		$facts->price		= $this->logicPayment->getPrice( $priceTaxed, $backend, 'DE' );
		$facts->tax			= $facts->price * ( $facts->taxRate / 100 );
		$facts->priceTaxed	= $facts->price + $facts->tax;
		if( $taxIncluded ){
			$facts->priceTaxed	= $facts->price;
			$facts->price		-= $facts->tax;
		}

		return $facts;
	}

	public function getOrderTaxes( int|string $orderId ): array
	{
		$taxes		= [];
		$sum		= 0;
		$positions	= $this->getOrderPositions( $orderId, TRUE );
		foreach( $positions as $position ){
			if( !isset( $taxes[$position->article->tax->rate] ) )
				$taxes[$position->article->tax->rate]	= 0;
			$taxes[$position->article->tax->rate]	+= $position->article->tax->all;
			$sum	+= $position->article->tax->all;
		}

		if( $this->useShipping ){
			$shipping	= $this->getOrderShipping( $orderId );
			if( !isset( $taxes[$shipping->taxRate] ) )
				$taxes[$shipping->taxRate]	= 0;
			$taxes[$shipping->taxRate]	+= $shipping->tax;
			$sum	+= $shipping->tax;

		}

		if( $this->usePayment ){
			$payment	= $this->getOrderPaymentFees( $orderId );
			if( !isset( $taxes[$payment->taxRate] ) )
				$taxes[$payment->taxRate]	= 0;
			$taxes[$payment->taxRate]	+= $payment->tax;
			$sum		+= $payment->tax;
		}

		$taxes['total']	= $sum;
		return $taxes;
	}

	/**
	 *	@deprecated		use Model_Shop_Cart instead
	 */
/*	public function getOpenSessionOrder( $sessionId )
	{
		$conditions	= [
			'sessionId'		=> $sessionId,
			'status'		=> '< 2',
		];
		return $this->modelOrder->getAll( $conditions );
	}*/

	/**
	 *	@deprecated
	 */
	public function getShipping( bool $strict = TRUE )
	{
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'getShipping is deprecated' );
		if( !$this->useShipping && $strict )
			throw new RuntimeException( "Shipping module is not installed" );
		return $this->useShipping ?: NULL;
	}

	/**
	 *	Returns Shipping Zone ID of Country.
	 *	@access		public
	 *	@param		int|string		 $countryId		ID of Country
	 *	@return		integer|NULL
	 *	@todo		rename to getShippingZoneOfCountryId and change behaviour
	 *	@deprecated
	 */
	public function getShippingZoneId( int|string $countryId )
	{
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'getShippingZoneId is deprecated' );
		return $this->getShipping()->getZoneId( $countryId );
	}

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		integer		 $quantity		Quantity to ge Shipping Grade for
	 *	@return		int
	 *	@deprecated
	 */
	public function getShippingGradeIdByQuantity( int $quantity )
	{
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'getShippingGradeIdByQuantity is deprecated' );
		return $this->getShipping()->getGradeID( $shippingZoneId, $shippingGradeId );
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		int|string		$shippingZoneId 		ID of Shipping Zone
	 *	@param		int|string		$shippingGradeId 		ID of Shipping Grade
	 *	@return		string
	 *	@deprecated
	 */
	public function getShippingPrice( int|string $shippingZoneId, int|string $shippingGradeId )
	{
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'getShippingPrice is deprecated' );
		return $this->getShipping()->getPrice( $shippingZoneId, $shippingGradeId );
	}

	public function hasArticleInCart( int|string $bridgeId, int|string $articleId ): bool
	{
		return $this->countArticleInCart( $bridgeId, $articleId ) > 0;
	}

	public function setOrderPaymentId( int|string $orderId, int|string $paymentId ): bool
	{
		return (bool) $this->modelOrder->edit( $orderId, [
			'paymentId'		=> $paymentId,
			'modifiedAt'	=> time(),
		] );
	}

	public function setOrderPaymentMethod( int|string $orderId, string $paymentMethod ): bool
	{
		return (bool) $this->modelOrder->edit( $orderId, [
			'paymentMethod'	=> $paymentMethod,
			'modifiedAt'	=> time(),
		] );
	}

	public function setOrderPositionStatus( int|string $positionId, $status ): bool
	{
		return (bool) $this->modelOrderPosition->edit( $positionId, [
			'status'		=> (int) $status,
			'modifiedAt'	=> time(),
		] );
	}

	public function setOrderUserId( int|string $orderId, int|string $userId ): bool
	{
		return (bool) $this->modelOrder->edit( $orderId, [
			'userId'		=> $userId,
			'modifiedAt'	=> time(),
		] );
	}

	public function setOrderStatus( int|string $orderId, $status ): bool
	{
		$order	= $this->getOrder( $orderId );
		if( $status == Model_Shop_Order::STATUS_PAYED ){
			if( $order->status == Model_Shop_Order::STATUS_ORDERED ){
				$positions	= $this->getOrderPositions( $orderId );
				foreach( $positions as $position ){
					$bridge	= $this->bridge->getBridge( $position->bridgeId );
					$change	= -1 * (int) $position->quantity;
					$bridge->object->changeQuantity( $position->articleId, $change );
				}
			}
		}
		return (bool) $this->modelOrder->edit( $orderId, [
			'status'		=> (int) $status,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@deprecated
	 */
	public function setShipping( $logic )
	{
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'setShipping is deprecated' );
		if( !( $logic instanceof Logic) )
			throw new RuntimeException( 'Invalid logic object (must extend Logic)' );
		$this->logicShipping		= $logic;
	}

	protected function __onInit(): void
	{
		$this->bridge				= new Logic_ShopBridge( $this->env );
		$this->modelUser			= new Model_User( $this->env );
		$this->modelAddress			= new Model_Address( $this->env );
		$this->modelCart			= new Model_Shop_Cart( $this->env );
		$this->modelOrder			= new Model_Shop_Order( $this->env );
		$this->modelOrderPosition	= new Model_Shop_Order_Position( $this->env );
		$this->moduleConfig			= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		if( $this->env->getModules()->has( 'Shop_Shipping' ) ){
			$this->useShipping		= TRUE;
			$this->logicShipping	= new Logic_Shop_Shipping( $this->env );
		}
		if( $this->env->getModules()->has( 'Shop_Payment' ) ){
			$this->usePayment		= TRUE;
			$this->logicPayment		= new Logic_Shop_Payment( $this->env );
		}
	}
}
