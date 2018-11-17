<?php
class Controller_Shop_Payment_Paypal extends CMF_Hydrogen_Controller{

	/**	@var	ADT_List_Dictionary			$config			Module configuration dictionary */
	protected $config;

	/**	@var	Logic_Payment				$provider		Payment provider logic instance */
	protected $provider;

	/**	@var	Logic_Shop					$shop			Shop logic instance */
	protected $shop;

	/**	@var	Net_HTTP_PartitionSession	$session		Session resource */
	protected $session;

	public function __onInit(){
		$this->config		= $this->env->getConfig()->getAll( 'module.shop_payment_paypal.', TRUE );
		$this->provider		= new Logic_Payment_Paypal( $this->env );
		$this->shop			= new Logic_Shop( $this->env );
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->orderId		= $this->session->get( 'shop_order_id' );
		if( !$this->orderId ){
			$this->messenger->noteError( 'Invalid order' );
			$this->restart( 'shop' );
		}
		$this->order		= $this->shop->getOrder( $this->orderId );
		$this->provider->setAccount(
			$this->config->get( 'merchant.username' ),
			$this->config->get( 'merchant.password' ),
			$this->config->get( 'merchant.signature' )
		);
	}

	public function authorize(){
//		$this->session->clear();
		$price		= $this->shop->calculateOrderTotalPrice( $this->orderId );
		$paymentId	= $this->provider->requestToken( $this->orderId, $price );
		$payment	= $this->provider->getPayment( $paymentId );
		$this->session->set( 'paymentId', $paymentId );
		$this->session->set( 'paymentToken', $payment->token );
		$mode		= $this->config->get( 'mode' );
		$url		= $this->config->get( 'server.login.'.$mode )."&token=".$payment->token;
		if( $this->config->get( 'option.instantPay' ) )
			$url	.= "&useraction=commit";
		$this->restart( $url, FALSE, NULL, TRUE );
	}

	public function authorized(){
		$token		= $this->env->getRequest()->get( 'token' );
		try{
			$payment	= $this->provider->getPaymentFromToken( $token );
			$this->provider->requestPayerDetails( $payment->paymentId );
			$this->restart( 'pay', TRUE );
		}
		catch( Exception $e){
			die( $e->getMessage() );
			throw new RuntimeException( 'Der Bezahlvorgang kann ohne Login bei PayPal nicht fortgeführt werden.' );
		}
	}

	public function cancelled(){
		$this->session->remove( 'paymentId' );
		$this->session->remove( 'token' );
		$this->restart( './shop/checkout' );
	}

/*	public function checkout(){
		$messenger	= $this->env->getMessenger();
		$paymentId	= $this->session->get( 'paymentId' );
		if( !$paymentId ){
			$messenger->noteError( 'Kein Bezahlvorgang eingeleitet. Weiterleitung zum Warenkorb.' );
			$this->restart( './shop/cart' );
		}
		$payment	= $this->provider->getPayment( $paymentId );
		if( $payment->status < 1 )
			$this->restart( 'authorize', TRUE );
		if( $this->config->get( 'option.instantPay' ) )
			$this->restart( 'pay', TRUE );
		$this->addData( 'cart', $cart );
		$this->addData( 'payment', $payment );
	}*/

	public function pay(){
		$messenger	= $this->env->getMessenger();
		$paymentId	= $this->session->get( 'paymentId' );
		if( !$paymentId ){
			$messenger->noteError( 'Kein Bezahlvorgang eingeleitet. Weiterleitung zum Warenkorb.' );
			$this->restart( './shop/cart' );
		}
		try{
			$payment	= $this->provider->getPayment( $paymentId );
		}
		catch( Exception $e ){
			$messenger->noteError( 'Ungültiger Bezahlvorgang. Weiterleitung zum Warenkorb.' );
			$this->restart( './shop/cart' );
		}
		try{
			$this->provider->finishPayment( $paymentId );
			$this->session->remove( 'paymentId' );
			$this->session->remove( 'token' );
		}
		catch( Exception $e ){
			$messenger->noteErrorFailure( 'Bezahlvorgang gescheitert. Weiterleitung zum Warenkorb.' );
			$this->restart( './shop/cart' );
		}
		$this->shop->setOrderStatus( $payment->orderId, 3 );
		$this->restart( './shop/finish' );
	}
}
?>
