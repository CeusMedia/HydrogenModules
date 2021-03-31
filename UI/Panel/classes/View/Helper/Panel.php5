<?php
class View_Helper_Panel
{
	static protected $defaultAttributes		= array();
	static protected $defaultClass			= 'panel';
	static protected $defaultClassBody		= 'panel-body';
	static protected $defaultClassFoot		= 'panel-foot';
	static protected $defaultClassHead		= 'panel-head';
	static protected $defaultTheme			= 'default';

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
	protected $body;
	protected $foot;
	protected $head;

	public static function create( CMF_Hydrogen_Environment $env, string $head, string $body, string $foot, array $attributes = array(), array $classes = array(), string $theme = NULL, string $id = NULL )
	{
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
	 *	@static
	 *	@return		string		Rendered output of this view helper component
	 */
	public static function renderStatic( CMF_Hydrogen_Environment $env, string $head, string $body, string $foot, array $attributes = array(), array $classes = array(), string $theme = NULL, string $id = NULL ): string
	{
		$instance	= static::create( $env, $head, $body, $foot, $attributes, $classes, $theme, $id );
		return $instance->render();
	}

	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public static function setDefaultTheme( $theme = 'default' ){
		static::$theme	= $theme;
	}

	public function __construct( CMF_Hydrogen_Environment $env )
	{
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
	public function __toString()
	{
		return $this->render();
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		string		Rendered output of this view helper component
	 */
	public function render(): string
	{
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


	public function setAttributes( array $attributes = array() ): self
	{
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
	public function setBody( string $body ): self
	{
		$this->body	= $this->checkRenderable( $body );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setClass( string $class ): self
	{
		$this->class	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setClassBody( string $class ): self
	{
		$this->classBody	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setClassFoot( string $class ): self
	{
		$this->classFoot	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setClassHead( string $class ): self
	{
		$this->classHead	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setFoot( string $foot ): self
	{
		$this->foot	= $this->checkRenderable( $foot );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setHead( string $head ): self
	{
		$this->head	= $this->checkRenderable( $head );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		...			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setId( string $id ): self
	{
		$this->attributes['id']	= $id;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$theme			...
	 *	@return		object		Helper instance for chainability
	 */
	public function setTheme( string $theme ): self
	{
		$this->theme	= $theme;
		return $this;
	}

	protected function checkRenderable( $mixed )
	{
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
}
