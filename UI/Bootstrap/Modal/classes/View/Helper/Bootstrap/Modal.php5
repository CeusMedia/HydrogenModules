<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Bootstrap_Modal
{
	protected $env;
	protected $attributes			= [];
	protected $body;
	protected $fade					= TRUE;
	protected $heading;
	protected $id;
	protected $formAction;
	protected $labelButtonCancel	= "Schließen";
	protected $labelButtonSubmit	= "Weiter";
	protected $bsVersion;
	protected $isBs4;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		object		$env			Instance of Hydrogen Environment
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->id			= 'modal-'.uniqid();
		$this->bsVersion	= $env->getModules()->get( 'UI_Bootstrap' )->config['version']->value;
		$this->isBs4		= version_compare( $this->bsVersion, 4, '>=' );
	}

	public function __toString(){
		return $this->render();
	}

	public static function create( Environment $env ): self
	{
		return new static( $env );
	}

	/**
	 *	Returns rendered component.
	 *	@access		public
	 *	@return		string
	 */
	public function render(): string
	{
		$body		= UI_HTML_Tag::create( 'div', $this->body, array(
			'class'	=> 'modal-body',
		) );
		$footer		= $this->renderFooter();
		$header		= $this->renderHeader();
		$attributes	= array(
			'id'				=> $this->id,
			'class'				=> 'modal hide'.( $this->fade ? ' fade' : '' ),
			'tabindex'			=> '-1',
			'role'				=> 'dialog',
			'aria-hidden'		=> 'true',
			'aria-labelledby'	=> 'myModalLabel',
		);
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
		$content	= array( $header, $body, $footer );
		if( $this->isBs4 ){
			$content	= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $content, array( 'class' => 'modal-content' ) ),
			), array(
				'class'	=> 'modal-dialog',
				'role'	=> 'document',
			) );
		}

		$modal		= UI_HTML_Tag::create( 'div', $content, $attributes );
		if( $this->formAction ){
			$modal	= UI_HTML_Tag::create( 'form', $modal, array(
				'action'	=> $this->formAction,
				'method'	=> 'POST',
			) );
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
	 *	Set label of cancel button in modal footer.
	 *	@access		public
	 *	@param		string		$label			Label of cancel button in modal footer
	 *	@return		self
	 */
	public function setButtonLabelCancel( string $label ): self
	{
		$this->labelButtonCancel	= $label;
		return $this;
	}

	/**
	 *	Set label of submit button in modal footer.
	 *	@access		public
	 *	@param		string		$label			Label of submit button in modal footer
	 *	@return		self
	 */
	public function setButtonLabelSubmit( string $label ): self
	{
		$this->labelButtonSubmit	= $label;
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
	 *	...
	 *	@access		public
	 *	@param		string		$fade			...
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setFade( string $fade ): self
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

	protected function renderFooter(): string
	{
		$buttonClose	= UI_HTML_Tag::create( 'button', $this->labelButtonCancel, array(
			'class'			=> 'btn',
			'data-dismiss'	=> 'modal',
			'aria-hidden'	=> 'true',
		) );
		$buttonSubmit	= UI_HTML_Tag::create( 'button', $this->labelButtonSubmit, array(
			'class'		=> 'btn btn-primary',
			'type'		=> 'submit',
		) );
		$buttonSubmit	= $this->formAction ? $buttonSubmit : '';
		$footer		= UI_HTML_Tag::create( 'div', array( $buttonClose, $buttonSubmit ), array(
			'class'	=> 'modal-footer',
		) );
		return $footer;
	}

	protected function renderHeader(): string
	{
		$buttonClose	= UI_HTML_Tag::create( 'button', '×', array(
			'type'			=> "button",
			'class'			=> "close",
			'data-dismiss'	=> "modal",
			'aria-hidden'	=> "true",
		) );
		$heading	= UI_HTML_Tag::create( 'h3', $this->heading, array( 'id' => "myModalLabel" ) );
		$header		= UI_HTML_Tag::create( 'div', array( $buttonClose, $heading ), array(
			'class'	=> 'modal-header',
		) );
		return $header;
	}
}
