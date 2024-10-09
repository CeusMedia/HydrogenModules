<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_ShopManager extends Logic
{
	/**	@var		Model_User					$modelUser */
	protected Model_User $modelUser;

	/**	@var		Model_Address				$modelAddress */
	protected Model_Address $modelAddress;

	/**	@var		Model_Shop_Order			$modelOrder */
	protected Model_Shop_Order $modelOrder;

	/**	@var		Model_Shop_Order_Position	$modelOrderPosition */
	protected Model_Shop_Order_Position $modelOrderPosition;

	/**	@var		?Logic_Shop_Shipping			$shipping			Shipping logic if module is installed */
	protected ?Logic_Shop_Shipping $shipping	= NULL;

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

	public function getAccountCustomer( int|string $userId ): object
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

	public function getOrder( int|string $orderId, bool $extended = FALSE ): object
	{
		$order	= $this->modelOrder->get( $orderId );
		if( $order && $extended ){
			$order->customer	= $this->getOrderCustomer( $orderId );
			$order->positions	= $this->getOrderPositions( $orderId );
		}
		return $order;
	}

	public function getOrders( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelOrder->getAll( $conditions, $orders, $limits );
	}

	public function getOrderPositions( int|string $orderId ): array
	{
		return $this->modelOrderPosition->getAllByIndex( 'orderId', $orderId );
	}

	public function getOrderPosition( int|string $positionId ): ?object
	{
		return $this->modelOrderPosition->get( $positionId );
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
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		integer		$quantity		Quantity to ge Shipping Grade for
	 *	@return		int
	 */
	public function getShippingGradeIdByQuantity( int $quantity ): int
	{
		return $this->getShipping()?->getGradeID( $quantity );
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
		return $this->modelOrderPosition->edit( $positionId, ['status' => $status] );
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
		return $this->modelOrder->edit( $orderId, ['status' => $status] );
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
		$this->modelUser			= new Model_User( $this->env );
		$this->modelAddress			= new Model_Address( $this->env );
		$this->modelOrder			= new Model_Shop_Order( $this->env );
		$this->modelOrderPosition	= new Model_Shop_Order_Position( $this->env );
		if( !$this->env->hasModules() )
			$this->setShipping( new Logic_Shop_Shipping( $this->env ) );
	}
}
