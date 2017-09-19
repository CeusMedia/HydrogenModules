<?php
class View_Helper_Bootstrap_Modal{

	protected $attributes	= array();
	protected $id;
	protected $fade				= TRUE;
	protected $heading;
	protected $body;
	protected $formAction;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		object		$env			Instance of Hydrogen Environment
	 */
	public function __construct( $env ){
		$this->env		= $env;
	}

	public function __toString(){
		return $this->render();
	}

	static public function create( $env ){
		return new static( $env );
	}

	/**
	 *	Returns rendered component.
	 *	@access		public
	 *	@return		string
	 */
	public function render(){
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
		$modal		= UI_HTML_Tag::create( 'div', array( $header, $body, $footer ), $attributes );
		if( $this->formAction ){
			$modal	= UI_HTML_Tag::create( 'form', $modal, array(
				'action'	=> $this->formAction,
				'method'	=> 'POST',
			) );
		}
		return $modal;
	}

	protected function renderFooter(){
		$buttonClose	= UI_HTML_Tag::create( 'button', 'Schließen', array(
			'class'			=> 'btn',
			'data-dismiss'	=> 'modal',
			'aria-hidden'	=> 'true',
		) );
		$buttonSubmit	= UI_HTML_Tag::create( 'button', 'Weiter', array(
			'class'		=> 'btn btn-primary',
			'type'		=> 'submit',
		) );
		$buttonSubmit	= $this->formAction ? $buttonSubmit : '';
		$footer		= UI_HTML_Tag::create( 'div', array( $buttonClose, $buttonSubmit ), array(
			'class'	=> 'modal-footer',
		) );
		return $footer;
	}

	protected function renderHeader(){
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

	/**
	 *	Sets additional modal attributes.
	 *	Set values for id, role, tabindex, aria-hidden will be ignored.
	 *	Set value for class will be added.
	 *	@access		public
	 *	@param		array		$attributes		Map of button attributes
	 *	@return		self
	 */
	public function setAttributes( $attributes ){
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
	public function setBody( $body ){
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
	public function setFade( $fade ){
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
	public function setFormAction( $action ){
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
	public function setHeading( $heading ){
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
	public function setId( $id ){
		$this->id		= $id;
		return $this;
	}
}
