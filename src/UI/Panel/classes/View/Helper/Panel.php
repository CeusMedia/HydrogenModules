<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/**
 * @phpstan-consistent-constructor
 */
class View_Helper_Panel
{
	protected static array $defaultAttributes		= [];
	protected static string $defaultClass			= 'panel';
	protected static string $defaultClassBody		= 'panel-body';
	protected static string $defaultClassFoot		= 'panel-foot';
	protected static string $defaultClassHead		= 'panel-head';
	protected static string $defaultTheme			= 'default';

	protected array $attributes					= [];
	protected Environment $env;
	protected ?string $class					= NULL;
	protected ?string $classBody				= NULL;
	protected ?string $classFoot				= NULL;
	protected ?string $classHead				= NULL;
	protected ?string $theme					= NULL;
	protected ?string $body;
	protected ?string $foot;
	protected ?string $head;

	public static function create( Environment $env, string $head, string $body, string $foot, array $attributes = [], array $classes = [], string $theme = NULL, string $id = NULL ): static
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
		}
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
	public static function renderStatic( Environment $env, string $head, string $body, string $foot, array $attributes = [], array $classes = [], string $theme = NULL, string $id = NULL ): string
	{
		$instance	= static::create( $env, $head, $body, $foot, $attributes, $classes, $theme, $id );
		return $instance->render();
	}

	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		string		$theme			...
	 *	@return		void
	 */
	public static function setDefaultTheme( string $theme = 'default' ): void
	{
		static::$defaultTheme	= $theme;
	}

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->setAttributes( static::$defaultAttributes );
		$this->setClass( static::$defaultClass );
		$this->setClassBody( static::$defaultClassBody );
		$this->setClassFoot( static::$defaultClassFoot );
		$this->setClassHead( static::$defaultClassHead );
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
		$attributes['class']	= $attributes['class'] ?? '';
		$attributes['class']	= trim( $this->class.' '.$attributes['class'] );
		$attributes['class']	= $attributes['class'].' panel-theme-'.$this->theme;
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'div', $this->head, ['class' => $this->classHead] ),
			HtmlTag::create( 'div', $this->body, ['class' => $this->classBody] ),
			HtmlTag::create( 'div', $this->foot, ['class' => $this->classFoot] ),
		], $attributes );
	}


	public function setAttributes( array $attributes = [] ): self
	{
		if( !is_array( $attributes ) )
			throw new InvalidArgumentException( 'Attributes must be of array' );
		$this->attributes	= array_merge( $this->attributes, $attributes );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$body			...
	 *	@return		self		Helper instance for method chaining
	 */
	public function setBody( string $body ): self
	{
		$this->body	= $this->checkRenderable( $body );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$class			...
	 *	@return		self		Helper instance for method chaining
	 */
	public function setClass( string $class ): self
	{
		$this->class	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$class			...
	 *	@return		self		Helper instance for method chaining
	 */
	public function setClassBody( string $class ): self
	{
		$this->classBody	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$class			...
	 *	@return		self		Helper instance for method chaining
	 */
	public function setClassFoot( string $class ): self
	{
		$this->classFoot	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$class			...
	 *	@return		self		Helper instance for method chaining
	 */
	public function setClassHead( string $class ): self
	{
		$this->classHead	= $class;
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$foot			...
	 *	@return		self		Helper instance for method chaining
	 */
	public function setFoot( string $foot ): self
	{
		$this->foot	= $this->checkRenderable( $foot );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$head			...
	 *	@return		self		Helper instance for method chaining
	 */
	public function setHead( string $head ): self
	{
		$this->head	= $this->checkRenderable( $head );
		return $this;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$id			...
	 *	@return		self		Helper instance for method chaining
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
	 *	@return		self		Helper instance for method chaining
	 */
	public function setTheme( string $theme ): self
	{
		$this->theme	= $theme;
		return $this;
	}

	protected function checkRenderable( $mixed )
	{
		if( is_null( $mixed ) || is_string( $mixed ) || is_numeric( $mixed ) )
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
