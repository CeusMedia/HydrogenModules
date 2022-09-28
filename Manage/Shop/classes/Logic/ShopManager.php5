<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_ShopManager extends Logic
{
	/**	@var		Model_User					$modelUser */
	protected $modelUser;

	/**	@var		Model_Address				$modelAddress */
	protected $modelAddress;

	/**	@var		Model_Shop_Order			$modelOrder */
	protected $modelOrder;

	/**	@var		Model_Shop_Order_Position	$modelOrderPosition */
	protected $modelOrderPosition;

	/**	@var		Logic_Shop_Shipping			$shipping			Shipping logic if module is installed */
	protected $shipping;

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

	public function getOrderCustomer( $orderId )
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

	public function getAccountCustomer( $userId )
	{
		$user	= $this->modelUser->get( $userId );
		if( !$user )
			throw new RangeException( 'No customer found for user ID '.$userId );
		$user->addressBilling	= $this->modelAddress->getByIndices( array(
			'relationType'	=> 'user',
			'relationId'	=> $userId,
			'type'			=> Model_Address::TYPE_BILLING,
		) );
		$user->addressDelivery	= $this->modelAddress->getByIndices( array(
			'relationType'	=> 'user',
			'relationId'	=> $userId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		) );
		return $user;
	}

	/**
	 *	@deprecated
	 */
	public function getGuestCustomer( $customerId )
	{
//		throw new RuntimeException( 'Method Logic_ShopManager::getGuestCustomer is deprecated' );
		$model	= new Model_Shop_Customer( $this->env );
		$user	= $model->get( $customerId );
		if( !$user )
			throw new RangeException( 'Invalid customer ID: '.$customerId );
		$user->addressBilling	= $this->modelAddress->getByIndices( array(
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_BILLING,
		) );
		$user->addressDelivery	= $this->modelAddress->getByIndices( array(
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		) );
		return $user;
	}

	public function getCustomers( $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return array();//$this->modelCustomer->getAll( $conditions, $orders, $limits );
	}

	public function getOrder( $orderId, bool $extended = FALSE )
	{
		$order	= $this->modelOrder->get( $orderId );
		if( $order && $extended ){
			$order->customer	= $this->getOrderCustomer( $orderId );
			$order->positions	= $this->getOrderPositions( $orderId );
		}
		return $order;
	}

	public function getOrders( $conditions = [], array $orders = [], array $limits = [] )
	{
		return $this->modelOrder->getAll( $conditions, $orders, $limits );
	}

	public function getOrderPositions( $orderId ): array
	{
		return $this->modelOrderPosition->getAllByIndex( 'orderId', $orderId );
	}

	public function getOrderPosition( $positionId )
	{
		return $this->modelOrderPosition->get( $positionId );
	}

	public function getOpenSessionOrder( $sessionId ): array
	{
		$conditions	= array(
			'session_id'		=> $sessionId,
			'status'			=> '< 2',
		);
		return $this->modelOrder->getAll( $conditions );
	}

	public function getShipping( bool $strict = TRUE )
	{
		if( !$this->shipping && $strict )
			throw new RuntimeException( 'Shipping module is not installed' );
		return $this->shipping ? $this->shipping : NULL;
	}

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		integer		 $quantity		Quantity to ge Shipping Grade for
	 *	@return		int
	 */
	public function getShippingGradeIdByQuantity( $quantity )
	{
		return $this->getShipping()->getShippingGradeIdByQuantity( $quantity );
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		integer		$shippingZoneId 		ID of Shipping Zone
	 *	@param		integer		$shippingGradeId 		ID of Shipping Grade
	 *	@return		string
	 */
	public function getShippingPrice( $shippingZoneId, $shippingGradeId )
	{
		return $this->getShipping()->getShippingPrice( $shippingZoneId, $shippingGradeId );
	}

	/**
	 *	Returns Shipping Zone ID of Country.
	 *	@access		public
	 *	@param		integer		 $countryId		ID of Country
	 *	@return		integer|NULL
	 *	@todo		rename to getShippingZoneOfCountryId and change behaviour
	 */
	public function getShippingZoneId( $countryId )
	{
		return $this->getShipping()->getShippingZoneId( $countryId );
	}

	/**
	 *	Change order position status.
	 *	@access		public
	 *	@param		integer|string	$orderId		Order position ID
	 *	@param		integer|string	$status			Status to set
	 *	@return		integer			1: order changed, 0: nothing changed
	 */
	public function setOrderPositionStatus( $positionId, $status )
	{
		return $this->modelOrderPosition->edit( $positionId, array( 'status' => (int) $status ) );
	}

	/**
	 *	Change order status.
	 *	@access		public
	 *	@param		integer|string	$orderId		Order ID
	 *	@param		integer|string	$status			Status to set
	 *	@return		integer			1: order changed, 0: nothing changed
	 */
	public function setOrderStatus( $orderId, $status )
	{
		return $this->modelOrder->edit( $orderId, array( 'status' => (int) $status ) );
	}

	/**
	 *	Set shipping logic instance.
	 *	@access		public
	 *	@param		Logic		$logic		Logic instance to set
	 *	@return		self
	 */
	public function setShipping(Logic $logic ): self
	{
		$this->shipping	= $logic;
		return $this;
	}

	protected function __onInit()
	{
		$this->modelUser			= new Model_User( $this->env );
		$this->modelAddress			= new Model_Address( $this->env );
		$this->modelOrder			= new Model_Shop_Order( $this->env );
		$this->modelOrderPosition	= new Model_Shop_Order_Position( $this->env );
		if( !$this->env->hasModules( 'Shop_Shipping' ) )
			$this->shipping	= new Logic_Shop_Shipping( $env );
	}
}
