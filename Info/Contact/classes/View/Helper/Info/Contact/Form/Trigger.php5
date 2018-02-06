<?php
class View_Helper_Info_Contact_Form_Trigger{

	protected $class		= 'btn';
	protected $icon;
	protected $iconPosition	= 'left';
	protected $label;
	protected $modalId;

	public function render(){
		if( !$this->modalId )
			throw new RuntimeException( 'No modal ID set' );
		$trigger	= new \CeusMedia\Bootstrap\Modal\Trigger();
		$trigger->setModalId( $this->modalId );
		$trigger->setAttributes( array( 'class' => $this->class ) );

		$label		= $this->label;
		if( $this->icon ){
			$icon	= $this->icon ? UI_HTML_Tag::create( 'i', '', array( 'class' => $this->icon ) ) : '';
			$label	= $icon.'&nbsp;'.$this->label;
			if( $this->iconPosition === 'right' )
				$label	= $this->label.'&nbsp;'.$icon;
		}
		return $trigger->setLabel( $label )->render();
	}

	public function setModalId( $modalId ){
		$this->modalId	= $modalId;
		return $this;
	}

	public function setClass( $class ){
		$this->class	= $class;
		return $this;
	}

	public function setIcon( $icon ){
		$this->icon		= $icon;
		return $this;
	}

	public function setIconPosition( $position = 'left' ){
		$this->iconPosition	= $position;
		return $this;
	}

	public function setLabel( $label ){
		$this->label	= $label;
		return $this;
	}
}
