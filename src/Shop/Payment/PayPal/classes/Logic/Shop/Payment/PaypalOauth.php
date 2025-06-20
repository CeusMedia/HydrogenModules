<?php /** @noinspection ALL */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\Common\Net\HTTP\Post as HttpPost;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Environment;

class Logic_Shop_Payment_PaypalOauth
{
	protected string $mode;
	protected string $server;

	protected ?string $accessToken		= NULL;
	protected ?string $refreshToken		= NULL;
	protected ?string $redirectUrl		= NULL;
	protected ?string $scope			= NULL;
	protected ?string $clientId			= NULL;
	protected ?string $clientSecret		= NULL;
	protected ?string $paypalOrderId	= NULL;

	//  --  //

	public ?object $latestResponse			= NULL;

	protected Environment $env;

	/**	@var	Model_Shop_Payment_Paypal	$modelPayment 	*/
	protected Model_Shop_Payment_Paypal 	$modelPayment;

	/**	@var	Model_Shop_Order			$modelOrder */
	protected Model_Shop_Order				$modelOrder;

	/**	@var	Dictionary					$config			Module configuration dictionary */
	protected Dictionary $moduleConfig;

	protected ?string $password				= NULL;
	protected ?string $username				= NULL;
	protected ?string $signature			= NULL;

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelPayment	= new Model_Shop_Payment_Paypal( $env );
		$this->modelOrder	= new Model_Shop_Order( $env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.shop_payment_paypal.', TRUE );
		$this->mode			= $this->moduleConfig->get( 'mode', 'test' );

		$strategyConfig		= $this->moduleConfig->getAll( 'strategy.oauth.', TRUE );
		$this->clientId		= $strategyConfig->get( 'auth.client.id' );
		$this->clientSecret	= $strategyConfig->get( 'auth.secret' );

		$apiConfig			= $strategyConfig->getAll( 'server.api.', TRUE );
		$this->server		= $apiConfig->get( $this->mode, $apiConfig->get( 'test' ) );
	}

	/**
	 *	@param		int|string			$orderId
	 *	@return		object|mixed		Response object of request
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		DomainException
	 */
	public function createOrder( int|string $orderId ): object
	{
		if( !$this->accessToken )
			$this->getAccessToken();

		$logicShop		= new Logic_Shop( $this->env );
		$logicBridge	= new Logic_ShopBridge( $this->env );

		$order			= $logicShop->getOrder( $orderId, TRUE );
		if( NULL === $order )
			throw new DomainException( 'Order not found' );
		$customerAccount	= $logicShop->getAccountCustomer( $order->userId );
		$billingAddress		= $customerAccount->addressBilling;
		$deliveryAddress	= $customerAccount->addressDelivery;

		$totalPrice	= 0;
		$totalTax	= 0;
		$items		= [];

		/** @var Entity_Shop_Order_Position $position */
		foreach( $order->positions as $position ){
			$article	= $logicBridge->getArticle( $position->bridgeId, $position->articleId, $position->quantity );
			$taxedPrice	= (float) $article->price->one + (float) $article->tax->one;
			$items[]	= [
				"name"			=> $article->title,
				"description"	=> $article->description,
				"unit_amount"	=> [
					"currency_code"	=> $position->currency,
					"value"			=> number_format( $taxedPrice, 2, '.', '' )
				],
				"quantity"		=> (string) $position->quantity
			];
			$totalPrice	+= $article->price->all;
			$totalTax	+= $article->tax->all;
		}

		$totalTaxedPrice			= $totalPrice + $totalTax;
		$totalTaxedPriceFormatted	= number_format( $totalTaxedPrice, 2, '.', '' );
		$data = [
			'intent'	=> 'CAPTURE',
			'purchase_units'	=> [[
				'amount'	=> [
					'currency_code'	=> 'EUR',
					'value'			=> $totalTaxedPriceFormatted,
					'breakdown'		=> [
						'item_total'		=> [
							'currency_code'	=> 'EUR',
							'value'			=> $totalTaxedPriceFormatted
						]
					]
				],
				'items'	=> $items
			] ],
			'application_context'	=> [
				'return_url'	=> $this->env->url.'shop/payment/paypal/authorized',
				'cancel_url'	=> $this->env->url.'shop/payment/paypal/cancelled'
			]
		];

		$paymentId	= $this->modelPayment->add( [
			'orderId'	=> $orderId,
			'token'		=> '',
			'status'	=> 0,
			'amount'	=> $totalPrice + $totalTax,
			'email'		=> $deliveryAddress->email,
			'firstname'	=> $deliveryAddress->firstname,
			'lastname'	=> $deliveryAddress->surname,
			'country'	=> $deliveryAddress->country,
			'postcode'	=> $deliveryAddress->postcode,
			'city'		=> $deliveryAddress->city,
			'street'	=> $deliveryAddress->street,
			'request'	=> json_encode( $data ),
			'timestamp'	=> time(),
		] );

		$ch = curl_init( "{$this->server}/v2/checkout/orders" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Authorization: Bearer {$this->accessToken}"
		]);
		curl_setopt( $ch, CURLOPT_POST, TRUE );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
		$response	= curl_exec( $ch );
		$httpcode	= curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		$this->modelPayment->edit( $paymentId, [
			'request'	=> json_encode( $data ),
			'response'	=> $response,
		] );

		if( str_starts_with( (string) $httpcode, '4' ) ){
			/** @var object{name: string, message: string, links: array} $result */
			$result	= json_decode( $response );
			throw new RuntimeException( 'Payment request failed: '.$result->message );
		}
		else if( !str_starts_with( (string) $httpcode, '2' ) )
			throw NotSupportedException::create( 'Unsupported response code: '.$httpcode );

		/** @var object{id: string, status: string, links: array} $result */
		$result					= json_decode( $response );
		$this->paypalOrderId	= $result->id;
		$this->latestResponse	= $result;
		$this->modelPayment->edit( $paymentId, ['token' => $result->id] );
		return $result;
	}

	public function finishPayment( int|string $paymentId, int|string $paypalOrderId ): bool
	{
		$paypalOrder	= $this->getPaypalOrder( $paypalOrderId );
		$capture		= $captureResult->purchase_units[0]->payments->captures[0] ?? NULL;

		if( 'COMPLETED' !== $paypalOrder->status )
			throw new RuntimeException( 'Transaction failed' );

		$openPayment	= $this->modelPayment->getByIndices( [
			'paymentId'		=> $paymentId,
//			'transactionId'	=> $capture->id,
			'status'		=> '< 2',
		] );

		if( NULL === $openPayment )
			throw new RuntimeException( 'Payment not open anymore' );

		$this->modelPayment->edit( $paymentId, [
			'status'		=> 2,
			'payerId'		=> $paypalOrder->payer->payer_id,
			'transactionId'	=> $capture->id,
//			'amount'		=> $capture->amount->value,
//			'currency'		=> $capture->amount->currency_code,
		] );
		$this->modelOrder->edit( $openPayment->orderId, [
			'status'		=> Model_Shop_Order::STATUS_PAYED,
			'modifiedAt'	=> time(),
		] );
		return TRUE;
	}

	public function getPayerId( int|string $paymentId ): string
	{
		$payment	= $this->getPayment( $paymentId );
		return $payment->payerId;
	}

	public function getPayment( int|string $paymentId ): object
	{
		$payment	= $this->modelPayment->get( $paymentId );
		if( !$payment )
			throw new InvalidArgumentException( 'No payment with ID "'.$paymentId.'"' );
		return $payment;
	}

	public function getPaymentFromToken( string $token ): object
	{
		$payment	= $this->modelPayment->getByIndex( 'token', $token );
		if( !$payment )
			throw new InvalidArgumentException( 'No payment with token "'.$token.'"' );
		return $payment;
	}

	public function getStatus( int|string $paymentId ): int
	{
		$payment	= $this->getPayment( $paymentId );
		return (int) $payment->status;
	}

	public function getToken( int|string $paymentId ): string
	{
		$payment	= $this->modelPayment->get( $paymentId );
		if( !$payment )
			throw new InvalidArgumentException( 'No payment with ID "'.$paymentId.'"' );
		return $payment->token;
	}

	/**
	 * @param int|string $paymentId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 * @deprecated deprecated?
	 */
	public function requestPayerDetails( int|string $paymentId ): void
	{
		$payment		= $this->getPayment( $paymentId );
		if( NULL === $payment )
			throw new RuntimeException( 'Payment not found' );
		$paypalOrder	= $this->getPaypalOrder( $payment->token );
		if( NULL === $paypalOrder )
			throw new RuntimeException( 'Paypal order of payment not found' );

		$this->getPaypalPaymentDetails( $paypalOrder );
//		...
//		$this->modelPayment->edit( $paymentId, $data );
	}

	/**
	 *	@param		string		$username
	 *	@param		string		$password
	 *	@param		string		$signature
	 *	@return		self
	 *	@throws		InvalidArgumentException
	 *	@deprecated	not needed in this strategy
	 */
	public function setAccount( string $username, string $password, string $signature ): self
	{
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function getAccessToken(): ?string
	{
		$ch	= curl_init( "{$this->server}/v1/oauth2/token" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_USERPWD, "{$this->clientId}:{$this->clientSecret}" );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials" );
		curl_setopt( $ch, CURLOPT_POST, TRUE );

		$response = curl_exec( $ch );
		curl_close( $ch );
		if( !$response )
			throw new RuntimeException('Requesting access token for payment failed' );

		$data	= json_decode( $response );
		$this->accessToken	= $data->access_token;
		return $this->accessToken;
	}

	protected function getPaypalOrder( string $paypalOrderId ): object
	{
		if( !$this->accessToken )
			$this->getAccessToken();

		$ch = curl_init( "{$this->server}/v2/checkout/orders/{$paypalOrderId}/capture" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Authorization: Bearer {$this->accessToken}"
		]);
		curl_setopt( $ch, CURLOPT_POST, TRUE );

		$response = curl_exec( $ch );
		curl_close( $ch );

		if( !$response )
			throw new RuntimeException( 'Reading order failed' );

		$result	= json_decode( $response );
		$this->latestResponse	= $result;
		return $result;
	}

	/**
	 *	Not really needed, but implemented like Logic_Shop_Payment_PaypalRest
	 *	@param		object		$paypalOrder
	 *	@return		array
	 */
	protected function getPaypalPaymentDetails( object $paypalOrder ): array
	{
		$payer		= $paypalOrder->payer ?? NULL;
		$capture	= $paypalOrder->purchase_units[0]->payments->captures[0] ?? NULL;

		return [
			'order_id'      => $paypalOrder->id ?? null,
			'status'        => $paypalOrder->status ?? null,
			'transaction_id'=> $capture->id ?? null,
			'amount'        => $capture->amount->value ?? null,
			'currency'      => $capture->amount->currency_code ?? null,
			'payer_id'      => $payer->payer_id ?? null,
			'payer_email'   => $payer->email_address ?? null,
			'payer_name'    => ($payer->name->given_name ?? '') . ' ' . ($payer->name->surname ?? ''),
			'create_time'   => $capture->create_time ?? null,
			'update_time'   => $capture->update_time ?? null,
		];
	}
}
