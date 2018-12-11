<?php
/**
 *	@todo	complete flow implementation, currently stoppted at method "pay"
 */
class Controller_Shop extends CMF_Hydrogen_Controller{

	/**	@var	Logic_ShopBridge		$brige */
	protected $bridge;
	/**	@var	Logic_Shop				$logic */
	protected $logic;
	/**	@var	ADT_List_Dictionary		$options */
	protected $options;

	protected $backends			= array();
	protected $servicePanels	= array();

	protected $cartTotal		= 0;

	public function __onInit() {
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Shop( $this->env );
		$this->words		= (object) $this->getWords( 'msg' );
		$this->bridge		= new Logic_ShopBridge( $this->env );
		$this->options		= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->orderId		= $this->session->get( 'shop_order_id' );

		if( !$this->session->get( 'shop_order' ) ){
			$this->session->set( 'shop_order', (object) array(
				'status'		=> 0,
				'rules'			=> FALSE,
				'paymentMethod'	=> NULL,
				'paymentId'		=> NULL,
				'currency'		=> 'EUR',
			) );
			$this->session->set( 'shop_order_customer', array() );
			$this->session->set( 'shop_order_billing', array() );
			$this->session->set( 'shop_order_positions', array() );
		}
		$this->addData( 'options', $this->options );
		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, array() );
		$this->addData( 'paymentBackends', $this->backends );
//		$this->orderId	= 41;
		$this->addData( 'orderId', (int) $this->orderId );
		if( (int) $this->orderId > 0 ){
			$this->addData( 'order', $this->logic->getOrder( $this->orderId ) );
		}
		if( $this->session->get( 'shop_order_positions' ) ){
			foreach( $this->session->get( 'shop_order_positions' ) as $position ){
				$source		= $this->bridge->getBridgeObject( (int)$position->bridgeId );
				$article	= $source->get( $position->articleId, $position->quantity );
				$this->cartTotal	+= $article->price->all;
			}
		}
		$this->addData( 'cartTotal', $this->cartTotal );
	}

	/**
	 *	Add article to cart.
	 *	Uses restart to Shop::changePositionQuantity to apply cart changes.
	 *	Will restart application to shop cart if forwarding is not used.
	 *	Otherwise: Will direct to given forward path if set by request (GET parameter forwardTo).
	 *	@access		public
	 *	@param		integer		$articleId			ID of article to remove from cart
	 *	@return		void
	 */
	public function addArticle( $bridgeId, $articleId, $quantity = 1 ){
		$bridgeId		= (int) $bridgeId;
		$articleId		= (int) $articleId;
		$quantity		= abs( $quantity );
		$forwardTo		= $this->request->get( 'forwardTo' );
		if( $this->request->get( 'from' ) )
			$forwardTo	.= '?from='.$this->request->get( 'from' );
		$positions		= $this->session->get( 'shop_order_positions' );
		if( array_key_exists( $articleId, $positions ) && $positions[$articleId]->quantity ){
			foreach( $positions as $nr => $position ){
				if( $position->bridgeId == $bridgeId && $position->articleId == $articleId ){
					$param	= '?forwardTo='.urlencode( $forwardTo );
					$url	= 'changePositionQuantity/'.$bridgeId.'/'.$articleId.'/'.$quantity;
					$this->restart( $url.$param, TRUE );
				}
			}
		}

		$positions[$articleId]	= (object) array(
			'bridgeId'	=> $bridgeId,
			'articleId'	=> $articleId,
			'quantity'	=> $quantity,
		);
		$this->session->set( 'shop_order_positions', $positions );
		$order		= $this->session->get( 'shop_order' );
		if( $order->status == Model_Shop_Order::STATUS_NEW ){
			$order->status = Model_Shop_Order::STATUS_AUTHENTICATED;
			$this->session->set( 'shop_order', $order );
		}
		$title		= $this->bridge->getArticleTitle( $bridgeId, $articleId );
		$this->messenger->noteSuccess( $this->words->successAddedToCart, $title, $quantity );
		$this->restart( $forwardTo ? $forwardTo : 'shop/cart' );
	}

	public function cart(){
		$this->addData( 'order', $this->session->get( 'shop_order' ) );
		$this->addData( 'customer', $this->session->get( 'shop_order_customer' ) );
		$this->addData( 'billing', $this->session->get( 'shop_order_billing' ) );
		$positions	= $this->session->get( 'shop_order_positions' );
		foreach( $positions as $nr => $position ){
			$source		= $this->bridge->getBridgeObject( (int)$position->bridgeId );
			$article	= $source->get( $position->articleId, $position->quantity );
			$positions[$nr]->article	= $article;
		}
		$this->addData( 'positions', $positions );
	}

	public function changePositionQuantity( $bridgeId, $articleId, $quantity, $operation = NULL ){
		$bridgeId		= (int) $bridgeId;
		$articleId		= (int) $articleId;
		$quantity		= abs( $quantity );
		$forwardTo		= $this->request->get( 'forwardTo' );
		$positions		= $this->session->get( 'shop_order_positions' );
		foreach( $positions as $nr => $position ){
			if( $position->bridgeId == $bridgeId && $position->articleId == $articleId ){
				switch( $operation ){
					case 'plus':
						$position->quantity	+= (int)$quantity;
						break;
					case 'minus':
						$position->quantity	-= (int)$quantity;
						break;
					default:
						$position->quantity	= (int)$quantity;
				}
				$positions[$nr]	= $position;
				$title			= $this->bridge->getArticleTitle( $bridgeId, $articleId );
				$link			= $this->bridge->getArticleLink( $bridgeId, $articleId );
				if( !$position->quantity ){
					unset( $positions[$nr] );
					$this->messenger->noteSuccess( $this->words->successRemovedFromCart, $title );
				}
				else{
					$this->messenger->noteSuccess( $this->words->successChangedQuantity, $title, $position->quantity );
				}
				$this->session->set( 'shop_order_positions', $positions );
			}
		}
		$this->restart( $forwardTo ? $forwardTo : 'shop/cart' );
	}

	public function checkout(){
		if( $this->request->has( 'save' ) && $this->request->isMethod( 'POST' ) ){
			$orderId	= $this->session->get( 'shop_order_id' );
			$orderId	= $this->logic->storeCartFromSession( $orderId );
			$this->session->set( 'shop_order_id', $orderId );
			$price      = $this->logic->calculateOrderTotalPrice( $orderId );
			$order		= $this->logic->getOrder( $orderId );

			if( $price && $this->backends ){
				if( count( $this->backends ) === 1 )
					$order->paymentMethod	= $this->backends[0]->key;
				if( $order->paymentMethod ){
					foreach( $this->backends as $backend ){
						if( $backend->key === $order->paymentMethod ){
//							$this->logic->setOrderPaymentMethod( $orderId, $backend->key );
							$this->restart( 'payment/'.$backend->path, TRUE );
						}
					}
				}
			}
			else{
				$this->logic->setOrderStatus( $orderId, Model_Shop_Order::STATUS_PAYED );
				$this->restart( 'finish', TRUE );
			}
		}
		$order		= $this->session->get( 'shop_order' );
		$customerId	= $this->session->get( 'shop_order_customer' );
		$positions	= $this->session->get( 'shop_order_positions' );
		if( !$customerId )
			$this->restart( 'customer', TRUE );
		if( !$positions ){
			$this->messenger->noteNotice( $this->words->errorCheckoutEmptyCart );
			$this->restart( 'cart', TRUE );
		}
		$this->addData( 'order', $order );
		$this->addData( 'positions', $positions );
		switch( $this->session->get( 'shop_customer_mode' ) ){
		 	case Model_Shop_Order::CUSTOMER_MODE_ACCOUNT:
				$customer	= $this->logic->getAccountCustomer( $customerId );
				break;
		 	case Model_Shop_Order::CUSTOMER_MODE_GUEST:
				$customer	= $this->logic->getGuestCustomer( $customerId );
				break;
		}
		$this->addData( 'customer', $customer );
	}

	public function conditions(){
		$order		= $this->session->get( 'shop_order' );
		$positions	= $this->session->get( 'shop_order_positions' );
		$customer	= $this->session->get( 'shop_order_customer' );

		if( !$positions )
			$this->restart( 'cart', TRUE );

		if( !$this->session->get( 'shop_order_customer' ) )
			$this->restart( 'customer', TRUE );

		if( $this->request->has( 'saveConditions' ) ){
			if( !$this->request->get( 'accept_rules' ) ){
				$this->messenger->noteError( $this->words->errorRulesNotAccepted );
				$order->status	= -1;
				$this->session->set( 'shop_order', $order );
			}
			else{
//				$this->messenger->noteSuccess( $this->words->successRulesAccepted );
				$order->status	= 1;
				$order->rules	= TRUE;
				$this->session->set( 'shop_order', $order );
				$this->restart( 'payment', TRUE );
			}
		}

		$this->addData( 'charges', $this->calculateCharges() );
		$this->addData( 'order', $order );
	}

	public function finish(){
		$orderId	= $this->session->get( 'shop_order_id' );
		if( !$orderId ){
			$this->env->getMessenger()->noteError( $this->words->errorFinishEmptyCart );
			$this->restart( 'cart', TRUE );
		}

		$order	= $this->logic->getOrder( $orderId );
		if( $order->status < Model_Shop_Order::STATUS_ORDERED )
			$this->logic->setOrderStatus( $orderId, Model_Shop_Order::STATUS_ORDERED );
		$this->sentOrderMailCustomer( $orderId );
		$this->sentOrderMailManager( $orderId );
		$this->session->set( 'shop_order_lastId', $orderId );
		$this->session->remove( 'shop_order' );
		$this->session->remove( 'shop_order_customer' );
		$this->session->remove( 'shop_order_billing' );
		$this->session->remove( 'shop_order_positions' );
		$this->session->remove( 'shop_order_id' );
		$this->env->getMessenger()->noteSuccess( $this->words->successFinished );
		$order	= $this->logic->getOrder( $orderId );
		$this->env->getModules()->callHook( 'Shop', 'onFinish', $this, array(
			'orderId'	=> $orderId,
			'order'		=> $order,
		) );
		$this->restart( 'service', TRUE );
	}

	public function index(){
		$this->restart( 'cart', TRUE );
	}

	public function payment(){
		if( $this->cartTotal == 0 || count( $this->backends ) === 1 ){
			$paymentBackend		= $this->backends[0];
			$this->restart( 'setPaymentBackend/'.$paymentBackend->key, TRUE );
		}
		$orderId	= $this->session->get( 'shop_order_id' );
		$this->addData( 'order', $this->session->get( 'shop_order' ) );
	}

/*	public function register(){
		if( $this->request->has( 'save' ) ){
			$customer	= $this->request->getAll( 'customer_', TRUE );
			$labels		= $this->getWords( 'customer' );
			$mandatory	= array(
				'firstname',
				'lastname',
				'email',
				'country',
				'city',
				'postcode',
				'address',
			);
			foreach( $mandatory as $name ){
				if( !$customer->get( $name ) ){
					$label	= Alg_Text_CamelCase::convert( $name, FALSE );
					$this->messenger->noteError(
						$this->words->errorFieldEmpty,
						'customer_'.$name,
						$labels['labelCustomer'.$label]
					);
				}
			}
			if( !$this->messenger->gotError() )
				$this->restart( 'conditions', TRUE );

			$model	= new Model_User( $this->env );
			$model->add( $customer );
			$this->restart( 'address', TRUE );
		}
		$model		= new Model_User( $this->env );
		$customer	= (object) array();
		foreach( $model->getColumns() as $column )
			$customer->$column	= $this->request->get( $column );

		$model		= new Model_Address( $this->env );
		$address	= (object) array();
		foreach( $model->getColumns() as $column )
			$address->$column	= $this->request->get( $column );

		$address->alternative	= $this->request->get( 'billing_alternative' );

		$this->addData( 'customer', $customer );
		$this->addData( 'address', $address );
	}*/

	public function registerPaymentBackend( $backend, $key, $title, $path, $priority = 5, $icon = NULL ){
		$this->backends[]	= (object) array(
			'backend'	=> $backend,
			'key'		=> $key,
			'title'		=> $title,
			'path'		=> $path,
			'priority'	=> $priority,
			'icon'		=> $icon,
		);
	}

	public function registerServicePanel( $key, $content, $priority ){
		$this->servicePanels[$key]	= (object) array(
			'key'		=> $key,
			'content'	=> $content,
			'priority'	=> $priority,
		);
	}

	/**
	 *	Remove article from cart by cart position article ID.
	 *	Will restart application to shop cart if forwarding is not used.
	 *	Otherwise: Will direct to given forward path if set by request (GET parameter forwardTo).
	 *	@access		public
	 *	@param		integer		$articleId			ID of article to remove from cart
	 *	@return		void
	 */
	public function removeArticle( $articleId ){
		$positions		= $this->session->get( 'shop_order_positions' );
		foreach( $positions as $nr => $position )
			if( $position->articleId == $articleId )
				unset( $positions[$nr] );
		$this->session->set( 'shop_order_positions', $positions );
		if( ( $forwardTo = $this->request->get( 'forwardTo' ) ) )
			$this->restart( $forwardTo );
		$this->restart( 'cart', TRUE );
	}

	public function rules(){
	}

	public function service(){
		$orderId	= $this->session->get( 'shop_order_lastId' );
		if( !$orderId )
			$this->restart( 'cart', TRUE );
		$this->addData( 'orderId', $orderId );
		$this->addData( 'order', $this->logic->getOrder( $orderId, TRUE ) );

		$arguments	= array( 'orderId' => $orderId, 'paymentBackends' => $this->backends );
		$this->env->getModules()->callHook( 'Shop', 'renderServicePanels', $this, $arguments );
		$this->addData( 'servicePanels', $this->servicePanels );

		$arguments	= array( 'orderId' => $orderId );
		$this->addData( 'delivery', NULL );
		$this->env->getModules()->callHook( 'Shop', 'onPaymentSuccess', $this, $arguments );
	}

	public function setPaymentBackend( $paymentBackendKey = NULL ){
		if( $paymentBackendKey ){
			$orderId	= $this->session->get( 'shop_order_id' );
			$this->logic->setOrderPaymentMethod( $orderId, $paymentBackendKey );
			$this->restart( 'checkout', TRUE );
		}
	}

	/*  --  PROTECTED  --  */
	protected function calculateCharges(){
		if( !$this->env->getModules()->has( 'Shop_Shipping' ) )
			return 0;
		$customer	= $this->session->get( 'shop_order_customer', TRUE );

		$charges	= 0;
//		if( $customer->get( 'country' ) )
/*		$grade		= new Model_Shop_Shipping_Grade();
		$grade		= $grade->getGradeID( $total['weight'] );
		$zone		= new Model_Shop_Shipping_Country();
		$zone		= $zone->getZoneID( $udata['country'] );
		$price		= new Model_Shop_Shipping_Price();
		$charges	= $price->getPrice( $zone, $grade );
*/		$option		= new Model_Shop_Shipping_Option( $this->env );
		$options	= $option->getAll();
		if( count( $options ) ){
			$set_options	= explode( "|", $order['options'] );
			foreach( $options as $option )
				if( in_array( $option['shippingoption_id'], $set_options ) )
					$charges	+= (float)$option['price'];
		}
		return $charges;
	}

	protected function sentOrderMailCustomer( $orderId ){
		$order		= $this->logic->getOrder( $orderId );
		$customer	= $this->logic->getOrderCustomer( $orderId );
		$language	= $this->env->getLanguage()->getLanguage();
		$language   = !empty( $customer->language ) ? $customer->language : $language;

		$logic		= Logic_Mail::getInstance( $this->env );
		$mail		= new Mail_Shop_Customer_Ordered( $this->env, array(
			'orderId'			=> $orderId,
			'paymentBackends'	=> $this->backends,
		) );
		$logic->appendRegisteredAttachments( $mail, $language );
		$logic->handleMail( $mail, $customer, $language );
	}

	protected function sentOrderMailManager( $orderId ){
		$language	= $this->env->getLanguage()->getLanguage();
		$email		= $this->env->getConfig()->get( 'module.shop.mail.manager' );
		$logic		= Logic_Mail::getInstance( $this->env );
		$mail		= new Mail_Shop_Manager_Ordered( $this->env, array(
			'orderId'			=> $orderId,
			'paymentBackends'	=> $this->backends,
		) );
		$logic->appendRegisteredAttachments( $mail, $language );
		$logic->handleMail( $mail, (object) array( 'email' => $email ), $language );
	}

	protected function submitOrder(){
//		$this->acceptRules();
		$this->saveConditions();
		$this->closeOrder();
		$this->restart( 'cart', TRUE );
	}
}
?>
