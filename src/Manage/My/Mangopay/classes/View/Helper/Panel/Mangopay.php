<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

abstract class View_Helper_Panel_Mangopay
{
	protected Environment $env;
	protected Dictionary $options;
	protected array $data			= [];

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->options	= new Dictionary();
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public static function formatMoney( $money, $separator = "&nbsp;", $accuracy = 2 ){
		$helper	= new View_Helper_Mangopay_Entity_Money( NULL );
		$helper->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
		$helper->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );
		$helper->set( $money )->setAccuracy( $accuracy )->setSeparator( $separator );
		return $helper->render();
	}

	public function getOption( $key )
	{
		$this->options->get( $key );
	}

	abstract public function render(): string;

	public static function renderCardNumber( $number ): string
	{
		$helper	= new View_Helper_Mangopay_Entity_CardNumber( NULL );
		return $helper->set( $number )->render();
	}

	public static function renderStatic( Environment $env, $data, $options = [] ): string
	{
		$helper	= new static( $env );
		return $helper->setData( $data )->setOptions( $options )->render();
	}

	public function setData( $data ): self
	{
		$this->data		= $data;
		return $this;
	}

	public function setOption( $key, $value ): self
	{
		$this->options->set( $key, $value );
		return $this;
	}

	public function setOptions( array $options = [] ): self
	{
		$this->options	= new Dictionary( $options );
		return $this;
	}
}
