<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Exception\Deprecation as DeprecationException;
use CeusMedia\HydrogenFramework\Logic;

class Logic_ShopResource extends Logic
{
	/**	@var	Logic_ShopBridge			$bridge */
	protected Logic_ShopBridge $bridge;

	/**	@var	Model_User					$modelUser */
	protected Model_User $modelUser;

	/**	@var	Model_Address				$modelAddress */
	protected Model_Address $modelAddress;

	/**	@var	Model_Shop_Order			$modelOrder */
	protected Model_Shop_Order $modelOrder;

	/**	@var	Model_Shop_Order_Position	$modelOrderPosition */
	protected Model_Shop_Order_Position $modelOrderPosition;

	/**	@var	Logic_Shop_Shipping|NULL	$logicShipping			Instance of shipping logic if module is installed */
	protected ?Logic_Shop_Shipping $logicShipping;

	/**	@var	Logic_Shop_Payment|NULL		$logicPayment			Instance of payment logic if module is installed */
	protected ?Logic_Shop_Payment $logicPayment;

	protected bool $usePayment				= FALSE;
	protected bool $useShipping				= FALSE;

	/**
	 *	@deprecated	get Model_Shop_Order::priceTaxed instead
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		InvalidArgumentException	if given order ID is invalid
	 */
	public function calculateOrderTotalPrice( int|string $orderId ): float
	{
		/** @var ?Entity_Shop_Order $order */
		$order	= $this->modelOrder->get( $orderId );
		if( NULL === $order )
			throw new InvalidArgumentException( 'Invalid order ID' );								//  else quit with exception
		return $order->priceTaxed;
	}

	/**
	 *	Returns number of orders for given conditions.
	 *	@access		public
	 *	@param		array		$conditions
	 *	@return		integer
	 */
	public function countOrders( array $conditions ): int
	{
		return $this->modelOrder->count( $conditions );
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		Entity_User
	 *	@throws		RangeException	if customer ID is invalid
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getAccountCustomer( int|string $userId ): Entity_User
	{
		/** @var ?Entity_User $user */
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

	/**
	 *	@deprecated
	 *	@noinspection	PhpUnusedParameterInspection
	 *	@noinspection	PhpUnused
	 */
	public function getGuestCustomer( int|string $customerId ): object
	{
		throw new DeprecationException( 'Method Logic_Shop::getGuestCustomer is deprecated' );
/*		$model		= new Model_Shop_Customer( $this->env );
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
		return $customer;*/
	}

	/**
	 *	@param		int|string	$orderId
	 *	@param		bool		$extended		Flag: also load [customer,positions,shipping,(options),payment,taxes], default: no
	 *	@return		Entity_Shop_Order
	 *	@throws		RangeException				if given order ID is invalid
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getOrder( int|string $orderId, bool $extended = FALSE ): Entity_Shop_Order
	{
		/** @var ?Entity_Shop_Order $order */
		$order	= $this->modelOrder->get( $orderId );
		if( NULL === $order )
			throw new RangeException( 'Invalid order ID: '.$orderId );
		if( $extended ){
			$order->customer	= $this->getOrderCustomer( $order );
			$order->positions	= $this->getOrderPositions( $orderId, TRUE );
			$order->shipping	= $this->getOrderShipping( $orderId );
//			$order->options		= $this->getOrderOptions( $orderId );
			$order->payment		= $this->getOrderPaymentFees( $orderId );
			$order->taxes		= $this->getOrderTaxes( $orderId );
		}
		return $order;
	}

	/**
	 *	@param		object|int|string		$orderObjectOrId
	 *	@return		Entity_User					Customer account data object
	 *	@throws		RangeException			if no order found for given order ID
	 *	@throws		RuntimeException		if order has no user assigned
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getOrderCustomer( object|int|string $orderObjectOrId ): Entity_User
	{
		$order	= $orderObjectOrId;
		if( !is_object( $order ) ){
			$order	= $this->modelOrder->get( $orderObjectOrId );
			if( NULL === $order )
				throw new RangeException( 'Invalid order ID: '.$orderObjectOrId );
		}

		if( !$order->userId )
			throw new RuntimeException( 'No user assigned to order' );
		return $this->getAccountCustomer( $order->userId );
	}

	/**
	 *	@todo		to be implemented: use Model_Shop_Shipping_Option
	 *	@noinspection PhpUnusedParameterInspection
	 */
	public function getOrderOptions( int|string $orderId ): object
	{
		return (object) [];
	}

	/**
	 *	@param		int|string		$positionId
	 *	@param		bool			$extended		Flag: extend by article entity
	 *	@return		Entity_Shop_Order_Position|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getOrderPosition( int|string $positionId, bool $extended = FALSE ): ?Entity_Shop_Order_Position
	{
		/** @var ?Entity_Shop_Order_Position $position */
		$position	= $this->modelOrderPosition->get( $positionId );
		if( NULL !== $position && $extended ){
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		RangeException			if no order found for given order ID
	 *	@throws		RuntimeException		if order has no user assigned
	 */
	public function getOrderShipping( int|string $orderId ): object
	{
		$taxIncluded	= $this->env->getConfig()->get( 'module.resource_shop.tax.included' );
		$facts			= (object) [
			'price'			=> .0,
			'tax'			=> .0,
			'priceTaxed'	=> .0,
			'taxRate'		=> $this->env->getConfig()->get( 'module.resource_shop.tax.percent', 19 ),
		];
		if( !$this->useShipping )
			return $facts;

		$customer		= $this->getOrderCustomer( $orderId );
		if( NULL !== $customer->addressDelivery ){
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getOrderPaymentFees( int|string $orderId ): object
	{
		$facts			= (object) [
			'price'			=> .0,
			'tax'			=> .0,
			'priceTaxed'	=> .0,
			'taxRate'		=> $this->env->getConfig()->get( 'module.resource_shop.tax.percent', 19 ),
		];
		if( !$this->usePayment )
			return $facts;

		$taxIncluded	= $this->env->getConfig()->get( 'module.resource_shop.tax.included' );

	//	$order	= $this->getOrder( $orderId );

//		$price		= .0;
		$priceTaxed	= .0;
//		$tax		= .0;

		$positions	= $this->getOrderPositions( $orderId, TRUE );
		foreach( $positions as $position ){
//			$price		+= $position->article->price->all;
			$priceTaxed	+= $position->article->price->all + $position->article->tax->all;
//			$tax		+= $position->article->tax->all;
		}

		if( $this->useShipping ){
			$shipping	= $this->getOrderShipping( $orderId );
//			$price		+= $shipping->price;
			$priceTaxed	+= $shipping->priceTaxed;
//			$tax		+= $shipping->tax;
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

	/**
	 *	Sums up calculates taxes for cart positions, shipping costs and payment costs.
	 *	@param		int|string		$orderId
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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
	 *	@deprecated
	 *	@noinspection	PhpUnusedParameterInspection
	 *	@noinspection	PhpUnused
	 */
	public function getShipping( bool $strict = TRUE ): ?bool
	{
		throw new DeprecationException( 'Method Logic_Shop::getShipping is deprecated' );
	}

	/**
	 *	Returns Shipping Zone ID of Country.
	 *	@access		public
	 *	@param		int|string		 $countryId		ID of Country
	 *	@return		integer|NULL
	 *	@todo		rename to getShippingZoneOfCountryId and change behaviour
	 *	@deprecated
	 *	@noinspection PhpUnusedParameterInspection
	 */
	public function getShippingZoneId( int|string $countryId ): ?int
	{
		throw new DeprecationException( 'Method Logic_Shop::getShippingZoneId is deprecated' );
	}

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		integer		 $quantity		Quantity to ge Shipping Grade for
	 *	@return		int
	 *	@deprecated
	 *	@noinspection	PhpUnusedParameterInspection
	 *	@noinspection	PhpUnused
	 */
	public function getShippingGradeIdByQuantity( int $quantity ): int
	{
		throw new DeprecationException( 'Method Logic_Shop::getShippingGradeIdByQuantity is deprecated' );
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		int|string		$shippingZoneId 		ID of Shipping Zone
	 *	@param		int|string		$shippingGradeId 		ID of Shipping Grade
	 *	@return		string
	 *	@deprecated
	 *	@noinspection	PhpUnusedParameterInspection
	 *	@noinspection	PhpUnused
	 */
	public function getShippingPrice( int|string $shippingZoneId, int|string $shippingGradeId ): string
	{
		throw new DeprecationException( 'Method Logic_Shop::getShippingPrice is deprecated' );
	}

	/**
	 *	@param		int|string		$orderId
	 *	@param		int|string		$paymentId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setOrderPaymentId( int|string $orderId, int|string $paymentId ): bool
	{
		return (bool) $this->modelOrder->edit( $orderId, [
			'paymentId'		=> $paymentId,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$orderId
	 *	@param		string			$paymentMethod
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setOrderPaymentMethod( int|string $orderId, string $paymentMethod ): bool
	{
		return (bool) $this->modelOrder->edit( $orderId, [
			'paymentMethod'	=> $paymentMethod,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$positionId
	 *	@param		int				$status
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setOrderPositionStatus( int|string $positionId, int $status ): bool
	{
		return (bool) $this->modelOrderPosition->edit( $positionId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$orderId
	 *	@param		int|string		$userId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setOrderUserId( int|string $orderId, int|string $userId ): bool
	{
		return (bool) $this->modelOrder->edit( $orderId, [
			'userId'		=> $userId,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$orderId
	 *	@param		int				$status
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setOrderStatus( int|string $orderId, int $status ): bool
	{
		$order	= $this->getOrder( $orderId );
		if( $status === $order->status )
			return FALSE;
		if( Model_Shop_Order::STATUS_PAYED === $status ){
			if( Model_Shop_Order::STATUS_ORDERED === $order->status ){
				$positions	= $this->getOrderPositions( $orderId );
				foreach( $positions as $position ){
					$bridge	= $this->bridge->getBridge( $position->bridgeId );
					$change	= -1 * (int) $position->quantity;
					$bridge->object->changeQuantity( $position->articleId, $change );
				}
			}
		}
		return (bool) $this->modelOrder->edit( $orderId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@deprecated
	 *	@noinspection	PhpUnusedParameterInspection
	 *	@noinspection	PhpUnused
	 */
	public function setShipping( $logic )
	{
		throw new DeprecationException( 'Method Logic_Shop::setShipping is deprecated' );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->bridge				= new Logic_ShopBridge( $this->env );
		$this->modelUser			= new Model_User( $this->env );
		$this->modelAddress			= new Model_Address( $this->env );
		$this->modelOrder			= new Model_Shop_Order( $this->env );
		$this->modelOrderPosition	= new Model_Shop_Order_Position( $this->env );
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
