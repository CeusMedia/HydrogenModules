<?php
class View_Helper_Input_File
{
	protected $env;
	protected $buttonClass		= 'btn-primary';
	protected $label			= 'durchsuchen';
	protected $name				= 'upload';
	protected $multiple			= FALSE;
	protected $folder			= FALSE;
	protected $required			= FALSE;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env		= $env;
	}

	public function __toString()
	{
		return $this->render();
	}

	public function render(): string
	{
		$input		= UI_HTML_Tag::create( 'input', NULL, array(
			'type'				=> "file",
			'name'				=> $this->name,
			'class'				=> 'bs-input-file',
			'id'				=> 'input_'.$this->name,
			'multiple'			=> $this->multiple ? 'multiple' : NULL,
			'webkitdirectory'	=> $this->folder ? '' : NULL,
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

	public static function renderStatic( CMF_Hydrogen_Environment $env, string $name = NULL, string $label = NULL, bool $required = FALSE, string $buttonClass = 'btn-primary' ): string
	{
		$instance	= new self( $env );
		if( $name )
			$instance->setName( $name );
		if( $label )
			$instance->setLabel( $label );
		$instance->setRequired( $required );
		$instance->setButtonClass( $buttonClass );
		return $instance->render();
	}

	public function setButtonClass( string $class ): self
	{
		$this->buttonClass	= $class;
		return $this;
	}

	public function setFolder( bool $folder ): self
	{
		$this->folder	= $folder;
		return $this;
	}

	public function setLabel( string $label ): self
	{
		$this->label	= $label;
		return $this;
	}

	public function setMultiple( bool $multiple ): self
	{
		$this->multiple	= $multiple;
		return $this;
	}

	public function setName( string $name ): self
	{
		$this->name		= $name;
		return $this;
	}

	public function setRequired( bool $boolean ): self
	{
		$this->required	= $boolean;
		return $this;
	}
}
