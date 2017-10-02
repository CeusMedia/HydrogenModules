<?php
class View_Helper_Mangopay_Entity_Money{

	const FORMAT_AMOUNT_CURRENCY		= '%1$s%3$s';
	const FORMAT_AMOUNT_SPACE_CURRENCY	= '%1$s%2$s%3$s';
	const FORMAT_CURRENCY_AMOUNT		= '%3$s%1$s';
	const FORMAT_CURRENCY_SPACE_AMOUNT	= '%3$s%2$s%1$s';

	const NUMBER_FORMAT_DOT				= 0;
	const NUMBER_FORMAT_COMMA			= 1;

	protected $accuracy			= 2;
	protected $amount			= 0;
	protected $currency			= "EUR";
	protected $env;
	protected $separator		= "&nbsp;";
	protected $format			= self::FORMAT_CURRENCY_SPACE_AMOUNT;
	protected $numberFormat		= self::NUMBER_FORMAT_DOT;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function __toString(){
		return $this->render();
	}

	public function set( \MangoPay\Money $money, $accuracy = NULL ){
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

	public function setNumberFormat( $numberFormat ){
		$this->numberFormat	= $numberFormat;
		return $this;
	}

	public function setSeparator( $separator ){
		$this->separator	= $separator;
		return $this;
	}

	public function render(){
		$price		= number_format(
			$this->amount / 100,
			$this->accuracy,
			$this->numberFormat == self::NUMBER_FORMAT_COMMA ? ',' : '.',
			$this->numberFormat == self::NUMBER_FORMAT_COMMA ? '.' : '´'
		);
		return sprintf( $this->format, $price, $this->separator, $this->currency );
	}
}
?>
