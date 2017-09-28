<?php
abstract class View_Helper_Mangopay{

	protected $env;

	public function __construct( $env ){
		$this->env		= $env;
	}

	static public function formatMoney( $money, $separator = "&nbsp;", $accuracy = 2 ){
		$price		= number_format( $money->Amount / 100, $accuracy, ',', '.' );
		$pattern	= '%2$s'.$separator.'%1$s';
		return sprintf( $pattern, $money->Currency, $price );
	}

	static public function renderCardNumber( $number ){
		$pattern	= '/^([^x]+)(x+)(.+)$/i';
		$number		= preg_replace( $pattern, '\\1<small class="muted">\\2</small>\\3', $number );
		return '<tt>'.$number.'</tt>';
	}
}
?>
