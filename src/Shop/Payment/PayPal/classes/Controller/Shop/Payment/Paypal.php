<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Shop_Payment_Paypal extends Controller
{
	/**	@var	Dictionary					$config			Module configuration dictionary */
	protected Dictionary $config;

	/**	@var	Logic_Shop_Payment_PaypalRest|Logic_Shop_Payment_PaypalOauth	$logicProvider		Payment provider logic instance */
	protected Logic_Shop_Payment_Paypal|Logic_Shop_Payment_PaypalOauth $logicProvider;

	/**	@var	Logic_Shop					$shop			Shop logic instance */
	protected Logic_Shop $logicShop;

	/**	@var	Dictionary					$session		Session resource */
	protected Dictionary $session;

	protected MessengerResource $messenger;

	protected ?string $orderId				= NULL;
	protected ?object $order				= NULL;
	protected string $strategy				= 'rest';

	public const STRATEGY_REST		= 'rest';
	public const STRATEGY_OAUTH		= 'oauth';


	public function authorize(): void
	{
		if( self::STRATEGY_REST === $this->strategy ){
			$price		= $this->order->priceTaxed;

			/** @var Logic_Shop_Payment_PaypalRest $provider */
			$provider	= $this->logicProvider;
			$paymentId	= $provider->requestToken( $this->orderId, $price );
			$payment	= $provider->getPayment( $paymentId );
			$this->session->set( 'paymentId', $paymentId );
			$this->session->set( 'paymentToken', $payment->token );
			$mode		= $this->config->get( 'mode' );
			$url		= $this->config->get( 'server.login.'.$mode )."&token=".$payment->token;
			if( $this->config->get( 'option.instantPay' ) )
				$url	.= "&useraction=commit";
			$this->restart( $url, FALSE, NULL, TRUE );
		}
		else if( self::STRATEGY_OAUTH === $this->strategy ){
			/** @var Logic_Shop_Payment_PaypalOauth $provider */
			$provider	= $this->logicProvider;
			$response	= $provider->createOrder( $this->orderId );

			$payment	= $provider->getPaymentFromToken( $response->id );
			$this->session->set( 'paymentId', $payment->paymentId );
			$this->session->set( 'paymentToken', $payment->token );
			$this->session->set( 'paypalOrderId', $response->id );

			foreach( $response->links as $link )
				if( 'approve' === $link->rel )
					$this->restart( $link->href, FALSE, NULL, TRUE );
		}
	}

	public function authorized(): void
	{
		$token		= $this->env->getRequest()->get( 'token' );
		$payment	= $this->logicProvider->getPaymentFromToken( $token );

		if( self::STRATEGY_REST === $this->strategy ){
			/** @var Logic_Shop_Payment_PaypalRest $provider */
			$provider	= $this->logicProvider;
			try{
				$provider->requestPayerDetails( $payment->paymentId );
				$this->restart( 'pay', TRUE );
			}
			catch( Exception $e ){
				die( $e->getMessage() );
				throw new RuntimeException( 'Der Bezahlvorgang kann ohne Login bei PayPal nicht fortgeführt werden.' );
			}
		}
		else if( self::STRATEGY_OAUTH === $this->strategy ){
			/** @var Logic_Shop_Payment_PaypalOauth $provider */
			$provider	= $this->logicProvider;
			$provider->finishPayment( $payment->paymentId, $token );
			$this->session->remove( 'paymentId' );
			$this->session->remove( 'token' );
			$this->restart( './shop/finish' );
		}
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function cancelled(): void
	{
		$paymentId	= $this->session->get( 'paymentId' );
		$token		= $this->env->getRequest()->get( 'token' );
		$payment	= $this->logicProvider->getPaymentFromToken( $token );
		$this->logicShop->setOrderStatus( $payment->orderId, Model_Shop_Order::STATUS_CANCELLED );
		if( self::STRATEGY_REST === $this->strategy ){
			$this->session->remove( 'paymentId' );
			$this->session->remove( 'token' );
			$this->restart( './shop/checkout' );
		}
		else if( self::STRATEGY_OAUTH === $this->strategy ){
			$this->session->remove( 'paymentId' );
			$this->session->remove( 'token' );
			$this->restart( './shop/checkout' );
		}
	}

/*	public function checkout(){
		$messenger	= $this->env->getMessenger();
		$paymentId	= $this->session->get( 'paymentId' );
		if( !$paymentId ){
			$messenger->noteError( 'Kein Bezahlvorgang eingeleitet. Weiterleitung zum Warenkorb.' );
			$this->restart( './shop/cart' );
		}
		$payment	= $this->logicProvider->getPayment( $paymentId );
		if( $payment->status < 1 )
			$this->restart( 'authorize', TRUE );
		if( $this->config->get( 'option.instantPay' ) )
			$this->restart( 'pay', TRUE );
		$this->addData( 'cart', $cart );
		$this->addData( 'payment', $payment );
	}*/

	public function pay(): void
	{
		$messenger	= $this->env->getMessenger();
		$paymentId	= $this->session->get( 'paymentId' );
		if( !$paymentId ){
			$messenger->noteError( 'Kein Bezahlvorgang eingeleitet. Weiterleitung zum Warenkorb.' );
			$this->restart( './shop/cart' );
		}

		try{
			$payment	= $this->logicProvider->getPayment( $paymentId );
			$this->logicShop->setOrderStatus( $payment->orderId, 3 );
		}
		catch( Exception $e ){
			$messenger->noteError( 'Ungültiger Bezahlvorgang. Weiterleitung zum Warenkorb.' );
			$this->restart( './shop/cart' );
		}
		if( self::STRATEGY_REST === $this->strategy ){
			try{
				$this->logicProvider->finishPayment( $paymentId );
				$this->session->remove( 'paymentId' );
				$this->session->remove( 'token' );
				$this->restart( './shop/finish' );
			}
			catch( Exception $e ){
				$messenger->noteError( 'Bezahlvorgang gescheitert. Weiterleitung zum Warenkorb.' );
				$this->restart( './shop/cart' );
			}
		}
		else if( self::STRATEGY_OAUTH === $this->strategy ){
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig()->getAll( 'module.shop_payment_paypal.', TRUE );
		$this->strategy		= strtolower( $this->config->get( 'strategy', 'REST' ) );
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logicShop	= new Logic_Shop( $this->env );

		$modelCart			= new Model_Shop_Cart( $this->env );
		$this->orderId		= $modelCart->get( 'orderId' );
		if( !$this->orderId ){
			$this->messenger->noteError( 'Invalid order' );
			$this->restart( 'shop' );
		}
		$this->order		= $this->logicShop->getOrder( $this->orderId );

		if( self::STRATEGY_REST === $this->strategy ){
			$this->logicProvider	= new Logic_Shop_Payment_PaypalRest( $this->env );
			$this->logicProvider->setAccount(
				$this->config->get( 'merchant.username' ),
				$this->config->get( 'merchant.password' ),
				$this->config->get( 'merchant.signature' )
			);
		}
		else if( self::STRATEGY_OAUTH === $this->strategy ){
			$this->logicProvider	= new Logic_Shop_Payment_PaypalOauth( $this->env );

		}
	}
}
