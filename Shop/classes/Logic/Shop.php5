<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Shop extends Logic
{
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

	/** @var	Dictionary					$moduleConfig */
	protected $moduleConfig;

	/**	@var	Logic_Shop_Shipping|NULL	$shipping			Instance of shipping logic if module is installed */
	protected $logicShipping;

	protected $useShipping					= FALSE;

	/**
	 * @deprecated	get Model_Shop_Order::priceTaxed instead
	 */
	public function calculateOrderTotalPrice( $orderId )
	{
		$order	= $this->modelOrder->get( $orderId );
		if( !$order )
			throw new InvalidArgumentException( 'Invalid order ID' );								//  else quit with exception
		return $order->priceTaxed;
	}

	public function countArticleInCart( $bridgeId, $articleId )
	{
		if( is_array( ( $positions = $this->modelCart->get( 'positions' ) ) ) )
			foreach( $positions as $position )
				if( $position->bridgeId == $bridgeId && $position->articleId == $articleId )
					return $position->quantity;
		return 0;
	}

	public function countArticlesInCart( bool $countEach = FALSE )
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

	public function countOrders( array $conditions )
	{
		return $this->modelOrder->count( $conditions );
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

	public function getBillingAddressFromCart()
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

	public function getDeliveryAddressFromCart()
	{
		$address		= NULL;
		if( $this->modelCart->get( 'userId' ) ){
			$address	= $this->modelAddress->getByIndices( array(
				'relationId'	=> $this->modelCart->get( 'userId' ),
				'relationType'	=> 'user',
				'type'			=> Model_Address::TYPE_DELIVERY,
			) );
		}
		return $address;
	}

	/**
	 *	@deprecated
	 */
	public function getGuestCustomer( $customerId )
	{
		throw new RuntimeException( 'Method Logic_Shop::getGuestCustomer is deprecated' );
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

	public function getOrder( $orderId, bool $extended = FALSE )
	{
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

	public function getOrderCustomer( $orderId )
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
	public function getOrderOptions( $orderId )
	{
		return (object) [];
	}

	public function getOrderPosition( $positionId, bool $extended = FALSE )
	{
		$position	= $this->modelOrderPosition->get( $positionId );
		if( $extended ){
			$source		= $this->bridge->getBridgeObject( (int) $position->bridgeId );				//  get bridge source of article
			$position->article	= $source->get( $position->articleId, $position->quantity );		//  get article data
		}
		return $position;
	}

	public function getOrderPositions( $orderId, $extended = FALSE )
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

	public function getOrders( array $conditions = [], array $orders = [], array$limits = [] ): array
	{
		return $this->modelOrder->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		make tax rate configurable - store rate on shipping price or service
	 */
	public function getOrderShipping( $orderId )
	{
		$taxIncluded	= $this->env->getConfig()->get( 'module.shop.tax.included' );
		$taxRate		= 19;				//  @todo: make configurable
		$price			= 0;
		$priceTaxed		= 0;
		$tax			= 0;
		if( $this->useShipping ){
			$customer		= $this->getOrderCustomer( $orderId );
			if( $customer && $customer->addressDelivery ){
				$weight			= 0;
				$positions		= $this->getOrderPositions( $orderId, TRUE );
				foreach( $positions as $position )
					$weight	+= $position->article->weight->all;
				$price	= $this->logicShipping->getPriceFromCountryCodeAndWeight(
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

	public function getOrderTaxes( $orderId )
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
/*	public function getOpenSessionOrder( $sessionId )
	{
		$conditions	= array(
			'sessionId'		=> $sessionId,
			'status'		=> '< 2',
		);
		return $this->modelOrder->getAll( $conditions );
	}*/

	/**
	 *	@deprecated
	 */
	public function getShipping( $strict = TRUE )
	{
		Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Shop' )->version )
			->setExceptionVersion( '0.8.3' )
			->message( 'getShipping is deprecated' );
		if( !$this->useShipping && $strict )
			throw new RuntimeException( "Shipping module is not installed" );
		return $this->useShipping ? $this->useShipping : NULL;
	}

	/**
	 *	Returns Shipping Zone ID of Country.
	 *	@access		public
	 *	@param		integer		 $countryId		ID of Country
	 *	@return		integer|NULL
	 *	@todo		rename to getShippingZoneOfCountryId and change behaviour
	 *	@deprecated
	 */
	public function getShippingZoneId( $countryId )
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

	public function hasArticleInCart( $bridgeId, $articleId )
	{
		return $this->countArticleInCart( $bridgeId, $articleId ) > 0;
	}

	public function setOrderPaymentId( $orderId, $paymentId )
	{
		if( $orderId ){
			return $this->modelOrder->edit( $orderId, array(
				'paymentId'		=> $paymentId,
				'modifiedAt'	=> time(),
			) );
		}
	}

	public function setOrderPaymentMethod( $orderId, $paymentMethod ){
		if( $orderId ){
			return $this->modelOrder->edit( $orderId, array(
				'paymentMethod'	=> $paymentMethod,
				'modifiedAt'	=> time(),
			) );
		}
	}

	public function setOrderPositionStatus( $positionId, $status )
	{
		return $this->modelOrderPosition->edit( $positionId, array(
			'status'		=> (int) $status,
			'modifiedAt'	=> time(),
		) );
	}

	public function setOrderUserId( $orderId, $userId )
	{
		return $this->modelOrder->edit( $orderId, array(
			'userId'		=> $userId,
			'modifiedAt'	=> time(),
		) );
	}

	public function setOrderStatus( $orderId, $status )
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
		return $this->modelOrder->edit( $orderId, array(
			'status'		=> (int) $status,
			'modifiedAt'	=> time(),
		) );
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

	protected function __onInit()
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
	}
}
