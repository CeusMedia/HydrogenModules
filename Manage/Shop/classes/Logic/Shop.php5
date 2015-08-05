<?php
class Logic_Shop_Shipping extends CMF_Hydrogen_Environment_Resource_Logic{
	/**	@var		Model_Shop_Shipping_Country			$modelShippingCountry */
	protected $modelShippingCountry;
	/**	@var		Model_Shop_Shipping_Grade			$modelShippingGrade */
	protected $modelShippingGrade;
	/**	@var		Model_Shop_Shipping_Option			$modelShippingOption */
	protected $modelShippingOption;
	/**	@var		Model_Shop_Shipping_Price			$modelShippingPrice */
	protected $modelShippingPrice;
	/**	@var		Model_Shop_Shipping_Zone			$modelShippingZone */
	protected $modelShippingZone;

	protected function __onInit(){
		$this->modelShippingCountry	= new Model_Shop_Shipping_Country( $this->env );
		$this->modelShippingGrade	= new Model_Shop_Shipping_Grade( $this->env );
		$this->modelShippingOption	= new Model_Shop_Shipping_Option( $this->env );
		$this->modelShippingPrice	= new Model_Shop_Shipping_Price( $this->env );
		$this->modelShippingZone	= new Model_Shop_Shipping_Zone( $this->env );
	}

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		int		 $quantity		Quantity to ge Shipping Grade for
	 *	@return		int
	 *	@todo		remove method and its calls
	 */
	public function getGradeID( $quantity )
	{
		$model	= new Model_Shop_Shipping_Grade( $this->env );
		return array_shift( $model->getAll( $conditions, array ('quantity' => 'DESC' ) ) );
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		int		$shippingzone_id 		ID of Shipping Zone
	 *	@param		int		$shippinggrade_id 		ID of Shipping Grade
	 *	@return		string
	 *	@todo		remove method and its calls
	 */
	public function getPrice( $shippingzone_id, $shippinggrade_id )
	{
		$model		= new Model_Shop_Shipping_Price();
		$conditions	= array( 'shippingzone_id' => $shippingZoneId, 'shippinggrade_id' => $shippingGradeId );
		$data		= $model->getByIndices( $conditions );
		if( $data )
			return $data->price;
		return NULL;
	}

	/**
	 *	Alias for getShippingZoneId.
	 *	@param		integer		$country_id
	 *	@return		integer|NULL
	 *	@todo		remove method and its calls
	 */
	public function getZoneId( $country_id ){
		$model	= new Model_Shop_Shipping_Country();
		$data	= $model->getByIndex( 'country_id', $countryId );
		if( $data )
			return $data->shippingzone_id;
		return NULL;
	}
}

class Logic_Shop extends CMF_Hydrogen_Environment_Resource_Logic{

	/**	@var		Model_Shop_Customer			$modelCustomer */
	protected $modelCustomer;

	/**	@var		Model_Shop_Order			$modelOrder */
	protected $modelOrder;

	/**	@var		Model_Shop_Order_Position	$modelOrderPosition */
	protected $modelOrderPosition;

	/**	@var		Logic_Shop_Shipping			$shipping			Shipping logic if module is installed */
	protected $shipping;

	protected function __onInit(){
		$this->modelCustomer		= new Model_Shop_Customer( $this->env );
		$this->modelOrder			= new Model_Shop_Order( $this->env );
		$this->modelOrderPosition	= new Model_Shop_Order_Position( $this->env );
		if( !$this->env->hasModules( 'Shop_Shipping' ) )
			$this->shipping	= new Logic_Shop_Shipping( $env );
	}

	public function countOrders( $conditions ){
		return $this->modelOrder->count( $conditions );
	}

	public function getCustomer( $customerId ){
		return $this->modelCustomer->get( $customerId );
	}

	public function getCustomers( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelCustomer->getAll( $conditions, $orders, $limits );
	}

	public function getOrder( $orderId, $extended = FALSE ){
		$order	= $this->modelOrder->get( $orderId );
		if( $order && $extended ){
			$order->customer	= $this->getCustomer( $order->customerId );
			$order->positions	= $this->getOrderPositions( $orderId );
		}
		return $order;
	}

	public function getOrders( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelOrder->getAll( $conditions, $orders, $limits );
	}

	public function getOrderPositions( $orderId ){
		return $this->modelOrderPosition->getAllByIndex( 'orderId', $orderId );
	}

	public function getOrderPosition( $positionId ){
		return $this->modelOrderPosition->get( $positionId );
	}

	public function getOpenSessionOrder( $sessionId ){
		$conditions	= array(
			'session_id'		=> $sessionId,
			'status'			=> '<2',
		);
		return $this->modelOrder->getAll( $conditions );
	}

	public function getShipping( $strict = TRUE ){
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
	public function getShippingGradeIdByQuantity( $quantity ){
		return $this->getShipping()->getShippingGradeIdByQuantity( $quantity );
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		integer		$shippingZoneId 		ID of Shipping Zone
	 *	@param		integer		$shippingGradeId 		ID of Shipping Grade
	 *	@return		string
	 */
	public function getShippingPrice( $shippingZoneId, $shippingGradeId ){
		return $this->getShipping()->getShippingPrice( $shippingzone_id, $shippinggrade_id );
	}

	/**
	 *	Returns Shipping Zone ID of Country.
	 *	@access		public
	 *	@param		integer		 $countryId		ID of Country
	 *	@return		integer|NULL
	 *	@todo		rename to getShippingZoneOfCountryId and change behaviour
	 */
	public function getShippingZoneId( $countryId ){
		return $this->getShipping()->getShippingZoneId( $country_id );
	}

	public function setOrderPositionStatus( $positionId, $status ){
		return $this->modelOrderPosition->edit( $positionId, array( 'status' => (int)$status ) );
	}

	public function setOrderStatus( $orderId, $status ){
		return $this->modelOrder->edit( $orderId, array( 'status' => (int)$status ) );
	}

	public function setShipping( CMF_Hydrogen_Environment_Resource_Logic $logic ){
		$this->shipping	= $logic;
	}
}
?>
