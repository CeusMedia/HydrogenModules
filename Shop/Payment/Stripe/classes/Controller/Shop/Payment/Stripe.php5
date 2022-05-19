<?php
class Controller_Shop_Payment_Stripe extends CMF_Hydrogen_Controller
{
	/**	@var	ADT_List_Dictionary			$config			Module configuration dictionary */
	protected $config;

	/**	@var	Logic_Shop_Payment_Stripe	$logicPayment	Stripe payment logic instance */
	protected $logicPayment;

	/**	@var	Logic_Shop					$logicShop		Shop logic instance */
	protected $logicShop;

	/**	@var	Logic_Payment_Stripe		$provider		Payment provider logic instance */
	protected $provider;

	/**	@var	Net_HTTP_PartitionSession	$session		Session resource */
	protected $session;

	protected $request;
	protected $messenger;
	protected $orderId;
	protected $order;
	protected $localUserId;
	protected $userId;
	protected $wallet;
	protected $backends			= [];
	protected $modelCart;

	public function index()
	{
		if( !( $sourceId = $this->request->get( 'source' ) ) )
			$this->restart( 'shop/payment' );
		if( $sourceId != $this->session->get( 'shop_payment_stripe_sourceId' ) ){
			$this->messenger->noteError( 'Invalid payment source ID' );
			$this->restart( 'shop/payment' );
		}
		$source	= \Stripe\Source::retrieve( $sourceId );
		if( isset( $source->redirect->status ) ){
			switch( $source->redirect->status ){
				case 'succeeded':
					$this->logicPayment->updatePayment( $source );
					$this->logicShop->setOrderStatus( $this->orderId, Model_Shop_Order::STATUS_PAYED );
					$this->messenger->noteSuccess( 'Die Bezahlung wurde erfolgreich durchgeführt.' );
					$this->restart( 'shop/finish' );
				case 'failed':
					$this->logicPayment->updatePayment( $source );
					$this->messenger->noteSuccess( 'Die Bezahlung wurde abgebrochen.' );
					$this->restart( 'shop/checkout' );
			}
		}
		print_m( $payIn );die;
	}

	public function perBankWire()
	{
		throw new Exception( 'Not implemented' );
		$returnUrl		= $this->env->url.'shop/checkout';
		try{
			$createdPayIn	= $this->provider->createPayInFromBankAccount(
				$this->userId,
				$this->wallet->Id,
				0,
				$this->order->currency,
				round( $this->order->priceTaxed * 100 )
			);
			$this->logicPayment->notePayment( $createdPayIn, $this->userId, $this->orderId );
			$this->restart( 'shop/finish' );
		}
		catch( Stripe\Libraries\ResponseException $e ){
			$this->handleStripeResponseException( $e );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			exit;
		}
		throw new Exception( 'No implemented' );
	}

	public function perCreditCard( $arg0 = NULL, $arg1 = NULL, $arg2 = NULL, $arg3 = NULL )
	{
		if( $this->request->get( 'stripeToken' ) ){
			try{
				$charge	= $this->provider->createChargeFromToken(
					$this->orderId,
 					$this->request->get( 'stripeToken' )
				);
				$this->logicPayment->notePayment( $charge, $this->userId, $this->orderId );
				$this->messenger->noteSuccess( 'Die Bezahlung wurde erfolgreich durchgeführt.' );
				$this->restart( 'shop/finish' );
			}
			catch( Stripe\Libraries\ResponseException $e ){
				$this->handleStripeResponseException( $e );
			}
			catch( Exception $e ){
				UI_HTML_Exception_Page::display( $e );
				exit;
			}
		}
		$configResource	= $this->env->getConfig()->getAll( 'module.resource_payment_stripe.', TRUE );
		$this->addData( 'publicKey', $configResource->get( 'api.key.public' ) );
		$this->addData( 'orderId', $this->orderId );
		$this->addData( 'order', $this->logicShop->getOrder( $this->orderId ) );
	}

	public function perDirectDebit()
	{
		throw new Exception( 'Not implemented yet' );
		$returnUrl		= $this->env->url.'shop/payment/stripe';
		try{
			$createdPayIn	= $this->provider->createBankPayInViaWeb(
				'GIROPAY',
				$this->userId,
				$this->wallet->Id,
				$this->order->currency,
				round( $this->order->priceTaxed * 100 ),
				$returnUrl
			);
			$this->logicPayment->notePayment( $createdPayIn, $this->userId, $this->orderId );
			$this->restart( $createdPayIn->ExecutionDetails->RedirectURL, FALSE, NULL, TRUE );
		}
		catch( Stripe\Libraries\ResponseException $e ){
			$this->handleStripeResponseException( $e );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			exit;
		}
		throw new Exception( 'No implemented' );
	}

	public function perGiropay()
	{
		if( $this->request->has( 'source' ) )
			$this->restart( 'shop/payment/stripe?source='.$this->request->get( 'source' ) );
		try{
			$source	= \Stripe\Source::create(array(
				'type'		=> 'giropay',
				'amount'	=> round( $this->order->priceTaxed * 100 ),
				'currency'	=> strtolower( $this->order->currency ),
				'redirect'	=> array(
					'return_url'	=> $this->env->url.'shop/payment/stripe',
				),
				'owner'	=> array(
					'name'	=> $this->buyerData->firstname.' '.$this->buyerData->surname,
					'email'	=> $this->buyerData->email,
				)
			));
			$this->logicPayment->notePayment( $source, $this->userId, $this->orderId );
			$this->relocate( $source->redirect->url );
		}
		catch( Stripe\Libraries\ResponseException $e ){
			$this->handleStripeResponseException( $e );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			exit;
		}
	}

	public function perSofort()
	{
		if( $this->request->has( 'source' ) )
			$this->restart( 'shop/payment/stripe?source='.$this->request->get( 'source' ) );
		try{
			$source	= \Stripe\Source::create(array(
				'type'		=> 'sofort',
				'amount'	=> round( $this->order->priceTaxed * 100 ),
				'currency'	=> strtolower( $this->order->currency ),
				'redirect'	=> array(
					'return_url'	=> $this->env->url.'shop/payment/stripe',
				),
				'owner'	=> array(
					'name'	=> $this->buyerData->firstname.' '.$this->buyerData->surname,
					'email'	=> $this->buyerData->email,
				),
				'sofort'	=> array(
					'country'	=> $this->buyerData->country,
				)
			));
			$this->logicPayment->notePayment( $source, $this->userId, $this->orderId );
			$this->relocate( $source->redirect->url );
		}
		catch( Stripe\Libraries\ResponseException $e ){
			$this->handleStripeResponseException( $e );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			exit;
		}
	}

	public function registerPaymentBackend( $backend, string $key, string $title, string $path, int $priority = 5, string $icon = NULL )
	{
		$this->backends[]	= (object) array(
			'backend'	=> $backend,
			'key'		=> $key,
			'title'		=> $title,
			'path'		=> $path,
			'priority'	=> $priority,
			'icon'		=> $icon,
			'mode'		=> 'instant',
		);
	}

	protected function __onInit()
	{
		$this->session			= $this->env->getSession();
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
//		$this->configPayment	= $this->env->getConfig()->getAll( 'module.shop_payment.', TRUE );
		$this->configShop		= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->provider			= new Logic_Payment_Stripe( $this->env );
		$this->logicPayment		= new Logic_Shop_Payment_Stripe( $this->env );
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->modelCart		= new Model_Shop_Cart( $this->env );

		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, array() );
		$this->addData( 'paymentBackends', $this->backends );
		$this->addData( 'configShop', $this->configShop );

		$this->order	= $this->getOrderFromCartInSession();
		$this->orderId	= $this->order->orderId;

		$this->localUserId	= $this->session->get( 'auth_user_id' );

		$this->buyerData	= $this->getBuyerDataFromOrder( $this->order, $this->modelCart );
		$this->userId	= $this->provider->getUserIdFromLocalUserId( $this->localUserId, FALSE );
		if( !$this->userId ){
			$account		= $this->provider->createCustomerFromLocalUser( $this->localUserId );
			$this->userId	= $account->Id;
		}
/*		$wallets		= $this->provider->getUserWalletsByCurrency( $this->userId, $this->order->currency );
		if( !$wallets )
			$wallets	= array( $this->provider->createUserWallet( $this->userId, $this->order->currency ) );
		$this->wallet	= $wallets[0];*/

/*		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, array() );
		$this->addData( 'paymentBackends', $this->backends );*/
	}

	protected function getOrderFromCartInSession()
	{
		$orderId		= $this->modelCart->get( 'orderId' );
		if( !$orderId ){
			$this->messenger->noteError( 'Invalid order' );
			$this->restart( 'shop' );
		}
		return $this->logicShop->getOrder( $orderId );
	}

	protected function getBuyerDataFromOrder( $order, $modelCart )
	{
		$modelAddress		= new Model_Address( $this->env );

		if( !$this->localUserId ){
			$this->messenger->noteError( 'Not authenticated' );
			$this->restart( 'shop/customer' );
		}
		if( $order->userId != $this->localUserId ){
			$this->messenger->noteError( 'Access to order denied for current user' );
			$this->restart( 'shop/customer' );
		}
		$modelUser	= new Model_User( $this->env );
		$user		= $modelUser->get( $this->localUserId );
		$address	= $modelAddress->getByIndices( array(
			'relationId'	=> $this->localUserId,
			'relationType'	=> 'user',
			'type'			=> Model_Address::TYPE_BILLING,
		) );
		if( !$address )
			throw new RuntimeException( 'Customer has no billing address' );
		$buyerData	= (object) array(
			'mode'		=> Model_Shop_Cart::CUSTOMER_MODE_ACCOUNT,
			'id'		=> $this->localUserId,
			'firstname'	=> $user->firstname,
			'surname'	=> $user->surname,
			'email'		=> $user->email,
			'country'	=> $address->country,
		);
		return $buyerData;
	}

	protected function handleStripeResponseException( $e )
	{
		$error		= (object) array_merge( $e->getJsonBody()['error'], array(
			'http'		=> $e->getHttpStatus(),
			'class'		=> get_class( $e ),
		) );
		$details	= print_m( $error, NULL, NULL, TRUE );
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}
}
