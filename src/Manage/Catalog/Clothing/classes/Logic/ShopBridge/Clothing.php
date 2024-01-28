<?php
class Logic_ShopBridge_Clothing extends Logic_ShopBridge_Abstract
{
//	/**	@var	Logic_Catalog_GalleryManager				$logic			Gallery logic instance */
//	protected Logic_Catalog_GalleryManager $logic;

	/**	@var	Model_Catalog_Clothing_Article		$modelArticle	Article model instance */
	protected Model_Catalog_Clothing_Article $modelArticle;

	/**	@var	Model_Catalog_Clothing_Category		$modelCategory	Gallery logic instance */
	protected Model_Catalog_Clothing_Category $modelCategory;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected int $taxRate = 19;

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$change			Negative value on payed order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function changeQuantity( string $articleId, int $change ): int
	{
		$article	= $this->check( $articleId );
		$this->modelArticle->edit( $articleId, [
			'quantity'	=> $article->quantity + $change
		] );
		return $article->quantity + $change;
	}

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		object|FALSE				Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function check( $articleId, bool $strict = TRUE )
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
	 *	@param		string		$articleId
	 *	@param		integer		$quantity
	 *	@return		object
	 */
	public function get( string $articleId, int $quantity = 1 ): object
	{
		return (object) array(
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
			'title'			=> $this->getTitle( $articleId ),
			'description'	=> $this->getDescription( $articleId ),
			'bridge'		=> $this->getBridgeClass(),
			'bridgeId'		=> $this->getBridgeId(),
		);
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@return		string
	 */
	public function getDescription( string $articleId ): string
	{
		$article	= $this->check( $articleId );
		return $article->title;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@return		string
	 */
	public function getLink( string $articleId ): string
	{
//		return $this->logic->pathModule.'image/'.$articleId;
		return $articleId;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@param		boolean		$absolute
	 *	@return		string
	 *	@todo		implement absolute mode
	 */
	public function getPicture( string $articleId, bool $absolute = FALSE ): string
	{
		return '';
//		$image		= $this->check( $articleId );
//		$category	= $this->modelCategory->get( $image->galleryCategoryId );
//		$uri		= $this->logic->pathImages.'thumbnail/'.$category->path.'/'.$image->filename;
//		return $absolute ? $this->env->url.ltrim( $uri, './' ) : $uri;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getPrice( string $articleId, int $amount = 1 ): float
	{
		$image	= $this->check( $articleId );
		return $image->price * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getTax( string $articleId, int $amount = 1 ): float
	{
		$article	= $this->check( $articleId );
		return $article->price * ( $this->taxRate / 100 ) * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@return		string
	 */
	public function getTitle( string $articleId ): string
	{
		$article	= $this->check( $articleId );
		return $article->title ?: $article->filename;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getWeight( string $articleId, int $amount = 1 ): float
	{
		$article	= $this->check( $articleId );
		return (float) $article->weight;
	}

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
//		$this->logic			= new Logic_Catalog_GalleryManager( $this->env );
		$this->modelArticle		= new Model_Catalog_Clothing_Article( $this->env );
		$this->modelCategory	= new Model_Catalog_Clothing_Category( $this->env );
//		$this->taxRate			= $this->env->getConfig()->get( 'module.catalog_clothing.tax.rate' );
	}
}
