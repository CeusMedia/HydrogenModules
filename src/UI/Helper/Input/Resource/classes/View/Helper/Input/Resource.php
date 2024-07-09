<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Input_Resource
{
	protected Environment $env;
	protected ?string $inputId		= NULL;
	protected ?string $modalId		= NULL;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	/**
	 *	@return		string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
//		$modal			= new View_Helper_Bootstrap_Modal( $this->env );
		$modal			= new BootstrapModalDialog( $this->modalId );
		$modal->setHeading( 'Auswahl' );
		$modal->setBody( '<div id="'.$this->modalId.'-content"></div><div id="'.$this->modalId.'-loader"><div class="alert alert-info">... Loading ...</div></div>' );
		$modal->setFade( FALSE );
		return $modal->render();
	}

	/**
	 *	@param		string		$id
	 *	@return		self
	 */
	public function setInputId( string $id ): self
	{
		$this->inputId	= $id;
		return $this;
	}

	/**
	 *	@param		string		$id
	 *	@return		self
	 */
	public function setModalId( string $id ): self
	{
		$this->modalId	= $id;
		return $this;
	}
}
