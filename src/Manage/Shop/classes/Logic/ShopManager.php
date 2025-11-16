<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Logic;

class Logic_ShopManager extends Logic
{
	/**	@var		Model_User					$modelUser */
	protected Model_User $modelUser;

	/**	@var		Model_Address				$modelAddress */
	protected Model_Address $modelAddress;

	/**	@var		Model_Shop_Order			$modelOrder */
	protected Model_Shop_Order $modelOrder;

//	/**	@var		Model_Shop_Order_Position	$modelOrderPosition */
//	protected Model_Shop_Order_Position $modelOrderPosition;

	/**	@var		?Logic_Shop_Shipping			$shipping			Shipping logic if module is installed */
	protected ?Logic_Shop_Shipping $shipping	= NULL;

	protected Logic_ShopResource $logicShop;

	/**
	 *	Returns number of orders for given conditions.
	 *	@access		public
	 *	@param		array		$conditions
	 *	@return		integer
	 */
	public function countOrders( array $conditions ): int
	{
//		return $this->modelOrder->count( $conditions );
		return $this->logicShop->countOrders( $conditions );
	}

	public function getOrderCustomer( int|string $orderId ): object
	{
		$order	= $this->modelOrder->get( $orderId );
		if( !$order )
			throw new RangeException( 'Invalid order ID: '.$orderId );
		if( $order->userId )
			return $this->getAccountCustomer( $order->userId );
		else if( $order->customerId )
			return $this->getGuestCustomer( $order->customerId );
		throw new Exception( 'No user or customer assigned to order' );
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		Entity_User
	 *	@throws		RangeException	if customer ID is invalid
	 */
	public function getAccountCustomer( int|string $userId ): Entity_User
	{
		return $this->logicShop->getAccountCustomer( $userId );
	}

	/**
	 *	@deprecated
	 */
	public function getGuestCustomer( int|string $customerId ): object
	{
//		throw new RuntimeException( 'Method Logic_ShopManager::getGuestCustomer is deprecated' );
		$model	= new Model_Shop_Customer( $this->env );
		/** @var Entity_Shop_Customer $user */
		$user	= $model->get( $customerId );
		if( !$user )
			throw new RangeException( 'Invalid customer ID: '.$customerId );
		$user->addressBilling	= $this->modelAddress->getByIndices( [
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_BILLING,
		] );
		$user->addressDelivery	= $this->modelAddress->getByIndices( [
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		] );
		return $user;
	}

	public function getCustomers( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return [];//$this->modelCustomer->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		int|string	$orderId
	 *	@param		bool		$extended		Flag: also load [customer,positions,shipping,(options),payment,taxes], default: no
	 *	@return		Entity_Shop_Order
	 *	@throws		RangeException				if given order ID is invalid
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getOrder( int|string $orderId, bool $extended = FALSE ): object
	{
		return $this->logicShop->getOrder( $orderId, $extended );
	}

	public function getOrders( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
//		return $this->modelOrder->getAll( $conditions, $orders, $limits );
		return $this->logicShop->getOrders( $conditions, $orders, $limits );
	}

	/**
	 *	@param		int|string		$orderId
	 *	@param		bool			$extended		Flag: extend by article entities
	 *	@return		array<Entity_Shop_Order_Position>
	 */
	public function getOrderPositions( int|string $orderId, bool $extended = FALSE ): array
	{
//		return $this->modelOrderPosition->getAllByIndex( 'orderId', $orderId );
		return $this->logicShop->getOrderPositions( $orderId, $extended );
	}

	/**
	 *	@param		int|string		$positionId
	 *	@param		bool			$extended		Flag: extend by article entity
	 *	@return		Entity_Shop_Order_Position|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getOrderPosition( int|string $positionId, bool $extended = FALSE ): ?object
	{
//		return $this->modelOrderPosition->get( $positionId );
		return $this->logicShop->getOrderPosition( $positionId, $extended );
	}

	public function getOpenSessionOrder( string $sessionId ): array
	{
		$conditions	= [
			'session_id'		=> $sessionId,
			'status'			=> '< 2',
		];
		return $this->modelOrder->getAll( $conditions );
	}

	public function getShipping( bool $strict = TRUE ): ?Logic_Shop_Shipping
	{
		if( !$this->shipping && $strict )
			throw new RuntimeException( 'Shipping module is not installed' );
		return $this->shipping ?: NULL;
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		int|string		$shippingZoneId 		ID of Shipping Zone
	 *	@param		int|string		$shippingGradeId 		ID of Shipping Grade
	 *	@return		string
	 */
	public function getShippingPrice( int|string $shippingZoneId, int|string $shippingGradeId ): string
	{
		return $this->getShipping()->getPrice( $shippingZoneId, $shippingGradeId );
	}

	/**
	 *	Returns Shipping Zone ID of Country.
	 *	@access		public
	 *	@param		int|string		 $countryId		ID of Country
	 *	@return		integer|NULL
	 *	@todo		rename to getShippingZoneOfCountryId and change behaviour
	 */
	public function getShippingZoneId( int|string $countryId ): ?int
	{
		return $this->getShipping()->getZoneId( $countryId );
	}

	/**
	 *	Change order position status.
	 *	@access		public
	 *	@param		integer|string		$positionId		Order position ID
	 *	@param		integer|string		$status			Status to set
	 *	@return		integer				1: order changed, 0: nothing changed
	 */
	public function setOrderPositionStatus( int|string $positionId, int|string $status ): int
	{
//		return $this->modelOrderPosition->edit( $positionId, ['status' => $status] );
		return $this->logicShop->setOrderPositionStatus( $positionId, (int) $status );
	}

	/**
	 *	Change order status.
	 *	@access		public
	 *	@param		integer|string	$orderId		Order ID
	 *	@param		integer|string	$status			Status to set
	 *	@return		integer			1: order changed, 0: nothing changed
	 */
	public function setOrderStatus( int|string $orderId, int|string $status ): int
	{
		return $this->modelOrder->edit( $orderId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	Set shipping logic instance.
	 *	@access		public
	 *	@param		Logic_Shop_Shipping		$logic		Logic instance to set
	 *	@return		self
	 */
	public function setShipping( Logic_Shop_Shipping $logic ): self
	{
		$this->shipping	= $logic;
		return $this;
	}

	protected function __onInit(): void
	{
		$this->logicShop			= new Logic_ShopResource( $this->env );
		$this->modelAddress			= new Model_Address( $this->env );
		$this->modelOrder			= new Model_Shop_Order( $this->env );
//		$this->modelOrderPosition	= new Model_Shop_Order_Position( $this->env );
		if( !$this->env->hasModules() )
			$this->setShipping( new Logic_Shop_Shipping( $this->env ) );
	}
}
