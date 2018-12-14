<?php
class Logic_Shop extends CMF_Hydrogen_Logic{

	/**	@var	Logic_ShopBridge			$bridge */
	protected $bridge;

	/**	@var	Model_Shop_Cart				$modelCart */
	protected $modelCart;

	/**	@var	Model_User					$modelUser */
	protected $modelUser;

	/**	@var	Model_Address				$modelAddress */
	protected $modelAddress;

	/**	@var	Model_Shop_Order			$modelOrder */
	protected $modelOrder;

	/**	@var	Model_Shop_Order_Position	$modelOrderPosition */
	protected $modelOrderPosition;

	/** @var	Alg_List_Dictionary			$moduleConfig */
	protected $moduleConfig;

	/**	@var	Logic_Shop_Shipping			$shipping			Instance of shipping logic if module is installed */
	protected $shipping;

	protected function __onInit(){
		$this->bridge				= new Logic_ShopBridge( $this->env );
		$this->modelUser			= new Model_User( $this->env );
		$this->modelAddress			= new Model_Address( $this->env );
		$this->modelCart			= new Model_Shop_Cart( $this->env );
		$this->modelOrder			= new Model_Shop_Order( $this->env );
		$this->modelOrderPosition	= new Model_Shop_Order_Position( $this->env );
		$this->moduleConfig			= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		if( $this->env->getModules()->has( 'Shop_Shipping' ) )
			$this->shipping		= new Logic_Shop_Shipping( $this->env );
	}

	public function calculateOrderTotalPrice( $orderId ){
		if( !$this->modelOrder->get( $orderId ) )													//  check if order exists
			throw new InvalidArgumentException( 'Invalid order ID' );								//  else quit with exception

		$sum	= 0;																				//  prepare total price sum
		foreach( $this->modelOrderPosition->getAllByIndex( 'orderId', $orderId ) as $position ){	//  iterate order positions
			$source		= $this->bridge->getBridgeObject( (int) $position->bridgeId );				//  get bridge source of article
			$article	= $source->get( $position->articleId, $position->quantity );				//  get article data
			$sum		+= (float) $article->price->all;											//  add price of position
		}
		return $sum;																				//  return total price sum
	}

	public function countArticleInCart( $bridgeId, $articleId ){
		if( is_array( ( $positions = $this->modelCart->get( 'positions' ) ) ) )
			foreach( $positions as $position )
				if( $position->bridgeId == $bridgeId && $position->articleId == $articleId )
					return $position->quantity;
		return 0;
	}

	public function countArticlesInCart( $countEach = FALSE ){
		$number	= 0;
		if( is_array( ( $positions = $this->modelCart->get( 'positions' ) ) ) ){
			if( !$countEach )
				return count( $positions );
			foreach( $positions as $position )
				$number	+= $position->quantity;
		}
		return $number;
	}

	public function countOrders( $conditions ){
		return $this->modelOrder->count( $conditions );
	}

	public function getOrderCustomer( $orderId ){
		$order	= $this->modelOrder->get( $orderId );
		if( !$order )
			throw new RangeException( 'Invalid order ID: '.$orderId );
		if( $order->userId )
			return $this->getAccountCustomer( $order->userId );
		else if( $order->customerId )
			return $this->getGuestCustomer( $order->customerId );
		throw new Exception( 'No user or customer assigned to order' );
	}

	public function getGuestCustomer( $customerId ){
		$model		= new Model_Shop_Customer( $this->env );
		$customer	= $model->get( $customerId );
		if( !$customer )
			throw new RangeException( 'Invalid customer ID: '.$customerId );
		$customer->addressBilling	= $this->modelAddress->getByIndices( array(
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_BILLING,
		) );
		$customer->addressDelivery	= $this->modelAddress->getByIndices( array(
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		) );
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

	public function getAccountCustomer( $userId ){
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

	public function getOrder( $orderId, $extended = FALSE ){
		$order	= $this->modelOrder->get( $orderId );
		if( $order && $extended ){
			$order->customer	= $this->getOrderCustomer( $orderId );
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

	/**
	 *	@deprecated		use Model_Shop_Cart instead
	 */
/*	public function getOpenSessionOrder( $sessionId ){
		$conditions	= array(
			'sessionId'		=> $sessionId,
			'status'		=> '<2',
		);
		return $this->modelOrder->getAll( $conditions );
	}*/

	public function getShipping( $strict = TRUE ){
		if( !$this->shipping && $strict )
			throw new RuntimeException( "Shipping module is not installed" );
		return $this->shipping ? $this->shipping : NULL;
	}

	/**
	 *	Returns Shipping Zone ID of Country.
	 *	@access		public
	 *	@param		integer		 $countryId		ID of Country
	 *	@return		integer|NULL
	 *	@todo		rename to getShippingZoneOfCountryId and change behaviour
	 */
	public function getShippingZoneId( $countryId ){
		return $this->getShipping()->getZoneId( $countryId );
	}

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		integer		 $quantity		Quantity to ge Shipping Grade for
	 *	@return		int
	 */
	public function getShippingGradeIdByQuantity( $quantity )
	{
		return $this->getShipping()->getGradeID( $shippingZoneId, $shippingGradeId );
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
		return $this->getShipping()->getPrice( $shippingZoneId, $shippingGradeId );
	}

	public function hasArticleInCart( $bridgeId, $articleId ){
		return $this->countArticleInCart( $bridgeId, $articleId ) > 0;
	}

	/**
	 * @deprecated	use Model_Shop_Cart::set instead
	 */
	public function setOrderPaymentMethod( $orderId, $paymentMethod ){
		if( $orderId ){
			return $this->modelOrder->edit( $orderId, array(
				'paymentMethod'	=> $paymentMethod,
				'modifiedAt'	=> time(),
			) );
		}
		$this->modelCart->set( 'paymentMethod', $paymentMethod );
	}

	/**
	 * @deprecated	use Model_Shop_Cart::set instead
	 */
	public function setOrderPaymentId( $orderId, $paymentId ){
		if( $orderId ){
			return $this->modelOrder->edit( $orderId, array(
				'paymentId'		=> $paymentId,
				'modifiedAt'	=> time(),
			) );
		}
	}

	public function setOrderPositionStatus( $positionId, $status ){
		return $this->modelOrderPosition->edit( $positionId, array(
			'status'		=> (int) $status,
			'modifiedAt'	=> time(),
		) );
	}

	public function setOrderUserId( $orderId, $userId ){
		return $this->modelOrder->edit( $orderId, array(
			'userId'		=> $userId,
			'modifiedAt'	=> time(),
		) );
	}

	public function setOrderStatus( $orderId, $status ){
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
		return $this->modelOrder->edit( $orderId, array(
			'status'		=> (int) $status,
			'modifiedAt'	=> time(),
		) );
	}

	public function setShipping( $logic ){
		if( !( $logic instanceof CMF_Hydrogen_Environment_Resource_Logic ) )
			throw new RuntimeException( 'Invalid logic object (must extend CMF_Hydrogen_Environment_Resource_Logic)' );
		$this->shipping		= $logic;
	}
}
?>
