<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Input_Resource
{
	protected $env;
	protected $inputId;
	protected $modalId;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	public function __toString()
	{
		return $this->render();
	}

	public function render(): string
	{
//		$modal			= new View_Helper_Bootstrap_Modal( $this->env );
		$modal			= new BootstrapModalDialog( $this->env );
		$modal->setHeading( 'Auswahl' );
		$modal->setBody( '<div id="'.$this->modalId.'-content"></div><div id="'.$this->modalId.'-loader"><div class="alert alert-info">... Loading ...</div></div>' );
		$modal->setId( $this->modalId );
		$modal->setFade( FALSE );
		return $modal->render();
	}

	public function setInputId( string $id ): self
	{
		$this->inputId	= $id;
		return $this;
	}

	public function setModalId( string $id ): self
	{
		$this->modalId	= $id;
		return $this;
	}
}


class View_Helper_Input_ResourceTrigger
{
	const MODE_IMAGE			= 'image';
	const MODE_STYLE			= 'style';
	const MODE_DOCUMENT			= 'document';

	protected $env;
	protected $modalId;
	protected $mode				= 'image';
	protected $inputId;
	protected $label			= 'select';
	protected $class			= 'btn';
	protected $paths			= [];

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	public function __toString()
	{
		return $this->render();
	}

	public function render(): string
	{
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

	public function setClass( string $class ): self
	{
		$this->class	= $class;
		return $this;
	}

	public function setInputId( string $inputId ): self
	{
		$this->inputId	= $inputId;
		return $this;
	}

	public function setLabel( string $label ): self
	{
		$this->label	= $label;
		return $this;
	}

	public function setModalId( string $modalId ): self
	{
		$this->modalId	= $modalId;
		return $this;
	}

	public function setMode( string $mode ): self
	{
		$this->mode	= $mode;
		return $this;
	}

	public function setPaths( string $paths ): self
	{
		$this->paths	= $paths;
		return $this;
	}
}
