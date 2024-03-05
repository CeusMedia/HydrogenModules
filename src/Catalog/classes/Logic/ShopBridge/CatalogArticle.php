<?php

use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class Logic_ShopBridge_CatalogArticle extends Logic_ShopBridge_Abstract
{
	/**	@var	Logic_Catalog		$logic */
	protected Logic_Catalog $logic;

	public string $path		= 'catalog/article/%articleId%';
	public float $taxPercent;
	public float $taxIncluded;

	/**
	 *	@param		int|string		$articleId
	 *	@param		int			$change
	 *	@return		int
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function changeQuantity( int|string $articleId, int $change ): int
	{
		return $this->logic->changeQuantity( $articleId, $change );
	}

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@return		object|FALSE				Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function check( int|string $articleId, bool $strict = TRUE ): object|FALSE
	{
		$article	= $this->logic->getArticle( $articleId );
		if( $article )
			return $article;
		if( !$strict )
			return FALSE;
		throw new InvalidArgumentException( 'Invalid article ID: '.$articleId );
	}

	/**
	 *	Returns complete information set of article available via shop bridge.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$quantity		Amount of articles
	 *	@return		object
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function get( int|string $articleId, int $quantity = 1 ): object
	{
		return (object) [
			'id'		=> $articleId,
			'link'		=> $this->getLink( $articleId ),
			'picture'	=> (object) [
				'relative'	=> $this->getPicture( $articleId ),
				'absolute'	=> $this->getPicture( $articleId, TRUE ),
			],
			'price'		=> (object) [
				'one'		=> $this->getPrice( $articleId ),
				'all'		=> $this->getPrice( $articleId, $quantity ),
			],
			'tax'		=> (object) [
				'rate'		=> $this->taxPercent,
				'one'		=> $this->getTax( $articleId ),
				'all'		=> $this->getTax( $articleId, $quantity ),
			],
			'weight'	=> (object) [
				'one'		=> $this->getWeight( $articleId ),
				'all'		=> $this->getWeight( $articleId, $quantity ),
			],
			'title'			=> $this->getTitle( $articleId ),
			'description'	=> $this->getDescription( $articleId ),
			'bridge'		=> $this->getBridgeClass(),
			'bridgeId'		=> $this->getBridgeId(),
		];
	}

	public function getAll( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->logic->getArticles( $conditions, $orders, $limits );
	}

	/**
	 *	Returns short description of article.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getDescription( int|string $articleId ): string
	{
		$article	= $this->check( $articleId );
		$words		= $this->env->getLanguage()->getWords( 'catalog' );
		$label		= $words['article'][$article->series ? 'issn' : 'isbn'];
		return $label.': '.$article->isn;
	}

	/**
	 *	Returns link to article description.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getLink( int|string $articleId ): string
	{
		return $this->logic->getArticleUri( $articleId );
	}

	/**
	 *	Returns URL of article picture, if existing.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		boolean			$absolute
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getPicture( int|string $articleId, bool $absolute = FALSE ): string
	{
		$uri		= $this->env->getConfig()->get( 'path.images' )."no_picture.png";
		$article	= $this->logic->getArticle( $articleId );
		if( $article->cover ){
			$pathCovers	= $this->env->getConfig()->get( 'path.contents' ).'articles/covers/';
			$id			= str_pad( $article->articleId, 5, 0, STR_PAD_LEFT );
			$uri		= $pathCovers.$id."__".$article->cover;
		}
		return $absolute ? str_replace( '/./', '/', $this->env->url.$uri ) : $uri;
	}

	/**
	 *	Returns price of article (one or many).
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$amount			Amount to articles to get price for
	 *	@return		float
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getPrice( int|string $articleId, int $amount = 1 ): float
	{
		$amount		= abs( $amount );
		return $this->check( $articleId )->price * $amount;
	}

	/**
	 *	Returns tax of article (one or many).
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$amount			Amount to articles to get tax for
	 *	@return		float
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getTax( int|string $articleId, int $amount = 1 ): float
	{
		$amount		= abs( $amount );												//  sanitize amount
		$price		= $this->check( $articleId )->price;									//  get price of article
		if( $this->taxIncluded )															//  tax is already included in price
			return $price * $this->taxPercent / ( 100 + $this->taxPercent );				//  calculate tax within price
		return $price * ( $this->taxPercent / 100 ) * $amount ;								//  otherwise calculate tax on top of price
	}

	/**
	 *	Returns title of article.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getTitle( int|string $articleId ): string
	{
		return $this->check( $articleId )->title;
	}

	/**
	 *	Returns weight of article (one or many).
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$amount			Amount to articles to get weight for
	 *	@return		float
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getWeight( int|string $articleId, int $amount = 1 ): float
	{
		return (float) ( $this->check( $articleId )->weight * $amount );
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog( $this->env );
		$this->taxPercent	= (float) $this->env->getConfig()->get( 'module.shop.tax.percent' );
		$this->taxIncluded	= (float) $this->env->getConfig()->get( 'module.shop.tax.included' );
	}
}
