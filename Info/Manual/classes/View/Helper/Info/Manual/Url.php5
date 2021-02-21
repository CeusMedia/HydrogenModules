<?php
class View_Helper_Info_Manual_Url
{
	const MODE_UNKNONW		= 0;
	const MODE_CATEGORY		= 1;
	const MODE_PAGE			= 2;

	protected $env;
	protected $mode			= self::MODE_UNKNONW;
	protected $category;
	protected $page;
	protected $baseUri		= './info/manual/';
	protected $modelCategory;
	protected $modelPage;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env				= $env;
		$this->modelPage		= new Model_Manual_Page( $env );
		$this->modelCategory	= new Model_Manual_Category( $env );
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function render(): string
	{
		if( !$this->category && !$this->page )
			throw new RuntimeException( 'Neither category nor page set' );
		if( $this->mode === static::MODE_CATEGORY ){
			$title		= urlencode( $this->category->title );
			return $this->baseUri.'category/'.$this->category->manualCategoryId.'-'.$title;
		}
		if( $this->mode === static::MODE_PAGE ){
			$title		= urlencode( $this->page->title );
			return $this->baseUri.'page/'.$this->page->manualPageId.'-'.$title;
		}
		return '';
	}

	public function setCategory( $categoryOrId ): self
	{
		if( is_object( $categoryOrId ) )
			$category	= $categoryOrId;
		else if( is_int( $categoryOrId ) ){
			$category	= $this->modelCategory->get( $categoryOrId );
			if( !$category )
				throw new RangeException( 'Invalid category ID given' );
		}
		else
			throw new InvalidArgumentException( 'Category must me model object or ID as integer' );
		$this->category	= $category;
		$this->setMode( static::MODE_CATEGORY );
		return $this;
	}

	public function setMode( $mode ): self
	{
		if( !in_array( $mode, array( self::MODE_CATEGORY, self::MODE_PAGE ) ) )
			throw new RangeException( 'Invalid mode given' );
		$this->mode	= $mode;
		return $this;
	}

	public function setPage( $pageOrId ): self
	{
		if( is_object( $pageOrId ) )
			$page	= $pageOrId;
		else if( is_int( $pageOrId ) ){
			$page	= $this->modelPage->get( $pageOrId );
			if( !$page )
				throw new RangeException( 'Invalid page ID given' );
		}
		else
			throw new InvalidArgumentException( 'Page must me model object or ID as integer' );
		$this->page	= $page;
		$this->setMode( static::MODE_PAGE );
		return $this;
	}

	public static function spawn( $env ): self
	{
		return new static( $env );
	}
}
