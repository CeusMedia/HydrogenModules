<?php
/**
 *	Helper to convert strings to be valid for URLs.
 *	Can be used to convert database item titles for SEO optimized links.
 *	Title string can be set directly, by data object or fetched from model by item ID.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

/**
 *	Helper to convert strings to be valid for URLs.
 *	Can be used to convert database item titles for SEO optimized links.
 *	Title string can be set directly, by data object or fetched from model by item ID.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		finish code doc
 */
class View_Helper_SEO_LinkTitle extends Abstraction
{
	const MODE_TITLE		= 1;
	const MODE_OBJECT		= 2;
	const MODE_MODEL		= 3;

	protected $column		= 'title';

	protected $id			= 0;

	protected $lowerCase	= FALSE;

	protected $mode			= self::MODE_TITLE;

	protected $object;

	protected $title;

	protected $model;

	/**
	 *	Constructor.
	 *	Sets case sensitivity by module configuration.
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 */
	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
		$module		= $this->env->getModules()->get( 'UI_Helper_SEO' );
		$this->setLowerCase( $module->config['lowerCase']->value );
	}

	/**
	 *	Tries to render current title and returns optimized link title.
	 *	Suppresses possible exceptions.
	 *	@access		public
	 *	@return		string			Optimized link title
	 */
	public function __toString()
	{
		try{
			return $this->render();
		}
		catch( Exception $e ){
			$payload	= array( 'exception' => $e );
			$this->env->getCaptain()->callHook( 'App', 'onException', $this, $payload );
			return '';
		}
	}

	/**
	 *	Renders current title and returns optimized link title.
	 *	@access		public
	 *	@return		string				Optimized link title
	 *	@throws		RuntimeException	if mode is MODE_MODEL and no model is set
	 *	@throws		RuntimeException	if mode is MODE_MODEL and no ID is set
	 *	@throws		RuntimeException	if mode is MODE_MODEL and no object is existing for given ID
	 *	@throws		RuntimeException	if mode is MODE_OBJECT and no object is set
	 */
	public function render(): string
	{
		switch( $this->mode ){
			case self::MODE_MODEL:
				if( !$this->model )
					throw new RuntimeException( 'No model set' );
				if( !$this->id )
					throw new RuntimeException( 'No ID set' );
				$item	= $this->model->get( $this->id );
				if( !$item )
					throw new RuntimeException( 'No item for given ID' );
				$title	= $item->$this->column;
				break;
			case self::MODE_OBJECT:
				if( !$this->object )
					throw new RuntimeException( 'No object set' );
				$title	= $this->object->{$this->column};
				break;
			case self::MODE_TITLE:
			default:
				$title	= $this->title;
				break;
		}
		return $this->convertForUrl( $title );
	}

	/**
	 *	Set object ID for getting object from model on render.
	 *	Sets helper mode to MODE_MODEL.
	 *	@access		public
	 *	@param		integer		$id		Object ID to get object on render
	 *	@return		self
	 */
	public function setId( string $id ): self
	{
		$this->id		= $id;
		$this->setMode( self::MODE_MODEL );
		return $this;
	}

	/**
	 *	Set object to get title from on render.
	 *	Sets helper mode to MODE_OBJECT.
	 *	@access		public
	 *	@param		object		$object		Object to get title from on render
	 *	@return		self
	 */
	public function setObject( $object ): self
	{
		$this->object	= $object;
		$this->setMode( self::MODE_OBJECT );
		return $this;
	}

	/**
	 *	Set mode to apply on render.
	 *	@access		public
	 *	@param		integer		$mode		One of self::MODE_*
	 *	@return		self
	 */
	public function setMode( int $mode ): self
	{
		$this->mode		= $mode;
		return $this;
	}

	/**
	 *	Set model instance for getting object by given ID from model on render.
	 *	Need an ID to be set before calling to render.
	 *	Sets helper mode to MODE_MODEL.
	 *	@access		public
	 *	@param		object		$model		Model instance for getting object b given ID
	 *	@return		self
	 */
	public function setModel( string $model ): self
	{
		$this->model	= $model;
		$this->setMode( self::MODE_MODEL );
		return $this;
	}

	/**
	 *	Set name of title column on model object.
	 *	@access		public
	 *	@param		string		$column		Name of title column on model object
	 *	@return		self
	 */
	public function setModelTitleColumn( string $column ): self
	{
		$this->column	= $column;
		return $this;
	}

	/**
	 *	Set title to optimize on render.
	 *	Sets helper mode to MODE_TITLE.
	 *	This is the most direct and performant form of providing a title to optimize.
	 *	All other modes are there for comfort and usability.
	 *	@access		public
	 *	@param		string		$title		Title to optimize on render
	 *	@return		self
	 */
	public function setTitle( string $title ): self
	{
		$this->title	= $title;
		$this->setMode( self::MODE_TITLE );
		return $this;
	}

	/**
	 *	Enable or disable convertion to lower case on render.
	 *	@access		public
	 *	@param		boolean		$lowerCase	Flag: convert to lower case on render
	 *	@return		self
	 */
	public function setLowerCase( bool $lowerCase = TRUE ): self
	{
		$this->lowerCase	= $lowerCase;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Converts title string to be valid for URLs.
	 *	@param		string		$string		String to convert to be valid for URLs
	 *	@return		string
	 *	@see		https://blog.ueffing.net/post/2016/03/14/string-seo-optimieren-creating-seo-friendly-url/
	 */
	protected function convertForUrl( $string = '' )
	{
		$string	= str_replace(
			array( 'Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß'),
			array( 'Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss'),
			$string
		);
		$string	= preg_replace( '/[^\\pL\d_]+/u', '-', $string );
		$string	= trim( $string, '-' );
		$string	= iconv( 'utf-8', "ascii//TRANSLIT", $string );
		$string	= $this->lowerCase ? strtolower( $string ) : $string;
		$string	= preg_replace( '/[^-a-z0-9_]+/i', '', $string );
		return $string;
	}
}
