<?php
class Logic_ShopBridge_Clothing extends Logic_ShopBridge_Abstract
{
	/**	@var	Model_Catalog_Clothing_Category		$modelCategory	Category model instance */
	protected $modelCategory;

	/**	@var	Model_Catalog_Clothing_Article		$modelArticle	Article model instance */
	protected $modelArticle;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected $taxRate = 19;

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$change			Negative value on payed order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function changeQuantity( $articleId, $change )
	{
		$change		= (int) $change;
		$article	= $this->modelArticle->get( $articleId );
		if( !$article && $strict )
			throw new RuntimeException( 'Article with ID '.$articleId.' is not existing' );
		if( !$article )
			return FALSE;
		$this->modelArticle->edit( $articleId, array(
			'quantity'	=> $article->quantity + $change
		) );
		return $article->quantity + $change;
	}

	/**
	 *	Checks existance of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		object						Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function check( $articleId, $strict = TRUE )
	{
		$article	= $this->modelArticle->get( $articleId );
		if( $article )
			return $article;
		if( $strict )
			throw new RuntimeException( 'Article with ID '.$articleId.' is not existing' );
		return FALSE;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function get( $articleId, $quantity = 1 )
	{
		$article	= $this->check( $articleId );
		$data		= (object) array(
			'id'		=> $articleId,
			'link'		=> $this->getLink( $articleId ),
			'picture'	=> (object) array(
				'relative'	=> $this->getPicture( $articleId ),
				'absolute'	=> $this->getPicture( $articleId, TRUE ),
			),
			'price'	=> (object) array(
				'one'	=> $this->getPrice( $articleId ),
				'all'	=> $this->getPrice( $articleId, $quantity ),
			),
			'tax'	=> (object) array(
				'one'	=> $this->getTax( $articleId ),
				'rate'	=> $this->taxRate,
				'all'	=> $this->getTax( $articleId, $quantity ),
			),
			'single'		=> FALSE,
			'title'			=> $this->getTitle( $articleId ),
			'description'	=> $this->getDescription( $articleId ),
			'bridge'		=> $this->getBridgeClass(),
			'bridgeId'		=> $this->getBridgeId(),
			'raw'			=> $this->modelArticle->get( $articleId ),
		);
		if( $this->localization ){
			$id	= 'catalog.clothing.article.'.$articleId.'-title';
			$data->title	= $this->localization->translate( $id, $data->title );
			$id	= 'catalog.clothing.article.'.$articleId.'-description';
			$data->description	= $this->localization->translate( $id, $data->description );
		}
		return $data;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getDescription( $articleId )
	{
		$article	= $this->check( $articleId );
		return $article->description;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getLink( $articleId )
	{
		$article	= $this->check( $articleId );
		return $this->pathImages.'products/'.$article->image;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		boolean		$absolute
	 *	@return		string
	 *	@todo		implement absolute mode
	 */
	public function getPicture( $articleId, $absolute = FALSE )
	{
		$article		= $this->check( $articleId );
		$category	= $this->modelCategory->get( $article->categoryId );
		$uri		= $this->pathImages.'products/'.$article->image;
		return $absolute ? $this->env->url.ltrim( $uri, './' ) : $uri;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getPrice( $articleId, $amount = 1 )
	{
		$article	= $this->check( $articleId );
		return $article->price * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getTax( $articleId, $amount = 1 )
	{
		$article	= $this->check( $articleId );
		return $article->price * ( $this->taxRate / 100 ) * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getTitle( $articleId )
	{
		$article	= $this->check( $articleId );
		return $article->title ? $article->title : $article->filename;
	}

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env
	 *	@return		void
	 */
	protected function __onInit(): void
	{
//		$this->logic			= new Logic_Catalog_Gallery( $this->env );
		$this->modelArticle		= new Model_Catalog_Clothing_Article( $this->env );
		$this->modelCategory	= new Model_Catalog_Clothing_Category( $this->env );
		$this->taxRate			= $this->env->getConfig()->get( 'module.catalog_clothing.tax.rate' );
		$this->pathImages		= $this->env->getConfig()->get( 'path.images' );
		if( class_exists( 'Logic_Frontend' ) ){
			$frontend			= Logic_Frontend::getInstance( $this->env );
			$this->pathImages	= $frontend->getPath( 'images' );
		}
		if( class_exists( 'Logic_Localization' ) ){
			$this->localization	= new Logic_Localization( $this->env );
		}
	}
}
