<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Mangopay_Input_Amount extends View_Helper_Mangopay_Abstract
{
	protected float $amount			= .0;
	protected float $min			= .0;
	protected ?float $max			= NULL;
	protected string $step			= '0.01';

	protected string $id			= 'input_amount';
	protected string $name			= 'amount';
	protected string $class			= 'span12';
	protected string $required		= 'required';

	public function render(): string
	{
		return HtmlTag::create( 'input', NULL, [
			'type'		=> 'number',
			'step'		=> $this->step,
			'min'		=> $this->min,
			'max'		=> $this->max,
			'name'		=> $this->name,
			'id'		=> $this->id,
			'class'		=> $this->class,
			'required'	=> $this->required,
			'value'		=> htmlentities( $this->amount, ENT_QUOTES, 'UTF-8' ),
		] );
	}

	public function set( float $amount ): self
	{
		$this->amount	= $amount;
		return $this;
	}

	public function setClass( string $class ): self
	{
		$this->class	= $class;
		return $this;
	}

	public function setMax( float $max ): self
	{
		$this->max		= $max;
		return $this;
	}

	public function setMin( float $min ): self
	{
		$this->min		= $min;
		return $this;
	}

	public function setName( string $name ): self
	{
		$this->id		= 'input_'.$name;
		$this->name		= $name;
		return $this;
	}
}
