<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_FinishPanel_Mangopay
{
	public const OUTPUT_FORMAT_HTML		= 1;
	public const OUTPUT_FORMAT_TEXT		= 2;

	public const OUTPUT_FORMATS			= [
		self::OUTPUT_FORMAT_HTML,
		self::OUTPUT_FORMAT_TEXT,
	];

	protected Environment $env;
	protected Model_Shop_Payment_Mangopay $modelPayment;
	protected Model_Shop_Order $modelOrder;
	protected $payin;
	protected int $outputFormat			= self::OUTPUT_FORMAT_HTML;
	protected string $listClass			= 'dl-horizontal';
	protected string $heading;
	protected ?object $order		= NULL;
	protected ?object $payment		= NULL;

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelPayment	= new Model_Shop_Payment_Mangopay( $env );
		$this->modelOrder	= new Model_Shop_Order( $env );
		$this->heading		= 'Bezahlung';
	}

	public function render(): string
	{
		if( !$this->payment )
			throw new RuntimeException( 'No payment selected' );
		return match( $this->order->paymentMethod ){
			'MangopayBW'	=> $this->renderBankWire(),
			'MangopayBWW'	=> $this->renderBankWireWeb(),
			'MangopayCCW'	=> $this->renderCreditCardWeb(),
			default			=> '',
		};
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

	public function setOutputFormat( int $format ): self
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

	protected function renderBankWire(): string
	{
		$facts		= new View_Helper_Mail_Facts();
		$facts->add( 'Methode', 'Vorkasse per Überweisung' );
		$facts->add( 'Kontoinhaber', $this->payin->PaymentDetails->BankAccount->OwnerName );
		$facts->add( 'IBAN', $this->payin->PaymentDetails->BankAccount->Details->IBAN );
		$facts->add( 'BIC', $this->payin->PaymentDetails->BankAccount->Details->BIC );
		$facts->add( 'Referenz', $this->payin->PaymentDetails->WireReference );
		$facts->add( 'Preis', number_format( $this->order->price, 2, ',', '' ).' '.$this->order->currency );

		if( self::OUTPUT_FORMAT_HTML === $this->outputFormat )
			return '
<div class="content-panel">
	<h3>'.$this->heading.'</h3>
	<div class="content-panel-inner">
		'.$facts->setFormat( $facts::FORMAT_HTML )->setListClass( $this->listClass )->render().'
		<p>
			Bitte überweisen Sie den Betrag auf das oberhalb genannte Konto!<br/>
			Beachten Sie dabei, <b>unbedingt die Referenz in der Überweisung anzugeben</b>!<br/>
		</p>
	</div>
</div>';

		return PHP_EOL.
View_Helper_Mail_Text::underscore( $this->heading ).PHP_EOL.
$facts->setFormat( $facts::FORMAT_TEXT )->render().PHP_EOL.
PHP_EOL.
'Bitte überweisen Sie den Betrag auf das oberhalb genannte Konto!'.PHP_EOL.
'Beachten Sie dabei, unbedingt die Referenz in der Überweisung anzugeben!'.PHP_EOL;
	}

	protected function renderBankWireWeb(): string
	{
		$facts		= new View_Helper_Mail_Facts();
		$facts->add( 'Methode', 'per Sofortüberweisung' );
		$facts->add( 'Preis', number_format( $this->order->price, 2, ',', '' ).' '.$this->order->currency );

		if( self::OUTPUT_FORMAT_HTML === $this->outputFormat )
			return '
<div class="content-panel">
	<h3>'.$this->heading.'</h3>
	<div class="content-panel-inner">
		'.$facts->setFormat( $facts::FORMAT_HTML )->setListClass( $this->listClass )->render().'
		<p>
			Wir haben den Betrag dankend erhalten.<br/>
		</p>
	</div>
</div>';
		return PHP_EOL.
View_Helper_Mail_Text::underscore( $this->heading ).PHP_EOL.
$facts->setFormat( $facts::FORMAT_TEXT )->render().PHP_EOL.PHP_EOL.
'Wir haben den Betrag dankend erhalten.'.PHP_EOL;
	}

	protected function renderCreditCardWeb(): string
	{
		$facts		= new View_Helper_Mail_Facts();
		$facts->add( 'Methode', 'per Kreditkarte' );
		$facts->add( 'Preis', number_format( $this->order->price, 2, ',', '' ).' '.$this->order->currency );

		if( self::OUTPUT_FORMAT_HTML === $this->outputFormat )
			return '
<div class="content-panel">
	<h3>'.$this->heading.'</h3>
	<div class="content-panel-inner">
		'.$facts->setFormat( $facts::FORMAT_HTML )->setListClass( $this->listClass )->render().'
		<p>
			Wir haben den Betrag dankend erhalten.<br/>
		</p>
	</div>
</div>';
		return PHP_EOL.
View_Helper_Mail_Text::underscore( $this->heading ).PHP_EOL.
$facts->setFormat( $facts::FORMAT_TEXT )->render().PHP_EOL.PHP_EOL.
'Wir haben den Betrag dankend erhalten.'.PHP_EOL;
	}
}
