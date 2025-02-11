<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_FinishPanel_Bank
{
	public const OUTPUT_FORMAT_HTML		= 1;
	public const OUTPUT_FORMAT_TEXT		= 2;

	public const OUTPUT_FORMATS			= [
		self::OUTPUT_FORMAT_HTML,
		self::OUTPUT_FORMAT_TEXT,
	];

	protected Environment $env;
//	protected Model_Shop_Payment_Bank $modelPayment;
	protected Model_Shop_Order $modelOrder;

	protected Dictionary $config;
//	protected $payin;
	protected int $outputFormat			= self::OUTPUT_FORMAT_HTML;
	protected string $listClass			= 'dl-horizontal';
	protected string $heading;
	protected ?object $order;
	protected ?object $payment;

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
//		$this->modelPayment	= new Model_Shop_Payment_Bank( $env );
		$this->modelOrder	= new Model_Shop_Order( $env );
		$this->heading		= 'Bezahlvorgang';
		$this->config		= $env->getConfig()->getAll( 'module.shop_payment_bank.', TRUE );
	}

	public function render(): string
	{
		switch( $this->order->paymentMethod ){
			case 'Bank:Transfer':
				return $this->renderTransfer();
		}
		return '';
	}


	public function setListClass( string $class ): self
	{
		$this->listClass	= $class;
		return $this;
	}

	public function setOrderId( $orderId ): self
	{
		$this->order	= $this->modelOrder->get( $orderId );
/*		if( $this->order->paymentId > 0 ){
			$this->payment	= $this->modelPayment->get( $this->order->paymentId );
			if( strlen( $this->payment->object ) )
				$this->payin	= json_decode( $this->payment->object );
		}*/
		return $this;
	}

	public function setOutputFormat( string $format ): self
	{
		$this->outputFormat	= $format;
		return $this;
	}

	public function setPaymentId( $paymentId ): self
	{
//		$this->payment	= $this->modelPayment->get( $paymentId );
/*		if( strlen( $this->payment->object ) )
			$this->payin	= json_decode( $this->payment->object );*/
//		$this->order	= $this->modelOrder->get( $this->payment->orderId );
		return $this;
	}

	protected function renderTransfer(): string
	{
		$bank		= $this->config->getAll( 'bank.', TRUE );

		$facts		= new View_Helper_Mail_Facts();
		$facts->add( 'Methode', 'per Vorkasse' );
		$facts->add( 'Bank', $bank->get( 'name' ) );
		$facts->add( 'Inhaber', $bank->get( 'holder' ) );
		$facts->add( 'IBAN', HtmlTag::create( 'tt', $bank->get( 'iban' ) ) );
		$facts->add( 'BIC', HtmlTag::create( 'tt', $bank->get( 'bic' ) ) );
		$facts->add( 'Preis', number_format( $this->order->priceTaxed, 2, ',', '' ).' '.$this->order->currency );

		if( $this->outputFormat == self::OUTPUT_FORMAT_HTML )
			return HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h3', $this->heading ),
					$facts->setFormat( $facts::FORMAT_HTML )->setListClass( $this->listClass )->render(),
				], ['class' => 'content-panel-inner'] ),
			], ['class' => 'content-panel'] );

		return PHP_EOL.join( PHP_EOL, [
			View_Helper_Mail_Text::underscore( $this->heading ),
			$facts->setFormat( $facts::FORMAT_TEXT )->render(),
		] ).PHP_EOL.PHP_EOL;
	}
}
