<?php
class Logic_ShopBridge_CatalogGallery extends Logic_ShopBridge_Abstract
{
	/**	@var	Logic_Catalog_Gallery				$logic			Gallery logic instance */
	protected Logic_Catalog_Gallery $logic;

	/**	@var	Model_Catalog_Gallery_Category		$modelCategory	Category model instance */
	protected Model_Catalog_Gallery_Category $modelCategory;

	/**	@var	Model_Catalog_Gallery_Image			$modelImage		Gallery model instance */
	protected Model_Catalog_Gallery_Image $modelImage;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected int $taxRate = 7;

	public function getAll( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return [];
	}

	/**
	 *	Change stock quantity of article.
	 *	No need to do anything here, since digital images are sold by right, not be quantity.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$change			Negative value on payed order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function changeQuantity( $articleId, int $change )
	{
	}

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		object						Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function check( $articleId, bool $strict = TRUE )
	{
		$article	= $this->modelImage->get( $articleId );
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
	 *	@return		object
	 */
	public function get( $articleId, int $quantity = 1 ): object
	{
		$image	= $this->check( $articleId );
		return (object) [
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
			'weight'	=> (object) [
				'one'	=> $this->getWeight( $articleId ),
				'all'	=> $this->getWeight( $articleId, $quantity ),
			],
			'single'		=> TRUE,
			'title'			=> $this->getTitle( $articleId ),
			'description'	=> $this->getDescription( $articleId ),
			'bridge'		=> $this->getBridgeClass(),
			'bridgeId'		=> $this->getBridgeId(),
		];
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getDescription( $articleId ): string
	{
		$image		= $this->check( $articleId );
		$category	= $this->modelCategory->get( $image->galleryCategoryId );
		return $category->title;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getLink( $articleId ): string
	{
		return $this->logic->pathModule.'image/'.$articleId;
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
		$image		= $this->check( $articleId );
		$category	= $this->modelCategory->get( $image->galleryCategoryId );
		$uri		= $this->logic->pathImages.'thumbnail/'.$category->path.'/'.$image->filename;
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
		$image	= $this->check( $articleId );
		return $image->price * $amount;
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
		$image	= $this->check( $articleId );
		return $image->price * ( $this->taxRate / 100 ) * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getTitle( $articleId ): string
	{
		$image	= $this->check( $articleId );
		return $image->title ?: $image->filename;
	}

	/**
	 *	Returns weight of article (one or many).
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get weight for
	 *	@return		integer
	 */
	public function getWeight( $articleId, int $amount = 1 )
	{
		return 0;
	}

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->logic			= new Logic_Catalog_Gallery( $this->env );
		$this->modelImage		= new Model_Catalog_Gallery_Image( $this->env );
		$this->modelCategory	= new Model_Catalog_Gallery_Category( $this->env );
		$this->taxRate			= $this->env->getConfig()->get( 'module.catalog_gallery.tax.rate' );
	}
}
