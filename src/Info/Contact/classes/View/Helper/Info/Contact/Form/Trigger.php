<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Info_Contact_Form_Trigger
{
	protected string $class				= 'btn';
	protected ?string $icon				= NULL;
	protected string $iconPosition		= 'left';
	protected ?string $label			= NULL;
	protected ?string $modalId			= NULL;

	public function render(): string
	{
		if( !$this->modalId )
			throw new RuntimeException( 'No modal ID set' );
		$trigger	= new \CeusMedia\Bootstrap\Modal\Trigger();
		$trigger->setModalId( $this->modalId );
		$trigger->setAttributes( ['class' => $this->class] );

		$label		= $this->label;
		if( $this->icon ){
			$icon	= HtmlTag::create( 'i', '', ['class' => $this->icon] );
			$label	= $icon.'&nbsp;'.$this->label;
			if( $this->iconPosition === 'right' )
				$label	= $this->label.'&nbsp;'.$icon;
		}
		return $trigger->setLabel( $label )->render();
	}

	public function setModalId( string $modalId ): self
	{
		$this->modalId	= $modalId;
		return $this;
	}

	public function setClass( string $class ): self
	{
		$this->class	= $class;
		return $this;
	}

	public function setIcon( string $icon ): self
	{
		$this->icon		= $icon;
		return $this;
	}

	public function setIconPosition( string $position = 'left' ): self
	{
		$this->iconPosition	= $position;
		return $this;
	}

	public function setLabel( string $label ): self
	{
		$this->label	= $label;
		return $this;
	}
}
