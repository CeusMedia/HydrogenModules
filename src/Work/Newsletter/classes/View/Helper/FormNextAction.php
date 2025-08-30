<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_FormNextAction implements Stringable
{
	/** @var array<object{value: string|int, label: string, disabled: bool}> $actions */
	protected array $actions					= [];

	protected string|int|float $currentValue	= '';

	protected string $inputName					= 'nextAction';

	protected string $heading					= '';

	/**
	 *	@param		string|int|float	$value
	 *	@param		string				$label
	 *	@param		bool				$disabled
	 *	@return		self
	 */
	public function addAction( string|int|float $value, string $label, bool $disabled = FALSE ): self
	{
		$this->actions[]	= (object) [
			'value'		=> $value,
			'label'		=> $label,
			'disabled'	=> $disabled,
		];
		return $this;
	}

	/**
	 *	@param		string		$heading
	 *	@return		self
	 */
	public function setHeading( string $heading ): self
	{
		$this->heading	= $heading;
		return $this;
	}

	/**
	 *	@param		string		$name
	 *	@return		self
	 */
	public function setName( string $name ): self
	{
		$this->inputName	= $name;
		return $this;
	}

	/**
	 *	@param		string|int|float	$value
	 *	@return		self
	 */
	public function setSelected( string|int|float $value ): self
	{
		$this->currentValue	= $value;
		return $this;
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		$list	= [];
		/** @var object{value: string|int|float, label: string, disabled: bool} $action */
		foreach( $this->actions as $action ){
			$inputAttributes	= [
				'type'		=> 'radio',
				'name'		=> $this->inputName,
				'value'		=> $action->value,
			];
			$labelAttributes	= [
				'class'		=> 'radio',
			];
			if( $action->disabled ){
				$labelAttributes['class']	.= ' muted';
				$inputAttributes['disabled'] = 'disabled';
			}
			if( $this->currentValue === $action->value )
				$inputAttributes['checked']	= 'checked';
			$input	= HtmlTag::create( 'input', NULL, $inputAttributes );
			$list[]	= HtmlTag::create( 'label', $input.'&nbsp;'.$action->label, $labelAttributes );
		}
		if( '' !== trim( $this->heading ) )
			array_unshift( $list, $this->heading );
		return HtmlTag::create( 'div', $list, ['class' => 'form-next-action'] );
	}

	/**
	 *	@return		string
	 */
	public function __toString(): string
	{
		return $this->render();
	}
}
