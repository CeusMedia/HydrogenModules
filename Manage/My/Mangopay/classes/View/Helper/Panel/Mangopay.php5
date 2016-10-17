<?php
abstract class View_Helper_Panel_Mangopay{

	public function __construct( $env, $options = array() ){
		$this->env		= $env;
		$this->options	= new ADT_List_Dictionary( $options );
	}

	public function getOption( $key ){
		$this->options->get( $key );
	}

	public function setData( $data ){
		$this->data		= $data;
	}

	public function setOption( $key, $value ){
		$this->options->set( $key, $value );
	}

	abstract public function render();

	static public function renderCardNumber( $number ){
		$pattern	= '/^([^x]+)(x+)(.+)$/i';
		$number		= preg_replace( $pattern, '\\1<small class="muted">\\2</small>\\3', $number );
		return '<tt>'.$number.'</tt>';
	}

	static public function formatMoney( $money, $separator = "&nbsp;", $accuracy = 2 ){
		$price		= number_format( $money->Amount / 100, $accuracy, ',', '.' );
//		$pattern	= '{Amount}&nbsp;{Currency}';
//		return str_replace( array( '{Amount}', '{Currency}' ), array( $price, $money->Currency), $pattern );
		$pattern	= '%2$s'.$separator.'%1$s';
		return sprintf( $pattern, $money->Currency, $price );
	}

	static public function renderStatic( $env, $data, $options = array() ){
		$helper	= new static( $env, $options );
		$helper->setData( $data );
		return $helper->render();
	}
}
?>
