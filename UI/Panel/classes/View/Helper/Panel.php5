<?php
class View_Helper_Panel{

	protected $attributes					= array();
	protected $env;
	protected $class;
	protected $classBody;
	protected $classFoot;
	protected $classHead;
	protected $contentBody;
	protected $contentFoot;
	protected $contentHead;
	protected $theme;

	static protected $defaultAttributes		= array();
	static protected $defaultClass			= 'panel';
	static protected $defaultClassBody		= 'panel-body';
	static protected $defaultClassFoot		= 'panel-foot';
	static protected $defaultClassHead		= 'panel-head';
	static protected $defaultTheme			= 'default';

	public function __construct( $env ){
		$this->env			= $env;
		$this->setAttributes( static::$attributes );
		$this->setClass( static::$defaultClass );
		$this->setClassBody( static::$defaultClassBody );
		$this->setClassFoot( static::$defaultClassFoot );
		$this->setCassHead( static::$defaultClassHead );
		$this->setTheme( static::$defaultTheme );
	}

	/**
	 *	Magic method to present as string.
	 *	@access		public
	 *	@return		string		Rendered output of this view helper component
	 */
	public function __toString(){
		return $this->render();
	}

	protected function checkRenderable( $mixed ){
		if( is_null( $mixed ) || is_string( $mixed ) || is_number( $mixed ) )
			return $mixed;
		if( is_object( $mixed ) ){
			$isRenderable		= is_a( 'Renderable', $mixed );
			$hasRenderMethod	= method_exists( $mixed, 'render' );
			$hasStringCase		= method_exists( $mixed, '__toString' );
			if( $isRenderable || $hasRenderMethod || $hasStringCase )
				return $mixed;
			throw new InvalidArgumentException( 'Given object is not renderable' );
		}
		throw new RangeException( 'Given argument is not renderable' );
	}

	static public function create( $env, $head, $body, $foot, $attributes = array(), $classes = array(), $theme = NULL, $id = NULL ){
		$instance	= new static( $env );
		$instance->setHead( $head )->setBody( $body )->setFoot( $foot );
		$instance->setAttributes( $attributes );
		foreach( $classes as $key => $value ){
			if( $key === 'head' )
				$instance->setClassHead( $value );
			if( $key === 'body' )
				$instance->setClassBody( $value );
			if( $key === 'foot' )
				$instance->setClassFoot( $value );
		};
		if( $theme !== NULL )
			$instance->setTheme( $theme );
		if( $id )
			$instance->setId( $id );
		return $instance;
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		string		Rendered output of this view helper component
	 */
	public function render(){
		$attributes		= $this->attributes;
		$attributes['class']	= isset( $attributes['class'] ) ? $attributes['class'] : '';
		$attributes['class']	= trim( $this->class.' '.$attributes['class'] );
		$attributes['class']	= $attributes['class'].' panel-theme-'.$this->theme;
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', $this->head, array( 'class' => $this->classHead ) ),
			UI_HTML_Tag::create( 'div', $this->body, array( 'class' => $this->classBody ) ),
			UI_HTML_Tag::create( 'div', $this->foot, array( 'class' => $this->classFoot ) ),
		), $attributes );
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@return		string		Rendered output of this view helper component
	 */
	static public function renderStatic( $env, $head, $body, $foot, $attributes = array(), $classes = array(),  $theme = NULL, $id = NULL ){
		$instance	= static::create( $env, $head, $body, $foot, $attributes, $classes, $theme, $id );
		return $instance->render();
	}

	public function setAttributes( $attributes = array() ){
		if( !is_array( $attributes ) )
			throw new InvalidArgumentException( 'Attributes must be of array' );
		$this->attributes	= array_merge( $this->attributes, $attributes );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setBody( $body ){
		$this->body	= $this->checkRenderable( $body );
		return $this;
	}


	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setClass( $class ){
		$this->class	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setClassBody( $class ){
		$this->classBody	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setClassFoot( $class ){
		$this->classFoot	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setClassHead( $class ){
		$this->classHead	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	static public function setDefaultTheme( $theme = 'default' ){
		static::$theme	= $theme;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setFoot( $foot ){
		$this->foot	= $this->checkRenderable( $foot );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setHead( $head ){
		$this->head	= $this->checkRenderable( $head );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setId( $id ){
		$this->attributes['id']	= $id;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$theme			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setTheme( $theme ){
		$this->theme	= $theme;
		return $this;
	}
}
