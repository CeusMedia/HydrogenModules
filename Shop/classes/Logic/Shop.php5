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

	/**	@var	Logic_Shop_Shipping|NULL	$shipping			Instance of shipping logic if module is installed */
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

		$priceShipping	= 0;
		if( $this->getShipping())
		if( $this->env->getModules()->has( 'Shop_Shipping' ) ){
			$logicShipping	= new Logic_Shop_Shipping( $this->env );
			if( $this->deliveryAddress ){
				$priceShipping	= $logicShipping->getPriceFromCountryCodeAndWeight(
					$this->deliveryAddress->country,
					$totalWeight
				);
				$rows[]	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', '&nbsp;' ),
					UI_HTML_Tag::create( 'td', $words->labelShipping, array( 'class' => 'autocut' ) ),
					UI_HTML_Tag::create( 'td', '&nbsp;', array( 'class' => 'column-cart-quantity' ) ),
					UI_HTML_Tag::create( 'td', $this->formatPrice( $priceShipping ), array( 'class' => 'price' ) )
				) );
			}
		}
		$priceTotal		= $totalPrice + $priceShipping;


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

	public function getDeliveryAddressFromCart(){
		$address		= NULL;
		$addressUserId	= 0;
		$customerMode	= $this->modelCart->get( 'customerMode' );
		if( $customerMode === Model_Shop_CART::CUSTOMER_MODE_ACCOUNT ){
			$addressUserId	= $this->modelCart->get( 'userId' );
			$relationType	= 'user';
		}
		else if( $customerMode === Model_Shop_CART::CUSTOMER_MODE_GUEST ){
			$addressUserId	= $this->modelCart->get( 'customerId' );
			$relationType	= 'customer';
		}
		if( $addressUserId ){
			$address	= $this->modelAddress->getByIndices( array(
				'relationId'	=> $addressUserId,
				'relationType'	=> $relationType,
				'type'			=> Model_Address::TYPE_DELIVERY,
			) );
		}
		return $address;
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

	public function getOrder( $orderId, $extended = FALSE ){
		$order	= $this->modelOrder->get( $orderId );
		if( $order && $extended ){
			$order->customer	= $this->getOrderCustomer( $orderId );
			$order->positions	= $this->getOrderPositions( $orderId, TRUE );
			$order->shipping	= $this->getOrderShipping( $orderId );
			$order->options		= $this->getOrderOptions( $orderId );
			$order->taxes		= $this->getOrderTaxes( $orderId );
		}
		return $order;
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

	/**
	 *	@todo		to be implemented: use Model_Shop_Shipping_Option
	 */
	public function getOrderOptions( $orderId ){
		return (object) array();
	}

	public function getOrderPosition( $positionId, $extended = FALSE ){
		$position	= $this->modelOrderPosition->get( $positionId );
		if( $extended ){
			$source		= $this->bridge->getBridgeObject( (int) $position->bridgeId );				//  get bridge source of article
			$position->article	= $source->get( $position->articleId, $position->quantity );		//  get article data
		}
		return $position;
	}

	public function getOrderPositions( $orderId, $extended = FALSE ){
		$positions	= $this->modelOrderPosition->getAllByIndex( 'orderId', $orderId );
		if( $extended ){
			foreach( $positions as $position ){
				$source		= $this->bridge->getBridgeObject( (int) $position->bridgeId );			//  get bridge source of article
				$position->article	= $source->get( $position->articleId, $position->quantity );	//  get article data
			}
		}
		return $positions;
	}

	public function getOrders( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelOrder->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		make tax rate configurable - store rate on shipping price or service
	 */
	public function getOrderShipping( $orderId ){
		$taxIncluded	= $this->env->getConfig()->get( 'module.shop.tax.included' );
		$taxRate		= 19;				//  @todo: make configurable
		$price			= 0;
		$priceTaxed		= 0;
		if( $this->env->getModules()->has( 'Shop_Shipping' ) ){
			$logicShipping	= new Logic_Shop_Shipping( $this->env );
			$customer		= $this->getOrderCustomer( $orderId );
			if( $customer && $customer->addressDelivery ){
				$weight			= 0;
				$positions		= $this->getOrderPositions( $orderId, TRUE );
				foreach( $positions as $position )
					$weight	+= $position->article->weight->all;
				$price	= $logicShipping->getPriceFromCountryCodeAndWeight(
					$customer->addressDelivery->country,
					$weight
				);
				$tax			= $price * ( $taxRate / 100 );
				$priceTaxed		+= $tax;
				if( $taxIncluded ){
					$priceTaxed	= $price;
					$price		-= $tax;
				}
			}
		}
		return (object) array(
			'price'			=> $price,
			'tax'			=> $tax,
			'priceTaxed'	=> $priceTaxed,
			'taxRate'		=> $taxRate,
		);
	}

	public function getOrderTaxes( $orderId ){
		$taxes		= array();
		$sum		= 0;
		$positions	= $this->getOrderPositions( $orderId, TRUE );
		foreach( $positions as $position ){
			if( !isset( $taxes[$position->article->tax->rate] ) )
				$taxes[$position->article->tax->rate]	= 0;
			$taxes[$position->article->tax->rate]	+= $position->article->tax->all;
			$sum	+= $position->article->tax->all;
		}
		$shipping	= $this->getOrderShipping( $orderId );
		if( !isset( $taxes[$shipping->taxRate] ) )
			$taxes[$shipping->taxRate]	= 0;
		$taxes[$shipping->taxRate]	+= $shipping->tax;
		$sum	+= $shipping->tax;
		$taxes['total']	= $sum;
		return $taxes;
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

	/**
	 *	@deprecated
	 */
	public function getShipping( $strict = TRUE ){
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'getShipping is deprecated' );
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
	 *	@deprecated
	 */
	public function getShippingZoneId( $countryId ){
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
	public function getShippingGradeIdByQuantity( $quantity )
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
	 *	@param		integer		$shippingZoneId 		ID of Shipping Zone
	 *	@param		integer		$shippingGradeId 		ID of Shipping Grade
	 *	@return		string
	 *	@deprecated
	 */
	public function getShippingPrice( $shippingZoneId, $shippingGradeId )
	{
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'getShippingPrice is deprecated' );
		return $this->getShipping()->getPrice( $shippingZoneId, $shippingGradeId );
	}

	public function hasArticleInCart( $bridgeId, $articleId ){
		return $this->countArticleInCart( $bridgeId, $articleId ) > 0;
	}

	/**
	 * @deprecated	use Model_Shop_Cart::set instead
	 */
	public function setOrderPaymentId( $orderId, $paymentId ){
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'setOrderPaymentId is deprecated - use Model_Shop_Cart::set instead' );
		if( $orderId ){
			return $this->modelOrder->edit( $orderId, array(
				'paymentId'		=> $paymentId,
				'modifiedAt'	=> time(),
			) );
		}
	}

	/**
	 * @deprecated	use Model_Shop_Cart::set instead
	 */
	public function setOrderPaymentMethod( $orderId, $paymentMethod ){
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'setOrderPaymentMethod is deprecated - use Model_Shop_Cart::set instead' );
		if( $orderId ){
			return $this->modelOrder->edit( $orderId, array(
				'paymentMethod'	=> $paymentMethod,
				'modifiedAt'	=> time(),
			) );
		}
		$this->modelCart->set( 'paymentMethod', $paymentMethod );
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

	/**
	 *	@deprecated
	 */
	public function setShipping( $logic ){
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'setShipping is deprecated' );
		if( !( $logic instanceof CMF_Hydrogen_Logic ) )
			throw new RuntimeException( 'Invalid logic object (must extend CMF_Hydrogen_Logic)' );
		$this->shipping		= $logic;
	}
}
?>
