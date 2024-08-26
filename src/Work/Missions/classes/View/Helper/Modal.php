<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Modal
{
	protected WebEnvironment $env;
	protected array $attributes	= [];
	protected ?string $id				= NULL;
	protected bool $fade				= TRUE;
	protected string $heading			= '';
	protected string $body				= '';
	protected string $formAction		= '';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment		$env			Instance of Hydrogen Environment
	 */
	public function __construct( WebEnvironment $env )
	{
		$this->env		= $env;
	}

	/**
	 *	Returns rendered component.
	 *	@access		public
	 *	@return		string
	 */
	public function render(): string
	{
		$body		= HtmlTag::create( 'div', $this->body, [
			'class'	=> 'modal-body',
		] );
		$footer		= $this->renderFooter();
		$header		= $this->renderHeader();
		$attributes	= [
			'id'				=> $this->id,
			'class'				=> 'modal hide'.( $this->fade ? ' fade' : '' ),
			'tabindex'			=> '-1',
			'role'				=> 'dialog',
			'aria-hidden'		=> 'true',
			'aria-labelledby'	=> 'myModalLabel',
		];
		foreach( $this->attributes as $key => $value ){
			switch( strtolower( $key ) ){
				case 'id':
				case 'role':
				case 'tabindex':
				case 'aria-hidden':
					break;
				case 'class':
					$attributes['class']	.= strlen( trim( $value ) ) ? ' '.$value : '';
					break;
				default:
					$attributes[$key]	= $value;
			}
		}
		$modal		= HtmlTag::create( 'div', [$header, $body, $footer], $attributes );
		if( $this->formAction ){
			$modal	= HtmlTag::create( 'form', $modal, [
				'action'	=> $this->formAction,
				'method'	=> 'POST',
			] );
		}
		return $modal;
	}

	/**
	 *	Sets additional modal attributes.
	 *	Set values for id, role, tabindex, aria-hidden will be ignored.
	 *	Set value for class will be added.
	 *	@access		public
	 *	@param		array		$attributes		Map of button attributes
	 *	@return		self
	 */
	public function setAttributes( array $attributes ): self
	{
		$this->attributes	= $attributes;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$body			...
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setBody( string $body ): self
	{
		$this->body		= $body;
		return $this;
	}

	/**
	 *	Toggle the use of a fading animation.
	 *	@access		public
	 *	@param		bool		$fade			Toggle to use fading animation
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setFade( bool $fade ): self
	{
		$this->fade		= $fade;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$action			...
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setFormAction( string $action ): self
	{
		$this->formAction	= $action;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$heading		...
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setHeading( string $heading ): self
	{
		$this->heading		= $heading;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$id				...
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setId( string $id ): self
	{
		$this->id		= $id;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function renderFooter(): string
	{
		$buttonClose	= HtmlTag::create( 'button', 'Schließen', [
			'class'			=> 'btn',
			'data-dismiss'	=> 'modal',
			'aria-hidden'	=> 'true',
		] );
		$buttonSubmit	= HtmlTag::create( 'button', 'Weiter', [
			'class'		=> 'btn btn-primary',
			'type'		=> 'submit',
		] );
		$buttonSubmit	= $this->formAction ? $buttonSubmit : '';
		return HtmlTag::create( 'div', [$buttonClose, $buttonSubmit], [
			'class'	=> 'modal-footer',
		] );
	}

	protected function renderHeader(): string
	{
		$buttonClose	= HtmlTag::create( 'button', '×', [
			'type'			=> "button",
			'class'			=> "close",
			'data-dismiss'	=> "modal",
			'aria-hidden'	=> "true",
		] );
		$heading	= HtmlTag::create( 'h3', $this->heading, ['id' => "myModalLabel"] );
		return HtmlTag::create( 'div', [$buttonClose, $heading], [
			'class'	=> 'modal-header',
		] );
	}
}
