<?php

use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

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
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$change			Negative value on paid order, positive value on restock.
	 *	@return		integer							Article quantity in stock after change
	 *	@throws		InvalidArgumentException		if not found
	 */
	public function changeQuantity( int|string $articleId, int $change ): int
	{
		return 1;
	}

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@return		object|FALSE					Bridged article data object if found
	 *	@throws		InvalidArgumentException		if not found
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function check( int|string $articleId, bool $strict = TRUE ): object|FALSE
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
	 *	@param		int|string		$articleId
	 *	@param		integer			$quantity
	 *	@return		object
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function get( int|string $articleId, int $quantity = 1 ): object
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
	 *	@param		int|string		$articleId
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getDescription( int|string $articleId ): string
	{
		$image		= $this->check( $articleId );
		$category	= $this->modelCategory->get( $image->galleryCategoryId );
		return $category->title;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@return		string
	 */
	public function getLink( int|string $articleId ): string
	{
		return $this->logic->pathModule.'image/'.$articleId;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@param		boolean			$absolute
	 *	@return		string
	 *	@todo		implement absolute mode
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getPicture( int|string $articleId, bool $absolute = FALSE ): string
	{
		$image		= $this->check( $articleId );
		$category	= $this->modelCategory->get( $image->galleryCategoryId );
		$uri		= $this->logic->pathImages.'thumbnail/'.$category->path.'/'.$image->filename;
		return $absolute ? $this->env->url.ltrim( $uri, './' ) : $uri;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@param		integer			$amount
	 *	@return		float
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getPrice( int|string $articleId, int $amount = 1 ): float
	{
		$image	= $this->check( $articleId );
		return (float) $image->price * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@param		integer			$amount
	 *	@return		float
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getTax( int|string $articleId, int $amount = 1 ): float
	{
		$image	= $this->check( $articleId );
		return $image->price * ( $this->taxRate / 100 ) * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getTitle( int|string $articleId ): string
	{
		$image	= $this->check( $articleId );
		return $image->title ?: $image->filename;
	}

	/**
	 *	Returns weight of article (one or many).
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$amount			Amount to articles to get weight for
	 *	@return		float
	 */
	public function getWeight( int|string $articleId, int $amount = 1 ): float
	{
		return .0;
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
