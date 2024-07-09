<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Input_ResourceTrigger
{
	public const MODE_IMAGE			= 'image';
	public const MODE_STYLE			= 'style';
	public const MODE_DOCUMENT		= 'document';

	protected Environment $env;
	protected ?string $modalId		= NULL;
	protected string $mode			= 'image';
	protected ?string $inputId		= NULL;
	protected string $label			= 'select';
	protected string $class			= 'btn';
	protected array $paths			= [];

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
		return HtmlTag::create( 'button', $this->label, [
			'type'		=> "button",
			'onclick'	=> "HelperInputResource.open(this)",
			'class'		=> $this->class,
		], [
			'modal-id'	=> $this->modalId,
			'input-id'	=> $this->inputId,
			'mode'		=> $this->mode,
			'paths'		=> join( ',', $this->paths ),
		] );
	}

	/**
	 *	@param		string		$class
	 *	@return		self
	 */
	public function setClass( string $class ): self
	{
		$this->class	= $class;
		return $this;
	}

	/**
	 *	@param		string		$inputId
	 *	@return		self
	 */
	public function setInputId( string $inputId ): self
	{
		$this->inputId	= $inputId;
		return $this;
	}

	/**
	 *	@param		string		$label
	 *	@return		self
	 */
	public function setLabel( string $label ): self
	{
		$this->label	= $label;
		return $this;
	}

	/**
	 *	@param		string		$modalId
	 *	@return		self
	 */
	public function setModalId( string $modalId ): self
	{
		$this->modalId	= $modalId;
		return $this;
	}

	/**
	 *	@param		string		$mode
	 *	@return		self
	 */
	public function setMode( string $mode ): self
	{
		$this->mode	= $mode;
		return $this;
	}

	/**
	 *	@param		array		$paths
	 *	@return		self
	 */
	public function setPaths( array $paths ): self
	{
		$this->paths	= $paths;
		return $this;
	}
}
