<?php
class View_Helper_Shop_FinishPanel_Mangopay
{
	const OUTPUT_FORMAT_HTML		= 1;
	const OUTPUT_FORMAT_TEXT		= 2;

	const OUTPUT_FORMATS			= [
		self::OUTPUT_FORMAT_HTML,
		self::OUTPUT_FORMAT_TEXT,
	];

	protected $env;
	protected $modelPayment;
	protected $modelOrder;
	protected $payin;
	protected $outputFormat			= self::OUTPUT_FORMAT_HTML;
	protected $listClass			= 'dl-horizontal';

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env			= $env;
		$this->modelPayment	= new Model_Shop_Payment_Mangopay( $env );
		$this->modelOrder	= new Model_Shop_Order( $env );
		$this->heading		= 'Bezahlung';
	}

	public function render()
	{
		if( !$this->payment )
			throw new RuntimeException( 'No payment selected' );
		switch( $this->order->paymentMethod ){
			case 'MangopayBW':
				return $this->renderBankWire();
			case 'MangopayBWW':
				return $this->renderBankWireWeb();
			case 'MangopayCCW':
				return $this->renderCreditCardWeb();
		}
	}

	public function setListClass( $class ): self
	{
		$this->listClass	= $class;
		return $this;
	}

	public function setOrderId( $orderId ): self
	{
		$this->order	= $this->modelOrder->get( $orderId );
		if( $this->order->paymentId > 0 ){
			$this->payment	= $this->modelPayment->get( $this->order->paymentId );
			if( strlen( $this->payment->object ) )
				$this->payin	= json_decode( $this->payment->object );
		}
		return $this;
	}

	public function setOutputFormat( $format ): self
	{
		$this->outputFormat	= $format;
		return $this;
	}

	public function setPaymentId( $paymentId ): self
	{
		$this->payment	= $this->modelPayment->get( $paymentId );
		if( strlen( $this->payment->object ) )
			$this->payin	= json_decode( $this->payment->object );
		$this->order	= $this->modelOrder->get( $this->payment->orderId );
		return $this;
	}

	protected function renderBankWire()
	{
		$facts		= new View_Helper_Mail_Facts( $this->env );
		$facts->add( 'Methode', 'Vorkasse per Überweisung' );
		$facts->add( 'Kontoinhaber', $this->payin->PaymentDetails->BankAccount->OwnerName );
		$facts->add( 'IBAN', $this->payin->PaymentDetails->BankAccount->Details->IBAN );
		$facts->add( 'BIC', $this->payin->PaymentDetails->BankAccount->Details->BIC );
		$facts->add( 'Referenz', $this->payin->PaymentDetails->WireReference );
		$facts->add( 'Preis', number_format( $this->order->price, 2, ',', '' ).' '.$this->order->currency );

		if( $this->outputFormat == SELF::OUTPUT_FORMAT_HTML )
			return '
<div class="content-panel">
	<h3>'.$this->heading.'</h3>
	<div class="content-panel-inner">
		'.$facts->render( $this->listClass ).'
		<p>
			Bitte überweisen Sie den Betrag auf das oberhalb genannte Konto!<br/>
			Beachten Sie dabei, <b>unbedingt die Referenz in der Überweisung anzugeben</b>!<br/>
		</p>
	</div>
</div>';

		return PHP_EOL.
View_Helper_Mail_Text::underscore( $this->heading ).PHP_EOL.
$facts->renderAsText().PHP_EOL.
PHP_EOL.
'Bitte überweisen Sie den Betrag auf das oberhalb genannte Konto!'.PHP_EOL.
'Beachten Sie dabei, unbedingt die Referenz in der Überweisung anzugeben!'.PHP_EOL;
	}

	protected function renderBankWireWeb()
	{
		$facts		= new View_Helper_Mail_Facts( $this->env );
		$facts->add( 'Methode', 'per Sofortüberweisung' );
		$facts->add( 'Preis', number_format( $this->order->price, 2, ',', '' ).' '.$this->order->currency );

		if( $this->outputFormat == SELF::OUTPUT_FORMAT_HTML )
			return '
<div class="content-panel">
	<h3>'.$this->heading.'</h3>
	<div class="content-panel-inner">
		'.$facts->render( $this->listClass ).'
		<p>
			Wir haben den Betrag dankend erhalten.<br/>
		</p>
	</div>
</div>';
		return PHP_EOL.
View_Helper_Mail_Text::underscore( $this->heading ).PHP_EOL.
$facts->renderAsText().PHP_EOL.PHP_EOL.
'Wir haben den Betrag dankend erhalten.'.PHP_EOL;
	}

	protected function renderCreditCardWeb()
	{
		$facts		= new View_Helper_Mail_Facts( $this->env );
		$facts->add( 'Methode', 'per Kreditkarte' );
		$facts->add( 'Preis', number_format( $this->order->price, 2, ',', '' ).' '.$this->order->currency );

		if( $this->outputFormat == SELF::OUTPUT_FORMAT_HTML )
			return '
<div class="content-panel">
	<h3>'.$this->heading.'</h3>
	<div class="content-panel-inner">
		'.$facts->render( $this->listClass ).'
		<p>
			Wir haben den Betrag dankend erhalten.<br/>
		</p>
	</div>
</div>';
		return PHP_EOL.
View_Helper_Mail_Text::underscore( $this->heading ).PHP_EOL.
$facts->renderAsText().PHP_EOL.PHP_EOL.
'Wir haben den Betrag dankend erhalten.'.PHP_EOL;
	}
}
