<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Mangopay_Input_Amount extends View_Helper_Mangopay_Abstract{

	protected $amount;
	protected $min				= 0;
	protected $max				= NULL;
	protected $step				= '0.01';

	protected $id				= 'input_amount';
	protected string $name				= 'amount';
	protected $class			= 'span12';
	protected $required			= 'required';

	public function render(){
		return HtmlTag::create( 'input', NULL, array(
			'type'		=> 'number',
			'step'		=> $this->step,
			'min'		=> $this->min,
			'max'		=> $this->max,
			'name'		=> $this->name,
			'id'		=> $this->id,
			'class'		=> $this->class,
			'required'	=> $this->required,
			'value'		=> htmlentities( $this->amount, ENT_QUOTES, 'UTF-8' ),
		) );
	}

	public function set( $amount ){
		$this->amount	= $amount;
		return $this;
	}

	public function setClass( $class ){
		$this->class	= $class;
		return $this;
	}

	public function setMax( $max ){
		$this->max		= $max;
		return $this;
	}

	public function setMin( $min ){
		$this->min		= $min;
		return $this;
	}

	public function setName( $name ){
		$this->id		= 'input_'.$name;
		$this->name		= $name;
		return $this;
	}
}
