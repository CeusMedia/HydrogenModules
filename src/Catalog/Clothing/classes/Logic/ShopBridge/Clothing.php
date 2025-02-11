<?php

use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class Logic_ShopBridge_Clothing extends Logic_ShopBridge_Abstract
{
	/**	@var	Model_Catalog_Clothing_Category		$modelCategory	Category model instance */
	protected Model_Catalog_Clothing_Category $modelCategory;

	/**	@var	Model_Catalog_Clothing_Article		$modelArticle	Article model instance */
	protected Model_Catalog_Clothing_Article $modelArticle;

	protected ?Logic_Localization $localization;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected int $taxRate = 19;

	protected string $pathImages;

	public function getWeight( int|string $articleId, int $amount = 1 ): float
	{
		// TODO: Implement getWeight() method.
		return .0;
	}

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$change			Negative value on paid order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function changeQuantity( int|string $articleId, int $change ): int
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
	 *	@param		int|string		$articleId		ID of article
	 *	@return		object|FALSE					Bridged article data object if found
	 *	@throws		InvalidArgumentException		if not found
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function check( int|string $articleId, bool $strict = TRUE ): object|FALSE
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
	 *	@param		int|string		$articleId
	 *	@param		integer			$quantity
	 *	@return		object
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function get( int|string $articleId, int $quantity = 1 ): object
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
	 *	@param		int|string		$articleId
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getDescription( int|string $articleId ): string
	{
		$article	= $this->check( $articleId );
		return $article->description;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getLink( int|string $articleId ): string
	{
		$article	= $this->check( $articleId );
		return $this->pathImages.'products/'.$article->image;
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
		$article		= $this->check( $articleId );
		$category	= $this->modelCategory->get( $article->categoryId );
		$uri		= $this->pathImages.'products/'.$article->image;
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
		$article	= $this->check( $articleId );
		return $article->price * $amount;
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
		$article	= $this->check( $articleId );
		return $article->price * ( $this->taxRate / 100 ) * $amount;
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
