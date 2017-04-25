<?php
class View_Helper_ModalTrigger{

	protected $attributes	= array();
	protected $id;
	protected $label;
	protected $modalId;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		object		$env			Instance of Hydrogen Environment
	 */
	public function __construct( $env ){
		$this->env		= $env;
	}

	/**
	 *	Returns rendered component.
	 *	@access		public
	 *	@return		string
	 */
	public function render(){
		if( !$this->label )
			throw new RuntimeException( 'No label set' );
		if( !$this->modalId )
			throw new RuntimeException( 'No modal ID set' );
		$attributes	= array(
			'id'			=> $this->id,
			'href'			=> "#".$this->modalId,
			'role'			=> "button",
			'class'			=> "btn",
			'data-toggle'	=> "modal",
		);
		foreach( $this->attributes as $key => $value ){
			switch( strtolower( $key ) ){
				case 'id':
				case 'href':
				case 'role':
				case 'data-toggle':
					break;
/*				case 'class':
					$attributes['class']	.= strlen( trim( $value ) ) ? ' '.$value : '';
					break;
*/				default:
					$attributes[$key]	= $value;
			}
		}
		return UI_HTML_Tag::create( 'a', $this->label, $attributes );
	}

	/**
	 *	Sets additional button attributes.
	 *	Set values for id, href, role, data-toggle will be ignored.
	 *	All others will set or override existing values.
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
	 *	@param		string		$id				...
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setId( $id ){
		$this->id		= $id;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$label			...
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setLabel( $label ){
		$this->label	= $label;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$modalId		...
	 *	@return		self
	 *	@todo		code doc
	 */
	public function setModalId( $modalId ){
		$this->modalId	= $modalId;
		return $this;
	}
}
?>
