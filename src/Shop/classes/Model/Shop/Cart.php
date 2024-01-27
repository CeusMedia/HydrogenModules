<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

class Model_Shop_Cart
{
	const CUSTOMER_MODE_UNKNOWN		= 0;
	const CUSTOMER_MODE_GUEST		= 1;
	const CUSTOMER_MODE_ACCOUNT		= 2;

	const CUSTOMER_MODES			= [
		self::CUSTOMER_MODE_UNKNOWN,
		self::CUSTOMER_MODE_GUEST,
		self::CUSTOMER_MODE_ACCOUNT,
	];

	protected Environment $env;
	protected Dictionary $session;
	/**	@var	Logic_ShopBridge		$brige */
	protected Logic_ShopBridge $bridge;
	protected Model_Shop_Order $modelOrder;
	protected Model_Shop_Order_Position $modelPosition;
	protected Dictionary $data;
	protected bool $taxIncluded;
	protected string $defaultCurrency;

	protected array $ignoreOnUpdate		= [
		'customerMode',
		'price',
		'priceTaxed',
	];

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env				= $env;
		$this->session			= $env->getSession();
		$this->bridge			= new Logic_ShopBridge( $env );
		$this->modelOrder		= new Model_Shop_Order( $env );
		$this->modelPosition	= new Model_Shop_Order_Position( $env );
		$this->taxIncluded		= $env->getConfig()->get( 'module.shop.tax.included' );
		$this->defaultCurrency	= $env->getConfig()->get( 'module.shop.price.currency' );

		$data	= $this->session->get( 'shop_cart' );
		if( !is_array( $data ) ){
			$this->createEmpty();
		}
		else{
			$this->data	= new Dictionary( $data );
		}
		if( $this->data->get( 'orderId' ) )
			$this->loadOrder();

		if( !is_array( $this->data->get( 'positions' ) ) )
			$this->set( 'positions', [] );

	}

	public function get( string $key )
	{
		return $this->data->get( $key );
	}

	public function getAll(): array
	{
		return $this->data->getAll();
	}

	public function has( string $key ): bool
	{
		return $this->data->has( $key );
	}

	public function loadOrder( $orderId = NULL )
	{
		$orderId	= $orderId ?: $this->data->get( 'orderId' );
		if( $orderId ){
			$order	= $this->modelOrder->get( $orderId );
			if( $order ){
				$this->data->set( 'userId', $order->userId );
				$this->data->set( 'orderStatus', $order->status );
				$this->data->set( 'paymentMethod', $order->paymentMethod );
//				$this->data->set( 'options', $order->options );
				$this->data->set( 'price', $order->price );
				$this->data->set( 'priceTaxed', $order->priceTaxed );
				$positions	= [];
				foreach( $this->modelPosition->getAllByIndex( 'orderId', $orderId ) as $item ){
					$source		= $this->bridge->getBridgeObject( (int) $item->bridgeId );
					$article	= $source->get( $item->articleId, $item->quantity );
					$positions[$item->articleId]	= (object) [
						'bridgeId'		=> $item->bridgeId,
						'articleId'		=> $item->articleId,
						'quantity'		=> $item->quantity,
						'article'		=> $article,
					];
				}
				$this->data->set( 'positions', $positions );
			}
		}
	}

	public function getTotal(): float
	{
		$total	= .0;
		foreach( $this->data->get( 'positions', [] ) as $position ){
			$total	+= $position->article->price->all;
			if( !$this->taxIncluded )
				$total	+= $position->article->tax->all;
		}
		return $total;
	}

	public function releaseOrder()
	{
		if( $this->data->get( 'orderId' ) )
			$this->createEmpty();
	}

	/**
	 *	@param		string		$key
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function remove( string $key ): bool
	{
		$this->data->remove( $key );
		if( $this->data->get( 'orderId' ) )
			if( !in_array( $key, $this->ignoreOnUpdate ) )
				$this->saveOrder();
		return $this->session->set( 'shop_cart', $this->data->getAll() );
	}

	/**
	 *	Saves cart from session to order in database.
	 *	@access		public
	 *	@return		string		Order ID
	 *	@throws		ReflectionException
	 */
	public function saveOrder(): string
	{
		$orderId	= $this->data->get( 'orderId' );
		if( $orderId && $this->modelOrder->get( $orderId ) ){
			return $this->updateOrder( $orderId );
		}
		return $this->createOrder();
	}

	/**
	 *	@param		string		$key
	 *	@param		mixed		$value
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function set( string $key, $value ): bool
	{
		$this->data->set( $key, $value );
		if( $this->data->get( 'orderId' ) )
			if( !in_array( $key, $this->ignoreOnUpdate ) )
				$this->saveOrder();
		return $this->session->set( 'shop_cart', $this->data->getAll() );
	}

	/*  --  PROTECTED  --  */

	protected function createEmpty()
	{
		$this->data	= new Dictionary( [
			'orderStatus'		=> Model_Shop_Order::STATUS_NEW,
			'acceptRules'		=> FALSE,
			'paymentMethod'		=> NULL,
			'paymentId'			=> NULL,
			'currency'			=> $this->defaultCurrency,
			'positions'			=> [],
			'customer'			=> [],
			'customerMode'		=> Model_Shop_Cart::CUSTOMER_MODE_UNKNOWN,
		] );
		$this->session->set( 'shop_cart', $this->data->getAll() );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function createOrder(): string
	{
		$orderId	= $this->modelOrder->add( [
			'userId'		=> $this->data->get( 'userId' ),
			'status'		=> $this->data->get( 'orderStatus' ),
			'paymentMethod'	=> $this->data->get( 'paymentMethod' ),
//			'options'		=> $this->data->get( 'options' ),
			'price'			=> 0,
			'priceTaxed'	=> 0,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		] );

		foreach( $this->data->get( 'positions' ) as $item ){
			$source		= $this->bridge->getBridgeObject( (int) $item->bridgeId );
			$positionId	= $this->modelPosition->add( [
				'orderId'		=> $orderId,
				'bridgeId'		=> $item->bridgeId,
				'articleId'		=> $item->articleId,
				'status'		=> Model_Shop_Order_Position::STATUS_NEW,
				'quantity'		=> $item->quantity,
//				'currency'		=> $article->currency,
				'price'			=> 0,
				'priceTaxed'	=> 0,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			] );
		}
		$this->updateOrderPrices( $orderId );
		$this->set( 'orderId', $orderId );
		return $orderId;
	}

	/**
	 *	@param		string		$orderId
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function updateOrder( string $orderId ): string
	{
		$this->modelOrder->edit( $orderId, [
			'userId'		=> $this->data->get( 'userId' ),
			'status'		=> $this->data->get( 'orderStatus' ),
//			'options'		=> $this->data->get( 'options' ),
			'paymentMethod'	=> $this->data->get( 'paymentMethod' ),
			'modifiedAt'	=> time(),
		] );

		$relations	= $this->modelPosition->getAllByIndex( 'orderId', $orderId );
		foreach( $relations as $relation ){
			foreach( $this->data->get( 'positions' ) as $item ){
				if( $item->bridgeId == $relation->bridgeId )
					if( $item->articleId == $relation->articleId )
						continue 2;
			}
			$this->modelPosition->remove( $relation->positionId );
		}

		foreach( $this->data->get( 'positions' ) as $item ){
			$relation	= $this->modelPosition->getByIndices( [
				'orderId'	=> $orderId,
				'bridgeId'	=> $item->bridgeId,
				'articleId'	=> $item->articleId
			] );
			$source		= $this->bridge->getBridgeObject( (int) $item->bridgeId );
			$article	= $source->get( $item->articleId, $item->quantity );
			$price		= $article->price->one;
			$priceTaxed	= $article->price->one + $article->tax->one;
			if( $this->taxIncluded ){														//  tax already is included
				$price		= $article->price->one - $article->tax->one;					//  reduce by tax added by default
				$priceTaxed	= $article->price->one;											//  reduce by tax added by default
			}
			if( $relation ){
				if( $relation->quantity != $item->quantity ){
					$this->modelPosition->edit( $relation->positionId, [
						'quantity'		=> $item->quantity,
						'price'			=> $price,
						'priceTaxed'	=> $priceTaxed,
						'modifiedAt'	=> time(),
					] );
				}
			}
			else{
				$positionId	= $this->modelPosition->add( [
					'orderId'		=> $orderId,
					'bridgeId'		=> $item->bridgeId,
					'articleId'		=> $item->articleId,
					'status'		=> Model_Shop_Order_Position::STATUS_NEW,
//					'userId'		=> 0,
//					'size'			=> 0,
					'quantity'		=> $item->quantity,
					'currency'		=> $article->currency,
					'price'			=> $price,
					'priceTaxed'	=> $priceTaxed,
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				] );
			}
		}
		$this->updateOrderPrices( $orderId );
		return $orderId;
	}

	/**
	 *	@param		string		$orderId
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	protected function updateOrderPrices( string $orderId ): bool
	{
		$price			= 0;
		$priceTaxed		= 0;
		$logicShop		= new Logic_Shop( $this->env );
		$order			= $logicShop->getOrder( $orderId, TRUE );

		foreach( $order->positions as $position ){
			$price		+= $position->article->price->all;
			$priceTaxed	+= $position->article->price->all + $position->article->tax->all;
			if( $this->taxIncluded ){
				$price		-= $position->article->tax->all;
				$priceTaxed	-= $position->article->tax->all;
			}
		}

		//  --  SHIPPING  --  //
		if( $this->env->getModules()->has( 'Shop_Payment' ) ){
			$shipping	= $logicShop->getOrderShipping( $orderId );
			$price		+= $shipping->price;
			$priceTaxed	+= $shipping->priceTaxed;
		}

		//  --  PAYMENT  --  //
		if( $this->env->getModules()->has( 'Shop_Payment' ) ){
			$payment	= $logicShop->getOrderPaymentFees( $orderId );
			$price		+= $payment->price;
			$priceTaxed	+= $payment->priceTaxed;
		}

		//  --  OPTIONS  --  //
		// @todo implement!

		return (bool) $this->modelOrder->edit( $orderId, [
			'price'			=> $price,
			'priceTaxed'	=> $priceTaxed,
		] );
	}
}
