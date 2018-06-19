<?php
class View_Helper_Input_File{

	protected $buttonClass		= 'btn-primary';
	protected $label			= 'durchsuchen';
	protected $name				= 'upload';
	protected $multiple			= FALSE;
	protected $required			= FALSE;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function __toString(){
		return $this->render();
	}

	public function render(){
		$input		= UI_HTML_Tag::create( 'input', NULL, array(
			'type'		=> "file",
			'name'		=> $this->name,
			'class'		=> 'bs-input-file',
			'id'		=> 'input_'.$this->name,
			'multiple'	=> $this->multiple ? 'multiple' : NULL,
		) );
		$toggle		= UI_HTML_Tag::create( 'a', $this->label, array(
			'class'		=> 'btn '.$this->buttonClass.' bs-input-file-toggle',
			'href'		=> "javascript:;"
		) );
		$info		= UI_HTML_Tag::create( 'input', NULL, array(
			'type'		=> 'text',
			'class'		=> 'span12 bs-input-file-info',
			'required'	=> $this->required ? 'required' : NULL
		) );
		$upload		= UI_HTML_Tag::create( 'div', $info.$input.$toggle, array(
			'class'		=> 'span12 input-append bs-input-file',
			'style'		=> 'position: relative;'
		) );
		$container	= UI_HTML_Tag::create( 'div', $upload, array(
			'class'		=> 'row-fluid'
		) );
		return $container;
	}

	static public function renderStatic( CMF_Hydrogen_Environment $env, $name = NULL, $label = NULL, $required = FALSE, $buttonClass = 'btn-primary' ){
		$instance	= new self( $env );
		if( $name )
			$instance->setName( $name );
		if( $label )
			$instance->setLabel( $label );
		return $instance->setRequired( $required )->setButtonClass( $buttonClass );
	}

	public function setButtonClass( $class ){
		$this->class	= $class;
		return $this;
	}

	public function setLabel( $label ){
		$this->label	= $label;
		return $this;
	}

	public function setMultiple( $multiple ){
		$this->multiple	= $multiple;
		return $this;
	}

	public function setName( $name ){
		$this->name		= $name;
		return $this;
	}

	public function setRequired( $boolean ){
		$this->required	= $boolean;
		return $this;
	}
}
?>
