<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_FinishPanel_Stripe
{
	const OUTPUT_FORMAT_HTML		= 1;
	const OUTPUT_FORMAT_TEXT		= 2;

	const OUTPUT_FORMATS			= [
		self::OUTPUT_FORMAT_HTML,
		self::OUTPUT_FORMAT_TEXT,
	];

	/**
	 * @var		Environment		$env
	 */
	protected Environment $env;

	protected Model_Shop_Payment_Stripe $modelPayment;

	protected Model_Shop_Order $modelOrder;

	protected ?object $payin			= NULL;

	protected int $outputFormat			= self::OUTPUT_FORMAT_HTML;

	protected string $listClass			= 'dl-horizontal';

	protected ?object $payment			= NULL;

	protected ?object $order			= NULL;

	protected string $heading;

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelPayment	= new Model_Shop_Payment_Stripe( $env );
		$this->modelOrder	= new Model_Shop_Order( $env );
		$this->heading		= 'Bezahlvorgang';
	}

	public function render(): string
	{
		if( $this->payment ){
			switch( $this->order->paymentMethod ){
				case 'Stripe:Card':
					return $this->renderCreditCard();
				case 'Stripe:Giropay':
					return $this->renderGiropay();
				case 'Stripe:Sofort':
					return $this->renderSofort();
			}
		}
		return '';
	}

	public function setListClass( string $class ): self
	{
		$this->listClass	= $class;
		return $this;
	}

	public function setOrderId( int|string $orderId ): self
	{
		$this->order	= $this->modelOrder->get( $orderId );
		if( $this->order->paymentId > 0 ){
			$this->payment	= $this->modelPayment->get( $this->order->paymentId );
			if( strlen( $this->payment->object ) )
				$this->payin	= json_decode( $this->payment->object );
		}
		return $this;
	}

	public function setOutputFormat( string $format ): self
	{
		$this->outputFormat	= $format;
		return $this;
	}

	public function setPaymentId( int|string $paymentId ): self
	{
		$this->payment	= $this->modelPayment->get( $paymentId );
		if( strlen( $this->payment->object ) )
			$this->payin	= json_decode( $this->payment->object );
		$this->order	= $this->modelOrder->get( $this->payment->orderId );
		return $this;
	}

	protected function renderCreditCard(): string
	{
		$facts		= new View_Helper_Mail_Facts();
		$facts->add( 'Methode', 'per Kreditkarte' );
		$facts->add( 'Preis', number_format( $this->order->priceTaxed, 2, ',', '' ).' '.$this->order->currency );
		$facts->add( 'Status', 'Wir haben den Betrag dankend erhalten.' );

		if( $this->outputFormat == self::OUTPUT_FORMAT_HTML )
			return HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h3', $this->heading ),
					$facts->setListClass( $this->listClass )->render(),
				], ['class' => 'content-panel-inner'] ),
			], ['class' => 'content-panel'] );

		return PHP_EOL.join( PHP_EOL, [
			View_Helper_Mail_Text::underscore( $this->heading ),
			$facts->setFormat( $facts::FORMAT_TEXT )->render(),
		] ).PHP_EOL.PHP_EOL;
	}

	protected function renderGiropay(): string
	{
		$facts		= new View_Helper_Mail_Facts();
		$facts->add( 'Methode', 'per GiroPay' );
		$facts->add( 'Preis', number_format( $this->order->priceTaxed, 2, ',', '' ).' '.$this->order->currency );
		$facts->add( 'Status', 'Wir haben den Betrag dankend erhalten.' );

		if( $this->outputFormat == self::OUTPUT_FORMAT_HTML )
			return HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h3', $this->heading ),
					$facts->setListClass( $this->listClass )->render(),
				], ['class' => 'content-panel-inner'] ),
			], ['class' => 'content-panel'] );

		return PHP_EOL.join( PHP_EOL, [
			View_Helper_Mail_Text::underscore( $this->heading ),
			$facts->setFormat( $facts::FORMAT_TEXT )->render(),
		] ).PHP_EOL.PHP_EOL;
	}

	protected function renderSofort(): string
	{
		$facts		= new View_Helper_Mail_Facts();
		$facts->add( 'Methode', 'per Sofortüberweisung' );
		$facts->add( 'Preis', number_format( $this->order->priceTaxed, 2, ',', '' ).' '.$this->order->currency );
		$facts->add( 'Status', 'Wir haben den Betrag dankend erhalten.' );

		if( $this->outputFormat == self::OUTPUT_FORMAT_HTML )
			return HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h3', $this->heading ),
					$facts->setListClass( $this->listClass )->render(),
				], ['class' => 'content-panel-inner'] ),
			], ['class' => 'content-panel'] );

		return PHP_EOL.join( PHP_EOL, [
			View_Helper_Mail_Text::underscore( $this->heading ),
			$facts->setFormat( $facts::FORMAT_TEXT )->render(),
		] ).PHP_EOL.PHP_EOL;
	}
}
