<?php
class Model_Shop_Cart{

	/**	@var	Logic_ShopBridge		$brige */
	protected $bridge;

	protected $ignoreOnUpdate		= array(
		'customerMode',
		'price',
		'priceTaxed',
	);

	public function __construct( $env ){
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
			$this->data	= new ADT_List_Dictionary( $data );
		}
		if( $this->data->get( 'orderId' ) )
			$this->loadOrder();

		if( !is_array( $this->data->get( 'positions' ) ) )
			$this->set( 'positions', array() );

	}

	protected function createEmpty(){
		$this->data	= new ADT_List_Dictionary( array(
			'orderStatus'		=> Model_Shop_Order::STATUS_NEW,
			'acceptRules'		=> FALSE,
			'paymentMethod'		=> NULL,
			'paymentId'			=> NULL,
			'currency'			=> $this->defaultCurrency,
			'positions'			=> array(),
			'customer'			=> array(),
			'customerId'		=> 0,
			'customerMode'		=> Model_Shop_Order::CUSTOMER_MODE_UNKNOWN,
		) );
		$this->session->set( 'shop_cart', $this->data->getAll() );
	}

	public function get( $key ){
		return $this->data->get( $key );
	}

	public function getAll(){
		return $this->data->getAll();
	}

	public function has( $key ){
		return $this->data->has( $key );
	}

	public function loadOrder( $orderId = NULL ){
		$orderId	= $orderId ? $orderId : $this->data->get( 'orderId' );
		if( $orderId ){
			$order	= $this->modelOrder->get( $orderId );
			if( $order ){
				$this->data->set( 'userId', $order->userId );
				$this->data->set( 'customerId', $order->customerId );
				$this->data->set( 'orderStatus', $order->status );
				$this->data->set( 'paymentMethod', $order->paymentMethod );
//				$this->data->set( 'options', $order->options );
				$this->data->set( 'price', $order->price );
				$this->data->set( 'priceTaxed', $order->priceTaxed );
				$positions	= array();
				foreach( $this->modelPosition->getAllByIndex( 'orderId', $orderId ) as $item ){
					$source		= $this->bridge->getBridgeObject( (int) $item->bridgeId );
					$article	= $source->get( $item->articleId, $item->quantity );
					$positions[$item->articleId]	= (object) array(
						'bridgeId'		=> $item->bridgeId,
						'articleId'		=> $item->articleId,
						'quantity'		=> $item->quantity,
						'article'		=> $article,
					);
				}
				$this->data->set( 'positions', $positions );
			}
		}
	}

	public function releaseOrder(){
		if( $this->data->get( 'orderId' ) )
			$this->createEmpty();
	}


	public function remove( $key ){
		$this->data->remove( $key );
		if( $this->data->get( 'orderId' ) )
			if( !in_array( $key, $this->ignoreOnUpdate ) )
				$this->saveOrder();
		return $this->session->set( 'shop_cart', $this->data->getAll() );
	}

	/**
	 *	Saves cart from session to order in database.
	 *	@access		public
	 *	@return		integer		Order ID
	 */
	public function saveOrder(){
		$orderId	= $this->data->get( 'orderId' );
		if( $orderId && $this->modelOrder->get( $orderId ) ){
			return $this->updateOrder( $orderId );
		}
		return $this->createOrder();
	}

	public function set( $key, $value ){
		$this->data->set( $key, $value );
		if( $this->data->get( 'orderId' ) )
			if( !in_array( $key, $this->ignoreOnUpdate ) )
				$this->saveOrder();
		return $this->session->set( 'shop_cart', $this->data->getAll() );
	}

	/*  --  PROTECTED  --  */

	protected function createOrder(){
		$orderId	= $this->modelOrder->add( array(
			'userId'		=> $this->data->get( 'userId' ),
			'customerId'	=> $this->data->get( 'customerId' ),
			'status'		=> $this->data->get( 'orderStatus' ),
			'paymentMethod'	=> $this->data->get( 'paymentMethod' ),
//			'options'		=> $this->data->get( 'options' ),
//			'price'			=> $this->data->get( 'price' ),
//			'priceTaxed'	=> $this->data->get( 'priceTaxed' ),
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		)  );

		foreach( $this->data->get( 'positions' ) as $item ){
			$source		= $this->bridge->getBridgeObject( (int) $item->bridgeId );
			$article	= $source->get( $item->articleId, $item->quantity );
			$price		= $article->price->one;
			$priceTaxed	= $article->price->one + $article->tax->one;
			if( $this->taxIncluded ){														//  tax already is included
				$price		-= $article->tax->one;											//  reduce by tax added by default
				$priceTaxed	-= $article->tax->one;											//  reduce by tax added by default
			}
			$positionId	= $this->modelPosition->add( array(
				'orderId'		=> $orderId,
				'bridgeId'		=> $item->bridgeId,
				'articleId'		=> $item->articleId,
				'status'		=> 0,
//				'userId'		=> 0,
//				'size'			=> 0,
				'quantity'		=> $item->quantity,
				'currency'		=> $article->currency,
				'price'			=> $price,
				'priceTaxed'	=> $priceTaxed,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
		}
		$this->updateOrderPrices( $orderId );
		$this->set( 'orderId', $orderId );
		return $orderId;
	}

	protected function updateOrder( $orderId ){
		$this->modelOrder->edit( $orderId, array(
			'userId'		=> $this->data->get( 'userId' ),
			'customerId'	=> $this->data->get( 'customerId' ),
			'status'		=> $this->data->get( 'orderStatus' ),
//			'options'		=> $this->data->get( 'options' ),
			'paymentMethod'	=> $this->data->get( 'paymentMethod' ),
			'modifiedAt'	=> time(),
		)  );

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
			$relation	= $this->modelPosition->getByIndices( array(
				'orderId'	=> $orderId,
				'bridgeId'	=> $item->bridgeId,
				'articleId'	=> $item->articleId
			) );
			$source		= $this->bridge->getBridgeObject( (int) $item->bridgeId );
			$article	= $source->get( $item->articleId, $item->quantity );
			$price		= $article->price->one;
			$priceTaxed	= $article->price->one + $article->tax->one;
			if( $this->taxIncluded ){														//  tax already is included
				$price		-= $article->tax->one;											//  reduce by tax added by default
				$priceTaxed	-= $article->tax->one;											//  reduce by tax added by default
			}
			if( $relation ){
				if( $relation->quantity != $item->quantity ){
					$this->modelPosition->edit( $relation->positionId, array(
						'quantity'		=> $item->quantity,
						'price'			=> $price,
						'priceTaxed'	=> $priceTaxed,
						'modifiedAt'	=> time(),
					) );
				}
			}
			else{
				$positionId	= $this->modelPosition->add( array(
					'orderId'		=> $orderId,
					'bridgeId'		=> $item->bridgeId,
					'articleId'		=> $item->articleId,
					'status'		=> 0,
//					'userId'		=> 0,
//					'size'			=> 0,
					'quantity'		=> $item->quantity,
					'currency'		=> $article->currency,
					'price'			=> $price,
					'priceTaxed'	=> $priceTaxed,
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				) );
			}
		}
		$this->updateOrderPrices( $orderId );
		return $orderId;
	}

	protected function updateOrderPrices( $orderId ){
		$price			= 0;
		$priceTaxed		= 0;
		$relations		= $this->modelPosition->getAllByIndex( 'orderId', $orderId );
		foreach( $relations as $relation ){
			$source		= $this->bridge->getBridgeObject( (int) $relation->bridgeId );
			$article	= $source->get( $relation->articleId, $relation->quantity );
			$price		+= $article->price->all;
			$priceTaxed	+= $article->price->all + $article->tax->all;
			if( $this->taxIncluded ){
				$price		-= $article->tax->all;
				$priceTaxed	-= $article->tax->all;
			}
		}
		$this->modelOrder->edit( $orderId, array(
			'price'			=> $price,
			'priceTaxed'	=> $priceTaxed,
		) );
	}
}