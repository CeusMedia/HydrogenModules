<?php
use \CeusMedia\Bootstrap;

class View_Helper_Input_Resource{

	protected $env;
	protected $inputId;
	protected $modalId;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function __toString(){
		return $this->render();
	}

	public function setInputId( $id ){
		$this->inputId	= $id;
		return $this;
	}

	public function setModalId( $id ){
		$this->modalId	= $id;
		return $this;
	}

	public function render(){
//		$modal			= new View_Helper_Bootstrap_Modal( $this->env );
		$modal			= new Bootstrap\Modal( $this->env );
		$modal->setHeading( 'Auswahl' );
		$modal->setBody( '<div id="'.$this->modalId.'-content"></div><div id="'.$this->modalId.'-loader"><div class="alert alert-info">... Loading ...</div></div>' );
		$modal->setId( $this->modalId );
		$modal->setFade( FALSE );
		return $modal->render();
	}
}


class View_Helper_Input_ResourceTrigger{

	const MODE_IMAGE			= 'image';
	const MODE_STYLE			= 'style';
	const MODE_DOCUMENT			= 'document';

	protected $env;
	protected $modalId;
	protected $mode				= 'image';
	protected $inputId;
	protected $label			= 'select';
	protected $class			= 'btn';
	protected $paths			= array();

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function __toString(){
		return $this->render();
	}

	public function setClass( $class ){
		$this->class	= $class;
		return $this;
	}

	public function setInputId( $inputId ){
		$this->inputId	= $inputId;
		return $this;
	}

	public function setLabel( $label ){
		$this->label	= $label;
		return $this;
	}

	public function setModalId( $modalId ){
		$this->modalId	= $modalId;
		return $this;
	}

	public function setMode( $mode ){
		$this->mode	= $mode;
		return $this;
	}

	public function setPaths( $paths ){
		$this->paths	= $paths;
		return $this;
	}


	public function render(){
		return UI_HTML_Tag::create( 'button', $this->label, array(
			'type'		=> "button",
			'onclick'	=> "HelperInputResource.open(this)",
			'class'		=> $this->class,
		), array(
			'modal-id'	=> $this->modalId,
			'input-id'	=> $this->inputId,
			'mode'		=> $this->mode,
			'paths'		=> join( ',', $this->paths ),
		) );
	}
}
