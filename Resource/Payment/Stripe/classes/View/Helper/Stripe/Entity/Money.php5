<?php
class View_Helper_Stripe_Entity_Money extends View_Helper_Stripe_Abstract{

	const FORMAT_AMOUNT_CURRENCY		= '%1$s%3$s';
	const FORMAT_AMOUNT_SPACE_CURRENCY	= '%1$s%2$s%3$s';
	const FORMAT_CURRENCY_AMOUNT		= '%3$s%1$s';
	const FORMAT_CURRENCY_SPACE_AMOUNT	= '%3$s%2$s%1$s';

	const NUMBER_FORMAT_DOT				= 0;
	const NUMBER_FORMAT_COMMA			= 1;

	protected $accuracy			= 2;
	protected $amount			= 0;
	protected $currency			= "EUR";
	protected $format			= self::FORMAT_CURRENCY_SPACE_AMOUNT;
	protected $nodeClass		= NULL;
	protected $nodeName			= 'span';
	protected $numberFormat		= self::NUMBER_FORMAT_DOT;
	protected $separator		= "&nbsp;";

	public function render(){
		$price		= number_format(
			$this->amount / 100,
			$this->accuracy,
			$this->numberFormat == self::NUMBER_FORMAT_COMMA ? ',' : '.',
			$this->numberFormat == self::NUMBER_FORMAT_COMMA ? '.' : '´'
		);
		$label	= sprintf( $this->format, $price, $this->separator, $this->currency );
		return UI_HTML_Tag::create( $this->nodeName, $label, array( 'class' => $this->nodeClass ) );
	}

	public function set( \Stripe\Money $money, $accuracy = NULL ){
		$this->setAmount( $money->Amount );
		$this->setCurrency( $money->Currency );
		if( $accuracy !== NULL )
			$this->setAccuracy( $accuracy );
		return $this;
	}

	public function setAccuracy( $accuracy ){
		$this->accuracy	= $accuracy;
		return $this;
	}

	public function setAmount( $amount ){
		$this->amount	= $amount;
		return $this;
	}

	public function setCurrency( $currency ){
		$this->currency	= $currency;
		return $this;
	}

	public function setFormat( $format ){
		$this->format	= $format;
		return $this;
	}

	public function setNodeClass( $classNames ){
		$this->nodeClass	= $classNames;
		return $this;
	}

	public function setNodeName( $nodeName ){
		$this->nodeName	= $nodeName;
		return $this;
	}

	public function setNumberFormat( $numberFormat ){
		$this->numberFormat	= $numberFormat;
		return $this;
	}

	public function setSeparator( $separator ){
		$this->separator	= $separator;
		return $this;
	}
}
?>
