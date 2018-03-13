<?php
class View_Helper_Shop_FinishPanel_Stripe{

	const OUTPUT_FORMAT_HTML		= 1;
	const OUTPUT_FORMAT_TEXT		= 2;

	protected $env;
	protected $modelPayment;
	protected $modelOrder;
	protected $payin;
	protected $outputFormat			= self::OUTPUT_FORMAT_HTML;
	protected $listClass			= 'dl-horizontal';

	public function __construct( $env ){
		$this->env			= $env;
		$this->modelPayment	= new Model_Shop_Payment_Stripe( $env );
		$this->modelOrder	= new Model_Shop_Order( $env );
		$this->heading		= 'Bezahlvorgang';
	}

	public function render(){
		if( !$this->payment )
			throw new RuntimeException( 'No payment selected' );

		switch( $this->order->paymentMethod ){
			case 'Stripe:Card':
				return $this->renderCreditCard();
			case 'Stripe:Giropay':
				return $this->renderGiropay();
			case 'Stripe:Sofort':
				return $this->renderSofort();
		}
	}

	protected function renderCreditCard(){
		$facts		= new View_Helper_Mail_Facts( $this->env );
		$facts->add( 'Methode', 'per Kreditkarte' );
		$facts->add( 'Preis', number_format( $this->order->priceTaxed, 2, ',', '' ).' '.$this->order->currency );
		$facts->add( 'Status', 'Wir haben den Betrag dankend erhalten.' );

		if( $this->outputFormat == SELF::OUTPUT_FORMAT_HTML )
			return UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'h3', $this->heading ),
					$facts->render( $this->listClass ),
				), array( 'class' => 'content-panel-inner' ) ),
			), array( 'class' => 'content-panel' ) );

		return PHP_EOL.join( PHP_EOL, array(
			View_Helper_Mail_Text::underscore( $this->heading ),
			$facts->renderAsText(),
		) ).PHP_EOL.PHP_EOL;
	}

	protected function renderGiropay(){
		$facts		= new View_Helper_Mail_Facts( $this->env );
		$facts->add( 'Methode', 'per GiroPay' );
		$facts->add( 'Preis', number_format( $this->order->priceTaxed, 2, ',', '' ).' '.$this->order->currency );
		$facts->add( 'Status', 'Wir haben den Betrag dankend erhalten.' );

		if( $this->outputFormat == SELF::OUTPUT_FORMAT_HTML )
			return UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'h3', $this->heading ),
					$facts->render( $this->listClass ),
				), array( 'class' => 'content-panel-inner' ) ),
			), array( 'class' => 'content-panel' ) );

		return PHP_EOL.join( PHP_EOL, array(
			View_Helper_Mail_Text::underscore( $this->heading ),
			$facts->renderAsText(),
		) ).PHP_EOL.PHP_EOL;
	}

	protected function renderSofort(){
		$facts		= new View_Helper_Mail_Facts( $this->env );
		$facts->add( 'Methode', 'per Sofortüberweisung' );
		$facts->add( 'Preis', number_format( $this->order->priceTaxed, 2, ',', '' ).' '.$this->order->currency );
		$facts->add( 'Status', 'Wir haben den Betrag dankend erhalten.' );

		if( $this->outputFormat == SELF::OUTPUT_FORMAT_HTML )
			return UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'h3', $this->heading ),
					$facts->render( $this->listClass ),
				), array( 'class' => 'content-panel-inner' ) ),
			), array( 'class' => 'content-panel' ) );

		return PHP_EOL.join( PHP_EOL, array(
			View_Helper_Mail_Text::underscore( $this->heading ),
			$facts->renderAsText(),
		) ).PHP_EOL.PHP_EOL;
	}

	public function setListClass( $class ){
		$this->listClass	= $class;
	}

	public function setOrderId( $orderId ){
		$this->order	= $this->modelOrder->get( $orderId );
		if( $this->order->paymentId > 0 ){
			$this->payment	= $this->modelPayment->get( $this->order->paymentId );
			if( strlen( $this->payment->object ) )
				$this->payin	= json_decode( $this->payment->object );
		}
	}

	public function setOutputFormat( $format ){
		$this->outputFormat	= $format;
	}

	public function setPaymentId( $paymentId ){
		$this->payment	= $this->modelPayment->get( $paymentId );
		if( strlen( $this->payment->object ) )
			$this->payin	= json_decode( $this->payment->object );
		$this->order	= $this->modelOrder->get( $this->payment->orderId );
	}
}
