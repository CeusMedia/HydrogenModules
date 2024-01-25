<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Text\CamelCase as TextCamelCase;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

/**
 *	@todo	complete flow implementation, currently stopped at method "pay"
 */
class Controller_Shop extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;

	/**	@var	Logic_ShopBridge		$brige */
	protected Logic_ShopBridge $bridge;

	/**	@var	Logic_Shop				$logic */
	protected Logic_Shop $logic;

	/**	@var	Dictionary				$options */
	protected Dictionary $options;

	/**	@var	Model_Shop_Cart			$modelCart */
	protected Model_Shop_Cart $modelCart;

	protected array $words;

	protected Model_Shop_Payment_Register $backends;

	protected array $servicePanels		= [];

	protected float $cartTotal			= .0;


	/**
	 *	Add article to cart.
	 *	Uses restart to Shop::changePositionQuantity to apply cart changes.
	 *	Will restart application to shop cart if forwarding is not used.
	 *	Otherwise: Will direct to given forward path if set by request (GET parameter forwardTo).
	 *	@access		public
	 *	@param		string		$articleId			ID of article to remove from cart
	 *	@return		void
	 */
	public function addArticle( string $bridgeId, string $articleId, int $quantity = 1 )
	{
		$bridgeId		= (int) $bridgeId;
		$articleId		= (int) $articleId;
		$quantity		= abs( $quantity );
		$forwardTo		= $this->request->get( 'forwardTo' );
		if( $this->request->get( 'from' ) )
			$forwardTo	.= '?from='.$this->request->get( 'from' );
		$positions		= $this->modelCart->get( 'positions' );
		if( array_key_exists( $articleId, $positions ) && $positions[$articleId]->quantity ){
			foreach( $positions as $nr => $position ){
				if( $position->bridgeId == $bridgeId && $position->articleId == $articleId ){
					$param	= '?forwardTo='.urlencode( $forwardTo );
					$url	= 'changePositionQuantity/'.$bridgeId.'/'.$articleId.'/'.$quantity;
					$this->restart( $url.$param, TRUE );
				}
			}
		}
		$source		= $this->bridge->getBridgeObject( (int) $bridgeId );
		$article	= $source->get( $articleId, $quantity );
		$positions[$bridgeId.'_'.$articleId]	= (object) [
			'bridgeId'	=> $bridgeId,
			'articleId'	=> $articleId,
			'quantity'	=> $quantity,
			'article'	=> $article,
		];
		$this->modelCart->set( 'positions', $positions );
//		$title		= $this->bridge->getArticleTitle( $bridgeId, $articleId );
		$this->messenger->noteSuccess( $this->words['successAddedToCart'], $article->title, $quantity );
		$this->restart( $forwardTo ?: 'shop/cart' );
	}

	public function cart()
	{
/*		$this->addData( 'order', $this->session->get( 'shop_order' ) );
		$this->addData( 'customer', $this->session->get( 'shop_order_customer' ) );
		$this->addData( 'billing', $this->session->get( 'shop_order_billing' ) );
		$positions	= $this->modelCart->get( 'positions' );
		foreach( $positions as $nr => $position ){
			$source		= $this->bridge->getBridgeObject( (int)$position->bridgeId );
			$article	= $source->get( $position->articleId, $position->quantity );
			$positions[$nr]->article	= $article;
		}
		$this->addData( 'positions', $positions );*/
		$this->addData( 'cart', $this->modelCart );
		$this->addData( 'address', $this->logic->getDeliveryAddressFromCart() );
	}

	/**
	 *	@param		$bridgeId
	 *	@param		$articleId
	 *	@param		$quantity
	 *	@param		$operation
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function changePositionQuantity( $bridgeId, $articleId, int $quantity, ?string $operation = NULL )
	{
		$bridgeId		= (int) $bridgeId;
		$articleId		= (int) $articleId;
		$quantity		= abs( $quantity );
		$forwardTo		= $this->request->get( 'forwardTo' );
		$positions		= $this->modelCart->get( 'positions' );
		foreach( $positions as $nr => $position ){
			if( $position->bridgeId == $bridgeId && $position->articleId == $articleId ){
				switch( $operation ){
					case 'plus':
						$position->quantity	+= (int) $quantity;
						break;
					case 'minus':
						$position->quantity	-= (int) $quantity;
						break;
					default:
						$position->quantity	= (int) $quantity;
				}

				$source		= $this->bridge->getBridgeObject( (int) $bridgeId );
				$article	= $source->get( $articleId, $position->quantity );
				$position->article	= $article;
				$positions[$nr]	= $position;
				if( !$position->quantity ){
					unset( $positions[$nr] );
					$this->messenger->noteSuccess( $this->words['successRemovedFromCart'], $position->article->title );
				}
				else{
					$this->messenger->noteSuccess( $this->words['successChangedQuantity'], $position->article->title, $position->quantity );
				}
				$this->modelCart->set( 'positions', $positions );
			}
		}
		$this->restart( $forwardTo ?: 'shop/cart' );
	}

	public function checkout(): void
	{
		$customerMode	= $this->modelCart->get( 'customerMode' );
//		print_m( $this->session->getAll( 'shop_' ) );die;
		if( $customerMode === Model_Shop_Cart::CUSTOMER_MODE_ACCOUNT ){
			$logicAuth	= new Logic_Authentication( $this->env );
			if( !$logicAuth->isIdentified() ){
				$this->modelCart->set( 'userId', 0 );
				$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_NEW );
				$this->restart( 'customer', TRUE );
			}
		}
		else if( $customerMode === Model_Shop_Cart::CUSTOMER_MODE_GUEST ){
			if( !$this->modelCart->get( 'userId' ) )
				$this->restart( 'customer', TRUE );
		}
		if( $this->request->has( 'save' ) && $this->request->getMethod()->is( 'POST' ) ){
			$orderId	= $this->modelCart->get( 'orderId' );
			if( !$orderId )
				$orderId	= $this->modelCart->saveOrder();
			$order		= $this->logic->getOrder( $orderId, TRUE );
			if( $order->price && 0 !== count( $this->backends->getAll() ) ){
				if( count( $this->backends->getAll() ) === 1 )
					$order->paymentMethod	= $this->backends->getAll()[0]->key;
				if( $order->paymentMethod ){
					foreach( $this->backends->getAll() as $backend ){
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
//		$order		= $this->session->get( 'shop_order' );
		$userId		= $this->modelCart->get( 'userId' );
		$positions	= $this->modelCart->get( 'positions' );
		if( !$userId )
			$this->restart( 'customer', TRUE );
		if( !$positions ){
			$this->messenger->noteNotice( $this->words['errorCheckoutEmptyCart'] );
			$this->restart( 'cart', TRUE );
		}
//		$this->addData( 'order', $order );
//		$this->addData( 'positions', $positions );
		$this->addData( 'cart', $this->modelCart );
		switch( $this->modelCart->get( 'customerMode' ) ){
		 	case Model_Shop_Cart::CUSTOMER_MODE_ACCOUNT:
				$customer	= $this->logic->getAccountCustomer( $userId );
				break;
		 	case Model_Shop_Cart::CUSTOMER_MODE_GUEST:
			default:
				$customer	= $this->logic->getAccountCustomer( $userId );
				break;
		}
		if( !$customer->addressDelivery )
			$this->restart( 'customer', TRUE );
		$this->addData( 'customer', $customer );
		$this->addData( 'address', $this->logic->getDeliveryAddressFromCart() );
	}

	public function conditions(): void
	{
//		$order		= $this->session->get( 'shop_order' );
		$positions	= $this->modelCart->get( 'positions' );

		if( !$positions )
			$this->restart( 'cart', TRUE );

		if( $this->modelCart->get( 'orderStatus' ) < Model_Shop_Order::STATUS_AUTHENTICATED )
			$this->restart( 'customer', TRUE );

		if( $this->request->has( 'saveConditions' ) ){
			if( !$this->request->get( 'accept_rules' ) ){
				$this->messenger->noteError( $this->words['errorRulesNotAccepted'] );
//				$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_NEW );
				$this->modelCart->set( 'acceptRules', FALSE );
			}
			else{
//				$this->messenger->noteSuccess( $this->words['successRulesAccepted'] );
//				$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_AUTHENTICATED );
				$this->modelCart->set( 'acceptRules', TRUE );
				$this->restart( 'payment', TRUE );
			}
		}

		$this->addData( 'charges', $this->calculateCharges() );
//		$this->addData( 'order', $order );
		$this->addData( 'cart', $this->modelCart );
	}

	public function finish(): void
	{
		$orderId	= $this->modelCart->get( 'orderId' );
		if( !$orderId ){
			$this->env->getMessenger()->noteError( $this->words['errorFinishEmptyCart'] );
			$this->restart( 'cart', TRUE );
		}
		$order	= $this->logic->getOrder( $orderId );
		if( $order->status < Model_Shop_Order::STATUS_ORDERED )
			$this->logic->setOrderStatus( $orderId, Model_Shop_Order::STATUS_ORDERED );
		$this->sentOrderMailCustomer( $orderId );
		$this->sentOrderMailManager( $orderId );
		$this->session->set( 'shop_order_lastId', $orderId );
		$this->modelCart->releaseOrder();
		$this->env->getMessenger()->noteSuccess( $this->words['successFinished'] );
		$order		= $this->logic->getOrder( $orderId );
		$payload	= [
			'orderId'	=> $orderId,
			'order'		=> $order,
		];
		$this->env->getModules()->callHookWithPayload( 'Shop', 'onFinish', $this, $payload );
		$this->restart( 'service', TRUE );
	}

	public function index(): void
	{
		$this->restart( 'cart', TRUE );
	}

	public function payment(): void
	{
		if( $this->cartTotal == 0 || count( $this->backends->getAll() ) === 1 ){
			$paymentBackend		= $this->backends->getAll()[0];
			$this->restart( 'setPaymentBackend/'.$paymentBackend->key, TRUE );
		}
//		$orderId	= $this->modelCart->get( 'orderId' );
		$this->addData( 'cart', $this->modelCart );

		$this->addData( 'billingAddress', $this->logic->getBillingAddressFromCart() );
	}

/*	public function register(): void
	{
		if( $this->request->has( 'save' ) ){
			$customer	= $this->request->getAll( 'customer_', TRUE );
			$labels		= $this->getWords( 'customer' );
			$mandatory	= [
				'firstname',
				'lastname',
				'email',
				'country',
				'city',
				'postcode',
				'address',
			];
			foreach( $mandatory as $name ){
				if( !$customer->get( $name ) ){
					$label	= TextCamelCase::convert( $name, FALSE );
					$this->messenger->noteError(
						$this->words['errorFieldEmpty'],
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
		$customer	= (object) [];
		foreach( $model->getColumns() as $column )
			$customer->$column	= $this->request->get( $column );

		$model		= new Model_Address( $this->env );
		$address	= (object) [];
		foreach( $model->getColumns() as $column )
			$address->$column	= $this->request->get( $column );

		$address->alternative	= $this->request->get( 'billing_alternative' );

		$this->addData( 'customer', $customer );
		$this->addData( 'address', $address );
	}*/

	public function registerServicePanel( $key, $content, $priority ): void
	{
		$this->servicePanels[$key]	= (object) [
			'key'		=> $key,
			'content'	=> $content,
			'priority'	=> $priority,
		];
	}

	/**
	 *	Remove article from cart by cart position article ID.
	 *	Will restart application to shop cart if forwarding is not used.
	 *	Otherwise: Will direct to given forward path if set by request (GET parameter forwardTo).
	 *	@access		public
	 *	@param		string		$articleId			ID of article to remove from cart
	 *	@return		void
	 */
	public function removeArticle( string $articleId ): void
	{
		$positions		= $this->modelCart->get( 'positions' );
		foreach( $positions as $nr => $position )
			if( $position->articleId == $articleId )
				unset( $positions[$nr] );
		$this->modelCart->set( 'positions', $positions );
		if( ( $forwardTo = $this->request->get( 'forwardTo' ) ) )
			$this->restart( $forwardTo );
		$this->restart( 'cart', TRUE );
	}

	public function rules(): void
	{
	}

	public function service(): void
	{
		$orderId	= $this->session->get( 'shop_order_lastId' );
		if( !$orderId )
			$this->restart( 'cart', TRUE );
		$this->addData( 'orderId', $orderId );
		$this->addData( 'order', $this->logic->getOrder( $orderId, TRUE ) );

		$payload	= ['orderId' => $orderId, 'paymentBackends' => $this->backends];
		$this->env->getModules()->callHookWithPayload( 'Shop', 'renderServicePanels', $this, $payload );
		$this->addData( 'servicePanels', $this->servicePanels );

		$payload	= ['orderId' => $orderId];
		$this->addData( 'delivery', NULL );
		$this->env->getModules()->callHookWithPayload( 'Shop', 'onPaymentSuccess', $this, $payload );
	}

	public function setPaymentBackend( $paymentBackendKey = NULL ): void
	{
		if( $paymentBackendKey ){
			$this->modelCart->set( 'paymentMethod', $paymentBackendKey );
			$this->restart( 'checkout', TRUE );
		}
		$this->restart( 'payment', TRUE );
	}

	/*  --  PROTECTED  --  */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Shop( $this->env );
		$this->bridge		= new Logic_ShopBridge( $this->env );
		$this->modelCart	= new Model_Shop_Cart( $this->env );
		$this->words		= $this->getWords( 'msg' );
		$this->options		= $this->env->getConfig()->getAll( 'module.shop.', TRUE );

		$this->addData( 'options', $this->options );
		$captain	= $this->env->getCaptain();
		$payload	= ['register' => new Model_Shop_Payment_Register( $this->env )];
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, $payload );
		$this->backends	= $payload['register'];
		$this->addData( 'paymentBackends', $payload['register'] );
		$this->addData( 'cart', $this->modelCart );
		if( $this->modelCart->get( 'positions' ) )
			foreach( $this->modelCart->get( 'positions' ) as $position )
				$this->cartTotal	+= $position->article->price->all;
		$this->addData( 'cartTotal', $this->cartTotal );
	}

	protected function calculateCharges(): float
	{
		if( !$this->env->getModules()->has( 'Shop_Shipping' ) )
			return 0;
//		$customer	= $this->session->get( 'shop_order_customer', TRUE );

		$charges	= 0;
//		if( $customer->get( 'country' ) )
/*		$grade		= new Model_Shop_Shipping_Grade();
		$grade		= $grade->getGradeID( $total['weight'] );
		$zone		= new Model_Shop_Shipping_Country();
		$zone		= $zone->getZoneID( $data['country'] );
		$price		= new Model_Shop_Shipping_Price();
		$charges	= $price->getPrice( $zone, $grade );
*/		$option		= new Model_Shop_Shipping_Option( $this->env );
		$options	= $option->getAll();
		if( count( $options ) ){
			$set_options	= explode( "|", $order['options'] );
			foreach( $options as $option )
				if( in_array( $option['shippingoption_id'], $set_options ) )
					$charges	+= (float) $option['price'];
		}
		return $charges;
	}

	protected function sentOrderMailCustomer( $orderId ): void
	{
		$order		= $this->logic->getOrder( $orderId );
		$customer	= $this->logic->getOrderCustomer( $orderId );
		$language	= $this->env->getLanguage()->getLanguage();
		$language   = !empty( $customer->language ) ? $customer->language : $language;

		$logic		= Logic_Mail::getInstance( $this->env );
		$mail		= new Mail_Shop_Customer_Ordered( $this->env, [
			'orderId'			=> $orderId,
			'paymentBackends'	=> $this->backends,
		] );
		$logic->appendRegisteredAttachments( $mail, $language );
		$logic->handleMail( $mail, $customer, $language );
	}

	protected function sentOrderMailManager( string $orderId ): void
	{
		$language	= $this->env->getLanguage()->getLanguage();
		$email		= $this->env->getConfig()->get( 'module.shop.mail.manager' );
		$logic		= Logic_Mail::getInstance( $this->env );
		$mail		= new Mail_Shop_Manager_Ordered( $this->env, [
			'orderId'			=> $orderId,
			'paymentBackends'	=> $this->backends,
		] );
		$logic->appendRegisteredAttachments( $mail, $language );
		$logic->handleMail( $mail, (object) ['email' => $email], $language );
	}

	protected function submitOrder(): void
	{
//		$this->acceptRules();
		$this->saveConditions();
		$this->closeOrder();
		$this->restart( 'cart', TRUE );
	}
}
