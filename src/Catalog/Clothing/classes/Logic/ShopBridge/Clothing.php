<?php
class Logic_ShopBridge_Clothing extends Logic_ShopBridge_Abstract
{
	/**	@var	Model_Catalog_Clothing_Category		$modelCategory	Category model instance */
	protected Model_Catalog_Clothing_Category $modelCategory;

	/**	@var	Model_Catalog_Clothing_Article		$modelArticle	Article model instance */
	protected Model_Catalog_Clothing_Article $modelArticle;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected int $taxRate = 19;

	protected string $pathImages;

	protected ?Logic_Localization $localization;

	public function getWeight( $articleId, int $amount = 1 )
	{
		// TODO: Implement getWeight() method.
	}

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$change			Negative value on payed order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function changeQuantity( $articleId, int $change )
	{
		$article	= $this->modelArticle->get( $articleId );
//		if( !$article && $strict )
//			throw new RuntimeException( 'Article with ID '.$articleId.' is not existing' );
		if( !$article )
			return FALSE;
		$this->modelArticle->edit( $articleId, [
			'quantity'	=> $article->quantity + $change
		] );
		return $article->quantity + $change;
	}

	/**
	 *	Checks existence of article and returns data object if found.
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
	 *	@param		object		$article
	 *	@param		integer		$quantity
	 *	@return		object
	 */
	public function get( $articleId, int $quantity = 1 ): object
	{
		$article	= $this->check( $articleId );
		$data		= (object) [
			'id'		=> $articleId,
			'link'		=> $this->getLink( $articleId ),
			'picture'	=> (object) [
				'relative'	=> $this->getPicture( $articleId ),
				'absolute'	=> $this->getPicture( $articleId, TRUE ),
			],
			'price'	=> (object) [
				'one'	=> $this->getPrice( $articleId ),
				'all'	=> $this->getPrice( $articleId, $quantity ),
			],
			'tax'	=> (object) [
				'one'	=> $this->getTax( $articleId ),
				'rate'	=> $this->taxRate,
				'all'	=> $this->getTax( $articleId, $quantity ),
			],
			'single'		=> FALSE,
			'title'			=> $this->getTitle( $articleId ),
			'description'	=> $this->getDescription( $articleId ),
			'bridge'		=> $this->getBridgeClass(),
			'bridgeId'		=> $this->getBridgeId(),
			'raw'			=> $this->modelArticle->get( $articleId ),
		];
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
	public function getDescription( $articleId ): string
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
	public function getLink( $articleId ): string
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
	public function getPicture( $articleId, bool $absolute = FALSE ): string
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
	public function getPrice( $articleId, int $amount = 1 ): float
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
	public function getTax( $articleId, int $amount = 1 ): float
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
	public function getTitle( $articleId ): string
	{
		$article	= $this->check( $articleId );
		return $article->title ?: $article->filename;
	}

	/**
	 *	Constructor.
	 *	@access		public
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
