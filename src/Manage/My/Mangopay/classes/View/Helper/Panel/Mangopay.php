<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

abstract class View_Helper_Panel_Mangopay{

	protected $data			= [];
	protected $env;
	protected $options;

	public function __construct( $env ){
		$this->env		= $env;
		$this->options	= new Dictionary();
	}

	public function __toString(){
		return $this->render();
	}

	static public function formatMoney( $money, $separator = "&nbsp;", $accuracy = 2 ){
		$helper	= new View_Helper_Mangopay_Entity_Money( NULL );
		$helper->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
		$helper->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );
		$helper->set( $money )->setAccuracy( $accuracy )->setSeparator( $separator );
		return $helper->render();
	}

	public function getOption( $key ){
		$this->options->get( $key );
	}

	abstract public function render();

	static public function renderCardNumber( $number ){
		$helper	= new View_Helper_Mangopay_Entity_CardNumber( NULL );
		return $helper->set( $number )->render();
	}

	static public function renderStatic( Environment $env, $data, $options = [] ){
		$helper	= new static( $env );
		return $helper->setData( $data )->setOptions( $options )->render();
	}

	public function setData( $data ){
		$this->data		= $data;
		return $this;
	}

	public function setOption( $key, $value ){
		$this->options->set( $key, $value );
		return $this;
	}

	public function setOptions( $options = [] ){
		$this->options	= new Dictionary( $options );
		return $this;
	}
}