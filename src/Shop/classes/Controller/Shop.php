<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
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

	protected Model_Shop_Payment_BackendRegister $backends;

	protected array $servicePanels		= [];

	protected float $cartTotal			= .0;


	/**
	 *	Add article to cart.
	 *	Uses restart to Shop::changePositionQuantity to apply cart changes.
	 *	Will restart application to shop cart if forwarding is not used.
	 *	Otherwise: Will direct to given forward path if set by request (GET parameter forwardTo).
	 *	@access		public
	 *	@param		string		$bridgeId
	 *	@param		string		$articleId			ID of article to remove from cart
	 *	@param		int			$quantity
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addArticle( string $bridgeId, string $articleId, int $quantity = 1 ): void
	{
		$bridgeId		= (int) $bridgeId;
		$articleId		= (int) $articleId;
		$quantity		= abs( $quantity );
		$forwardTo		= $this->request->get( 'forwardTo' );
		if( $this->request->get( 'from' ) )
			$forwardTo	.= '?from='.$this->request->get( 'from' );
		$positions		= $this->modelCart->get( 'positions' );
		if( array_key_exists( $articleId, $positions ) && $positions[$articleId]->quantity ){
			foreach( $positions as $position ){
				if( $position->bridgeId == $bridgeId && $position->articleId == $articleId ){
					$param	= '?forwardTo='.urlencode( $forwardTo );
					$url	= 'changePositionQuantity/'.$bridgeId.'/'.$articleId.'/'.$quantity;
					$this->restart( $url.$param, TRUE );
				}
			}
		}
		$source		= $this->bridge->getBridgeObject( $bridgeId );
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

	public function cart(): void
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
	 *	@param		string $bridgeId
	 *	@param		string $articleId
	 *	@param		integer			$quantity
	 *	@param		string|NULL		$operation
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function changePositionQuantity( string $bridgeId, string $articleId, int $quantity, ?string $operation = NULL ): void
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

				$source		= $this->bridge->getBridgeObject( $bridgeId );
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

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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
		if( $this->request->getMethod()->is( 'POST' ) && $this->request->has( 'save' ) ){
			$orderId	= $this->modelCart->get( 'orderId' );
			if( NULL === $orderId )
				$orderId	= $this->modelCart->saveOrder();
			$order		= $this->logic->getOrder( $orderId, TRUE );
			$this->startPaymentIfNeeded( $order );						//  will redirect into payment flow
			$this->logic->setOrderStatus( $orderId, Model_Shop_Order::STATUS_PAYED );
			$this->restart( 'finish', TRUE );
		}

		//  GET request
		$this->prepareCheckoutView();
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function conditions(): void
	{
//		$order		= $this->session->get( 'shop_order' );
		$positions	= $this->modelCart->get( 'positions' );

		if( !$positions )
			$this->restart( 'cart', TRUE );

		if( $this->modelCart->get( 'orderStatus' ) < Model_Shop_Order::STATUS_AUTHENTICATED )
			$this->restart( 'customer', TRUE );

		//  POST request: accept rules and advance to payment step
		if( $this->request->getMethod()->isPost() && $this->request->has( 'saveConditions' ) ){
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
		$this->addData( 'paymentFees', $this->calculatePaymentFees() );
//		$this->addData( 'order', $order );
		$this->addData( 'cart', $this->modelCart );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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
		$payload	= [
			'orderId'	=> $orderId,
			'order'		=> $this->logic->getOrder( $orderId ),
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
		$address	= $this->logic->getBillingAddressFromCart();

		$priceShipping	= .0;
		if( $this->env->getModules()->has( 'Shop_Shipping' ) ){
			$weight	= 0;
			foreach( $this->modelCart->get( 'positions' ) as $position )
				$weight	+= $position->article->weight->all;
			$logicShipping	= new Logic_Shop_Shipping( $this->env );
			$priceShipping	= $logicShipping->getPriceFromCountryCodeAndWeight( $this->logic->getDeliveryAddressFromCart()->country, $weight );
		}

		$price	= $this->cartTotal + $priceShipping;

		$logicPayment	= new Logic_Shop_Payment( $this->env );
		$logicPayment->setBackends( $this->backends );
		$backendPrices	= [];
		foreach( $this->backends->getAll() as $backend ){
			$backendPrices[$backend->key]	= NULL;
			if( $backend->feeExclusive )
				$backendPrices[$backend->key]	= $logicPayment->getPrice( $price, $backend, $address->country );
		}
		$this->addData( 'cart', $this->modelCart );
		$this->addData( 'billingAddress', $address );
		$this->addData( 'backendPrices', $backendPrices );
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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

	/**
	 *	Set or reset payment backend.
	 *	@param		string|NULL		$paymentBackendKey
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setPaymentBackend( ?string $paymentBackendKey = NULL ): void
	{
		if( $paymentBackendKey ){
			$this->modelCart->set( 'paymentMethod', $paymentBackendKey );
			$this->restart( 'checkout', TRUE );
		}
		$this->restart( 'payment', TRUE );
	}

	/*  --  PROTECTED  --  */

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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

		if( $this->env->getModules()->has( 'Shop_Payment' ) ){
			$logicPayment	= new Logic_Shop_Payment( $this->env );
			$logicPayment->collectBackends();
			$this->backends	= $logicPayment->getBackends();
			$this->addData( 'paymentBackends', $this->backends );
		}
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
/*		if( $customer->get( 'country' ) ){
			$modelShippingGrade		= new Model_Shop_Shipping_Grade();
			$modelShippingZone		= new Model_Shop_Shipping_Country();
			$modelShippingPrice		= new Model_Shop_Shipping_Price();
			$grade		= $modelShippingGrade->getGradeID( $total['weight'] );
			$zone		= $modelShippingZone->getZoneID( $data['country'] );
			$charges	= $modelShippingPrice->getPrice( $zone, $grade );
		}*/

/*		$modelShippingOption		= new Model_Shop_Shipping_Option( $this->env );
		$order		= $this->modelCart->getAll();
		$options	= $modelShippingOption->getAll();
		if( count( $options ) ){
			$set_options	= explode( "|", $order['options'] );
			foreach( $options as $option )
				if( in_array( $option['shippingoption_id'], $set_options ) )
					$charges	+= (float) $option['price'];
		}*/

		return $charges;
	}

	/**
	 *	@return		float
	 */
	protected function calculatePaymentFees(): float
	{
		if( !$this->env->getModules()->has( 'Shop_Payment') )
			return .0;

		$logic	= new Logic_Shop_Payment( $this->env );
		$logic->setBackends( $this->backends );
		$backend	= $this->getData( 'paymentMethod', '' );
		if( '' === $backend )
			return .0;
		return $logic->getPrice( $this->cartTotal, $backend, 'DE' );
	}

	protected function prepareCheckoutView(): void
	{
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

	/**
	 * @param $orderId
	 * @return void
	 * @throws ReflectionException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	protected function sentOrderMailCustomer( $orderId ): void
	{
		$order		= $this->logic->getOrder( $orderId );
		$customer	= $this->logic->getOrderCustomer( $order );
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

	/**
	 *	@param		string		$orderId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
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

	protected function startPaymentIfNeeded( object $order ): void
	{
		if( 0 === ( $order->price ?? 0 ) )
			return;
		$nrOfBackends	= count( $this->backends->getAll() );
		if( 0 === $nrOfBackends )
			return;
		if( 1 === $nrOfBackends )
			$order->paymentMethod	= $this->backends->getAll()[0]->key;

		if( NULL === ( $order->paymentMethod ?? 0 ) )
			return;

		$backend	= $this->backends->get( $order->paymentMethod, FALSE );
		if( NULL === $backend || !$backend->active )
			$this->restart( 'payment', TRUE );
		$this->restart( 'payment/'.$backend->path, TRUE );
	}

	/**
	 * @deprecated not used anymore
	 * @todo delete
	 */
	protected function submitOrder(): void
	{
		throw new \CeusMedia\Common\Exception\Deprecation( 'Shop::submitOrder is deprecated' );
	}
}
