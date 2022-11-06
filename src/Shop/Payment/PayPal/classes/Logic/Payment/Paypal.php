<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

class Logic_Payment_PayPal
{
	protected Environment $env;
	protected $password;
	protected $username;
	protected $signature;
	public $latestResponse;

	/**	@var	Model_Shop_Payment_Paypal		$model			*/
	protected Model_Shop_Payment_Paypal $model;

	/**	@var	Dictionary				$config			Module configuration dictionary */
	protected Dictionary $config;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->model	= new Model_Shop_Payment_Paypal( $env );
		$this->config	= $this->env->getConfig()->getAll( 'module.shop_payment_paypal.', TRUE );
	}

	public function finishPayment( $paymentId )
	{
		$payment	= $this->getPayment( $paymentId );
		if( !$payment->payerId )
			throw new RuntimeException( 'Payer not logged in' );
		$data		= array(
			'METHOD'		=> 'DoExpressCheckoutPayment',
			'TOKEN'			=> $payment->token,
			'AMT'			=> $payment->amount,
			'PAYERID'		=> $payment->payerId,
			'CURRENCYCODE'	=> 'EUR',
			'PAYMENTACTION'	=> 'Sale',
		);
		try{
			$response	= (object) $this->request( $data );
			if( !( $response->ACK === "Success" ) ){
				$this->latestResponse	= $response;
				throw new RuntimeException( 'Transaction failed' );
			}
			$data		= array(
				'status'	=> 2,
			);
			$this->model->edit( $paymentId, $data );
			return TRUE;
		}
		catch( Exception $e ){
			print( $e->getMessage() );
			print_m( $this->latestResponse );
			die;
		}
	}

	public function getPayerId( $paymentId )
	{
		$payment	= $this->getPayment( $paymentId );
		return $payment->payerId;
	}

	public function getPayment( $paymentId )
	{
		$payment	= $this->model->get( $paymentId );
		if( !$payment )
			throw new InvalidArgumentException( 'No payment with ID "'.$paymentId.'"' );
		return $payment;
	}

	public function getPaymentFromToken( string $token )
	{
		$payment	= $this->model->getByIndex( 'token', $token );
		if( !$payment )
			throw new InvalidArgumentException( 'No payment with token "'.$token.'"' );
		return $payment;
	}

	public function getStatus( $paymentId )
	{
		$payment	= $this->getPaymentData( $paymentId );
		return $payment->status;
	}

	public function getToken( $paymentId )
	{
		$payment	= $this->model->get( $paymentId );
		if( !$payment )
			throw new InvalidArgumentException( 'No payment with ID "'.$paymentId.'"' );
		return $payment->token;
	}

	public function requestPayerDetails( $paymentId )
	{
		$payment	= $this->getPayment( $paymentId );
		$data	= array(
			'METHOD'	=> 'getExpressCheckoutDetails',
			'TOKEN'		=> $payment->token
		);
		$response	= (object) $this->request( $data );
		if( !( $response->ACK === "Success" && !empty( $response->PAYERID ) ) ){
			$this->latestResponse	= $response;
			throw new RuntimeException( 'Requesting details failed' );
		}
		$data		= array(
			'payerId'	=> $response->PAYERID,
			'status'	=> 1,
			'email'		=> $response->EMAIL,
			'firstname'	=> $response->FIRSTNAME,
			'lastname'	=> $response->LASTNAME,
		);
		if( $this->config->get( 'option.shipping' ) !== "none" ){
			$data		+= array(
				'country'	=> $response->SHIPTOCOUNTRYCODE,
				'street'	=> $response->SHIPTOSTREET,
				'city'		=> $response->SHIPTOCITY,
				'postcode'	=> $response->SHIPTOZIP,
			);
		}
		$this->model->edit( $paymentId, $data );
	}

	/**
	 *	Requests token from PayPal and returns payment ID.
	 *	@access		public
	 *	@param		integer		$orderId		ID of shop order
	 *	@param		float		$amount			Total cart price
	 *	@return		integer		Payment ID
	 */
	public function requestToken( $orderId, $amount, $subject = NULL )
	{
		$language		= $this->env->getLanguage();
		$titleCart		= $language->getWords( 'shop/payment/paypal' )['cart']['title'];
		$titleApp		= $language->getWords( 'main' )['main']['title'];
		$taxIncluded	= $this->env->getConfig()->get( 'module.shop.tax.included' );

		$logicShop		= new Logic_Shop( $this->env );
		$logicBridge	= new Logic_ShopBridge( $this->env );

		$order		= $logicShop->getOrder( $orderId, TRUE );
		$customer	= $order->customer;
		$positions	= $order->positions;

$handling	= 0;
$insurance	= 0;

		$shipping	= 0;
		if( isset( $order->shipping ) )
			$shipping	= $order->shipping->priceTaxed;

		$data	= array(
			'SUBJECT'		=> $subject,
			'METHOD'		=> "SetExpressCheckout",
			'LOCALECODE'	=> 'de_DE',
			'RETURNURL'		=> $this->env->url."shop/payment/paypal/authorized",
			'CANCELURL'		=> $this->env->url."shop/payment/paypal/cancelled",
			'ALLOWNOTE'		=> 1,
		);
		if( $this->config->get( 'option.shipping' ) === "none" )
			$data['NOSHIPPING']	= 1;
		$headerOptions	= $this->config->getAll( '', TRUE );
		$data['HDRBACKCOLOR']	= $this->config->get( 'option.header.color.background' );
		$data['HDRBORDERCOLOR']	= $this->config->get( 'option.header.color.border' );
		$data['HDRIMG']			= $this->config->get( 'option.header.image' );
		$data['PAYFLOWCOLOR']	= $this->config->get( 'option.payflow.color.background' );
		$data['LOCALECODE']		= strtoupper( $this->env->getLanguage()->getLanguage() );
		$data['ALLOWNOTE']		= "1";
		$data['FIRSTNAME']		= $customer->firstname;
		$data['LASTNAME']		= $customer->surname;

		$totalPrice	= 0;
		$totalTax	= 0;
		$items		= [];
		foreach( array_values( $positions ) as $nr => $position ){
			$article	= $logicBridge->getArticle( $position->bridgeId, $position->articleId, $position->quantity );
			$totalPrice	+= $article->price->all;
			$totalTax	+= $article->tax->all;
			$items['NUMBER'.$nr]		= $nr;
			$items['NAME'.$nr]			= $article->title;
			$items['DESC'.$nr]			= $article->description;
			$items['QTY'.$nr]			= $position->quantity;
//			$items['ITEMCATEGORY'.$nr]	= 'Digital';
			$items['AMT'.$nr]			= number_format( $article->price->all, 2, ".", "," );
			if( !$taxIncluded )
				$items['TAXAMT'.$nr]	= number_format( $article->tax->all, 2, ".", "," );
		}
		foreach( $items as $key => $value )
			$data['L_PAYMENTREQUEST_0_'.$key]	= $value;

		$total	= $totalPrice + $shipping + $handling + $insurance;
		if( !$taxIncluded ){
			$total	+= $totalTax;
			$data['PAYMENTREQUEST_0_TAXAMT']	= number_format( $totalTax, 2, ".", "," );
		}

		$data['PAYMENTINFO_0_CURRENCYCODE']		= 'EUR';
		$data['PAYMENTREQUEST_0_PAYMENTACTION']	= 'Sale';
		$data['PAYMENTREQUEST_0_CURRENCYCODE']	= 'EUR';
		$data['PAYMENTREQUEST_0_DESC']			= sprintf( $titleCart, $titleApp );
		$data['PAYMENTREQUEST_0_ITEMAMT']		= number_format( $totalPrice, 2, ".", "," );
		$data['PAYMENTREQUEST_0_SHIPPINGAMT']	= number_format( $shipping, 2, ".", "," );;
		$data['PAYMENTREQUEST_0_HANDLINGAMT']	= number_format( $handling, 2, ".", "," );;
		$data['PAYMENTREQUEST_0_INSURANCEAMT']	= number_format( $insurance, 2, ".", "," );;
		$data['PAYMENTREQUEST_0_AMT']			= number_format( $total, 2, ".", "," );

		try{
			$response	= (object) $this->request( $data );
			if( !$response || $response->ACK !== "Success" ){
				$this->latestResponse	= $response;
				print_m( $data );
				print_m( $response );
				throw new RuntimeException( 'Requesting token failed' );
			}
/*			$modelAddress	= new Model_Address( $this->env );
			$address		= $modelAddress->get( array(
				'relationType'	=> 'user',
				'relationId'	=> $this->localUserId,
				'type'			=> Model_Address::TYPE_BILLING,
			) );*/
			$data	= array(
				'orderId'	=> $orderId,
				'token'		=> $response->TOKEN,
				'status'	=> 0,
				'amount'	=> $total,
				'email'		=> $customer->email,
				'firstname'	=> $customer->firstname,
				'lastname'	=> $customer->surname,
				'country'	=> $customer->country,
				'postcode'	=> $customer->postcode,
				'city'		=> $customer->city,
				'street'	=> $customer->street.( $customer->number ? ' '.$customer->number : '' ),
				'timestamp'	=> time(),
			);
			return $this->model->add( $data );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );exit;
			print( $e->getMessage() );
			print_m( $this->latestResponse );
			die;
		}
	}

	public function setAccount( string $username, string $password, string $signature ): self
	{
		if( !strlen( trim( $username ) ) )
			throw new Exception( "Merchant username is missing" );
		if( !strlen( trim( $password ) ) )
			throw new Exception( "Merchant password is missing" );
		if( !strlen( trim( $signature ) ) )
			throw new Exception( "Merchant signature is missing" );
		$this->username	= $username;
		$this->password	= $password;
		$this->signature = $signature;
		return $this;
	}

	protected function request( array $data ): array
	{
		if( !( $this->username && $this->password && $this->signature ) )
			throw new RuntimeException( 'No merchant account set' );
		$data	= array_merge( array(
			'USER'		=> $this->username,
			'PWD'		=> $this->password,
			'SIGNATURE'	=> $this->signature,
			'VERSION'	=> $this->config->get( 'server.api.version' ),
		), $data );
		$mode		= $this->config->get( 'mode' );
		$server		= $this->config->get( 'server.api.'.$mode );
		$response	= Net_HTTP_Post::sendData( $server, $data );
		$data		= [];
		parse_str( $response, $data );
		return $data;
	}
}
