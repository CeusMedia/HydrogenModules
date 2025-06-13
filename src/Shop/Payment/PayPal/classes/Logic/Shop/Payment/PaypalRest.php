<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Post as HttpPost;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Environment;

class Logic_Shop_Payment_PaypalRest
{
	public ?object $latestResponse		= NULL;

	protected Environment $env;

	/**	@var	Model_Shop_Payment_Paypal		$model			*/
	protected Model_Shop_Payment_Paypal $model;

	/**	@var	Dictionary				$config			Module configuration dictionary */
	protected Dictionary $moduleConfig;

	protected ?string $password			= NULL;
	protected ?string $username			= NULL;
	protected ?string $signature		= NULL;

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->model		= new Model_Shop_Payment_Paypal( $env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.shop_payment_paypal.', TRUE );
	}

	public function finishPayment( int|string $paymentId ): bool
	{
		$payment	= $this->getPayment( $paymentId );
		if( !$payment->payerId )
			throw new RuntimeException( 'Payer not logged in' );
		$data		= [
			'METHOD'		=> 'DoExpressCheckoutPayment',
			'TOKEN'			=> $payment->token,
			'AMT'			=> $payment->amount,
			'PAYERID'		=> $payment->payerId,
			'CURRENCYCODE'	=> 'EUR',
			'PAYMENTACTION'	=> 'Sale',
		];
		try{
			$response	= (object) $this->request( $data );
			if( !( $response->ACK === "Success" ) ){
				$this->latestResponse	= $response;
				throw new RuntimeException( 'Transaction failed' );
			}
			$data		= [
				'status'	=> 2,
			];
			$this->model->edit( $paymentId, $data );
			return TRUE;
		}
		catch( Exception $e ){
			print( $e->getMessage() );
			print_m( $this->latestResponse );
			die;
		}
	}

	public function getPayerId( int|string $paymentId ): string
	{
		$payment	= $this->getPayment( $paymentId );
		return $payment->payerId;
	}

	public function getPayment( int|string $paymentId ): object
	{
		$payment	= $this->model->get( $paymentId );
		if( !$payment )
			throw new InvalidArgumentException( 'No payment with ID "'.$paymentId.'"' );
		return $payment;
	}

	public function getPaymentFromToken( string $token ): object
	{
		$payment	= $this->model->getByIndex( 'token', $token );
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
		$payment	= $this->model->get( $paymentId );
		if( !$payment )
			throw new InvalidArgumentException( 'No payment with ID "'.$paymentId.'"' );
		return $payment->token;
	}

	public function requestPayerDetails( int|string $paymentId ): void
	{
		$payment	= $this->getPayment( $paymentId );
		$data	= [
			'METHOD'	=> 'getExpressCheckoutDetails',
			'TOKEN'		=> $payment->token
		];
		$response	= (object) $this->request( $data );
		if( !( $response->ACK === "Success" && !empty( $response->PAYERID ) ) ){
			$this->latestResponse	= $response;
			throw new RuntimeException( 'Requesting details failed' );
		}
		$data		= [
			'payerId'	=> $response->PAYERID,
			'status'	=> 1,
			'email'		=> $response->EMAIL,
			'firstname'	=> $response->FIRSTNAME,
			'lastname'	=> $response->LASTNAME,
		];
		if( $this->moduleConfig->get( 'strategy.rest.option.shipping' ) !== "none" ){
			$data		+= [
				'country'	=> $response->SHIPTOCOUNTRYCODE,
				'street'	=> $response->SHIPTOSTREET,
				'city'		=> $response->SHIPTOCITY,
				'postcode'	=> $response->SHIPTOZIP,
			];
		}
		$this->model->edit( $paymentId, $data );
	}

	/**
	 *	Requests token from PayPal and returns payment ID.
	 *	@access		public
	 *	@param		int|string		$orderId		ID of shop order
	 *	@param		float			$amount			Total cart price
	 *	@param		string|NULL		$subject
	 *	@return		string			Payment ID
	 */
	public function requestToken( int|string $orderId, float $amount, ?string $subject = NULL ): string
	{
		$language		= $this->env->getLanguage();
		$titleCart		= $language->getWords( 'shop/payment/paypal' )['cart']['title'];
		$titleApp		= $language->getWords( 'main' )['main']['title'];
		$taxIncluded	= $this->env->getConfig()->get( 'module.resource_shop.tax.included' );

		$logicShop		= new Logic_Shop( $this->env );
		$logicBridge	= new Logic_ShopBridge( $this->env );

		$order			= $logicShop->getOrder( $orderId, TRUE );
		$customerAccount	= $logicShop->getAccountCustomer( $order->userId );
		$billingAddress		= $customerAccount->addressBilling;
		$deliveryAddress	= $customerAccount->addressDelivery;

		$handling	= 0;
		$insurance	= 0;
		$shipping	= 0;

		if( isset( $order->shipping ) )
			$shipping	= $order->shipping->priceTaxed;

		$data	= [
			'SUBJECT'		=> $subject,
			'METHOD'		=> "SetExpressCheckout",
			'LOCALECODE'	=> 'de_DE',
			'RETURNURL'		=> $this->env->url."shop/payment/paypal/authorized",
			'CANCELURL'		=> $this->env->url."shop/payment/paypal/cancelled",
			'ALLOWNOTE'		=> 1,
		];
		$strategyConfig		= $this->moduleConfig->getAll( 'strategy.rest.', TRUE );

		if( 'none' === $strategyConfig->get( 'option.shipping' ) )
			$data['NOSHIPPING']	= 1;
		$data['HDRBACKCOLOR']	= $strategyConfig->get( 'option.header.color.background' );
		$data['HDRBORDERCOLOR']	= $strategyConfig->get( 'option.header.color.border' );
		$data['HDRIMG']			= $strategyConfig->get( 'option.header.image' );
		$data['PAYFLOWCOLOR']	= $strategyConfig->get( 'option.payflow.color.background' );
		$data['LOCALECODE']		= strtoupper( $this->env->getLanguage()->getLanguage() );
		$data['ALLOWNOTE']		= "1";
		$data['FIRSTNAME']		= $deliveryAddress->firstname;
		$data['LASTNAME']		= $deliveryAddress->surname;

		$totalPrice	= 0;
		$totalTax	= 0;
		$items		= [];
		foreach( array_values( $order->positions ) as $nr => $position ){
			$article	= $logicBridge->getArticle( $position->bridgeId, $position->articleId, $position->quantity );
			$totalPrice	+= $article->price->all;
			$totalTax	+= $article->tax->all;
			$items['NUMBER'.$nr]		= $nr;
			$items['NAME'.$nr]			= $article->title;
			$items['DESC'.$nr]			= $article->description;
			$items['QTY'.$nr]			= $position->quantity;
//			$items['ITEMCATEGORY'.$nr]	= 'Digital';
			$items['AMT'.$nr]			= number_format( $article->price->all, 2 );
			if( !$taxIncluded )
				$items['TAXAMT'.$nr]	= number_format( $article->tax->all, 2 );
		}
		foreach( $items as $key => $value )
			$data['L_PAYMENTREQUEST_0_'.$key]	= $value;

		$total	= $totalPrice + $shipping + $handling + $insurance;
		if( !$taxIncluded ){
			$total	+= $totalTax;
			$data['PAYMENTREQUEST_0_TAXAMT']	= number_format( $totalTax, 2 );
		}

		$data['PAYMENTINFO_0_CURRENCYCODE']		= 'EUR';
		$data['PAYMENTREQUEST_0_PAYMENTACTION']	= 'Sale';
		$data['PAYMENTREQUEST_0_CURRENCYCODE']	= 'EUR';
		$data['PAYMENTREQUEST_0_DESC']			= sprintf( $titleCart, $titleApp );
		$data['PAYMENTREQUEST_0_ITEMAMT']		= number_format( $totalPrice, 2 );
		$data['PAYMENTREQUEST_0_SHIPPINGAMT']	= number_format( $shipping, 2 );
		$data['PAYMENTREQUEST_0_HANDLINGAMT']	= number_format( $handling, 2 );
		$data['PAYMENTREQUEST_0_INSURANCEAMT']	= number_format( $insurance, 2 );
		$data['PAYMENTREQUEST_0_AMT']			= number_format( $total, 2 );

		$paymentId	= $this->model->add( [
			'orderId'	=> $orderId,
			'status'	=> 0,
			'amount'	=> $total,
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

		$response	= (object) $this->request( $data );
		$this->latestResponse	= $response;

		$this->model->edit( $paymentId, [
			'token'		=> $response->TOKEN,
			'response'	=> json_encode( $response ),
		] );

		if( !$response || $response->ACK !== "Success" )
			throw new RuntimeException( 'Requesting token failed' );

		return $paymentId;
	}

	/**
	 *	@param		string		$username
	 *	@param		string		$password
	 *	@param		string		$signature
	 *	@return		self
	 *	@throws		InvalidArgumentException
	 */
	public function setAccount( string $username, string $password, string $signature ): self
	{
		if( !strlen( trim( $username ) ) )
			throw new InvalidArgumentException( "Merchant username is missing" );
		if( !strlen( trim( $password ) ) )
			throw new InvalidArgumentException( "Merchant password is missing" );
		if( !strlen( trim( $signature ) ) )
			throw new InvalidArgumentException( "Merchant signature is missing" );
		$this->username		= $username;
		$this->password		= $password;
		$this->signature	= $signature;
		return $this;
	}

	protected function request( array $data ): array
	{
		if( !( $this->username && $this->password && $this->signature ) )
			throw new RuntimeException( 'No merchant account set' );

		$strategyConfig	= $this->moduleConfig->getAll( 'strategy.rest.', TRUE );
		$mode		= $this->moduleConfig->get( 'mode' );
		$server		= $strategyConfig->get( 'server.api.'.$mode );
		$response	= HttpPost::sendData( $server, array_merge( [
			'USER'		=> $this->username,
			'PWD'		=> $this->password,
			'SIGNATURE'	=> $this->signature,
			'VERSION'	=> $strategyConfig->get( 'server.api.version' ),
		], $data ) );
		$data		= [];
		parse_str( $response, $data );
		return $data;
	}
}
