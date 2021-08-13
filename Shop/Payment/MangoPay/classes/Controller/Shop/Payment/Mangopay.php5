<?php
class Controller_Shop_Payment_Mangopay extends CMF_Hydrogen_Controller
{
	/**	@var	ADT_List_Dictionary			$config			Module configuration dictionary */
	protected $config;

	/**	@var	Logic_Shop					$logicShop		Shop logic instance */
	protected $logicShop;

	/**	@var	Logic_Payment_Mangopay		$provider		Payment provider logic instance */
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

	public function index( $transactionId = NULL )
	{
		if( !( $transactionId = $this->request->get( 'transactionId' ) ) ){
			$this->restart( 'shop/payment' );
		}
		if( $transactionId != $this->session->get( 'shop_payment_mangopay_payInId' ) ){
			$this->messenger->noteError( 'Invalid payment transaction ID' );
			$this->restart( 'shop/payment' );
		}
		if( !( $payIn = $this->provider->getPayin( $transactionId ) ) ){
			$this->messenger->noteError( 'Invalid payment transaction ID' );
			$this->restart( 'shop/payment' );
		}
		if( $payIn->Status === "SUCCEEDED" ){
			$result	= $this->logicPayment->transferOrderAmountToClientSeller(
				$this->orderId,
				$payIn,
				TRUE
			);
			if( $result ){
				$this->logicPayment->updatePayment( $payIn );
				$this->logicShop->setOrderStatus( $this->orderId, Model_Shop_Order::STATUS_PAYED );
				$this->messenger->noteSuccess( 'Payin succeeded.' );
				$this->restart( 'shop/finish' );
			}
		}
		if( $payIn->Status === "FAILED" ){
			$this->logicPayment->updatePayment( $payIn );
			$this->restart( 'shop/checkout' );
		}
		print_m( $payIn );die;
	}


	public function perBankWire()
	{
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
		catch( MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			exit;
		}
		throw new Exception( 'No implemented' );
	}

	public function perDirectDebit()
	{
		$returnUrl		= $this->env->url.'shop/payment/mangopay';
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
		catch( MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			exit;
		}
		throw new Exception( 'No implemented' );
	}

	public function perCreditCard()
	{
/*		if( $this->request->has( 'transactionId' ) ){
			$result = $this->provider->getPayin( $this->request->get( 'transactionId' ) );
			if( $result->Status === "SUCCEEDED" ){

				$this->messenger->noteSuccess( 'Payin succeeded.' );
				$this->restart( './shop/finish' );
			}
			else{
				$helper	= new View_Helper_Mangopay_Error( $this->env );
				$helper->setCode( $result->ResultCode );
				$this->messenger->noteError( $helper->render() );
				$this->restart( './shop/payment/mangopay' );
			}
		}*/
		try{
			$returnUrl		= $this->env->url.'shop/payment/mangopay';
			$createdPayIn	= $this->provider->createCardPayInViaWeb(
				$this->userId,
				$this->wallet->Id,
				'CB_VISA_MASTERCARD',//$this->request->get( 'cardType' ),
				$this->order->currency,
				round( $this->order->priceTaxed * 100 ),
				$returnUrl
			);
			$this->logicPayment->notePayment( $createdPayIn, $this->userId, $this->orderId );
			$this->restart( $createdPayIn->ExecutionDetails->RedirectURL, FALSE, NULL, TRUE );
		}
		catch( MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			exit;
		}
	}

	protected function __onInit()
	{
		$this->config		= $this->env->getConfig()->getAll( 'module.shop_payment.', TRUE );
		$this->provider		= new Logic_Payment_Mangopay( $this->env );
		$this->logicShop	= new Logic_Shop( $this->env );
		$this->logicPayment	= new Logic_Shop_Payment_Mangopay( $this->env );
		$this->modelPayment	= new Model_Shop_Payment_Mangopay( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();

		$modelCart			= new Model_Shop_Cart( $this->env );
		$this->orderId		= $modelCart->get( 'orderId' );
		if( !$this->orderId ){
			$this->messenger->noteError( 'Invalid order' );
			$this->restart( 'shop' );
		}
		$this->order		= $this->logicShop->getOrder( $this->orderId );

		$this->localUserId	= $this->session->get( 'auth_user_id' );
		if( !$this->localUserId ){
			$this->messenger->noteError( 'Not authenticated' );
			$this->restart( 'shop/customer' );
		}
		if( $this->order->userId != $this->localUserId ){
			$this->messenger->noteError( 'Access to order denied for current user' );
			$this->restart( 'shop/customer' );
		}
		$this->userId	= $this->provider->getUserIdFromLocalUserId( $this->localUserId, FALSE );
		if( !$this->userId ){
			$account		= $this->provider->createNaturalUserFromLocalUser( $this->localUserId );
			$this->userId	= $account->Id;
		}
		$wallets		= $this->provider->getUserWalletsByCurrency( $this->userId, $this->order->currency );
		if( !$wallets )
			$wallets	= array( $this->provider->createUserWallet( $this->userId, $this->order->currency ) );
		$this->wallet	= $wallets[0];

/*		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, array() );
		$this->addData( 'paymentBackends', $this->backends );*/
	}

	protected function handleMangopayResponseException( $e )
	{
		ob_start();
		print_r( $e->GetErrorDetails()->Errors );
		$details	= ob_get_clean();
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}
}
