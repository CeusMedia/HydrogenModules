<?php
class View_Helper_Shop_FinishPanel_Bank{

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
//		$this->modelPayment	= new Model_Shop_Payment_Bank( $env );
		$this->modelOrder	= new Model_Shop_Order( $env );
		$this->heading		= 'Bezahlvorgang';
		$this->config		= $env->getConfig()->getAll( 'module.shop_payment_bank.', TRUE );
	}

	public function render(){
		switch( $this->order->paymentMethod ){
			case 'Bank:Transfer':
				return $this->renderTransfer();
		}
	}

	protected function renderTransfer(){
		$bank		= $this->config->getAll( 'bank.', TRUE );

		$facts		= new View_Helper_Mail_Facts( $this->env );
		$facts->add( 'Methode', 'per Vorkasse' );
		$facts->add( 'Bank', $bank->get( 'name' ) );
		$facts->add( 'Inhaber', $bank->get( 'holder' ) );
		$facts->add( 'IBAN', UI_HTML_Tag::create( 'tt', $bank->get( 'iban' ) ) );
		$facts->add( 'BIC', UI_HTML_Tag::create( 'tt', $bank->get( 'bic' ) ) );
		$facts->add( 'Preis', number_format( $this->order->priceTaxed, 2, ',', '' ).' '.$this->order->currency );

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
/*		if( $this->order->paymentId > 0 ){
			$this->payment	= $this->modelPayment->get( $this->order->paymentId );
			if( strlen( $this->payment->object ) )
				$this->payin	= json_decode( $this->payment->object );
		}*/
	}

	public function setOutputFormat( $format ){
		$this->outputFormat	= $format;
	}

	public function setPaymentId( $paymentId ){
		$this->payment	= $this->modelPayment->get( $paymentId );
/*		if( strlen( $this->payment->object ) )
			$this->payin	= json_decode( $this->payment->object );*/
		$this->order	= $this->modelOrder->get( $this->payment->orderId );
	}
}
