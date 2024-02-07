<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class Logic_ShopBridge_Bookstore extends Logic_ShopBridge_Abstract
{
	/**	@var	Logic_Catalog_Bookstore		$logic		Bookstore logic instance */
	protected Logic_Catalog_Bookstore $logic;

	/**	@var	Dictionary					$moduleConfig */
	protected Dictionary $moduleConfig;

	public array $cache		= [];
	public string $path		= "catalog/bookstore/article/%articleId%";
	public float $taxPercent;
	public float $taxIncluded;

	/**
	 *	@param		string		$articleId
	 *	@param		int			$change
	 *	@return		int
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function changeQuantity( string $articleId, int $change ): int
	{
		return $this->logic->changeQuantity( $articleId, $change );
	}

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		string			$articleId		ID of article
	 *	@return		object|FALSE	Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function check( string $articleId, bool $strict = TRUE ): object|FALSE
	{
		if( isset( $this->cache[$articleId] ) )
			return $this->cache[$articleId];
		$article	= $this->logic->getArticle( $articleId );
		if( $article ){
			$this->cache[$articleId]	= $article;
			return $article;
		}
		if( !$strict )
			return FALSE;
		throw new InvalidArgumentException( 'Invalid article ID: '.$articleId );
	}

	/**
	 *	Returns complete information set of article available via shop bridge.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$quantity		Amount of articles
	 *	@return		object
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function get( string $articleId, int $quantity = 1 ): object
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
	 *	@param		string		$articleId		ID of article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getDescription( string $articleId ): string
	{
		$article	= $this->check( $articleId );
		$words		= $this->env->getLanguage()->getWords( 'catalog/bookstore' );
		$label		= $words['article'][$article->series ? 'issn' : 'isbn'];
		return $label.': '.$article->isn;
	}

	/**
	 *	Returns link to article description.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getLink( string $articleId ): string
	{
		return $this->logic->getArticleUri( (int) $articleId );
	}

	/**
	 *	Returns URL of article picture, if existing.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		boolean		$absolute
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getPicture( string $articleId, bool $absolute = FALSE ): string
	{
		$uri		= $this->env->getConfig()->get( 'path.images' )."bookstore/no_picture.png";
		$article	= $this->check( $articleId );
		if( $article->cover ){
/*			$pathCovers	= $this->env->getConfig()->get( 'path.contents' ).'articles/covers/';
			$id			= str_pad( $article->articleId, 5, 0, STR_PAD_LEFT );
			$uri		= $pathCovers.'s/'.$id.'_'.$article->cover;*/
			$uri		= './file/bookstore/article/s/'.$article->cover;
		}
		return $absolute ? str_replace( '/./', '/', $this->env->url.$uri ) : $uri;
	}

	/**
	 *	Returns price of article (one or many).
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get price for
	 *	@return		float
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getPrice( string $articleId, int $amount = 1 ): float
	{
		$amount		= abs( $amount );
		return (float) $this->check( $articleId )->price * $amount;
	}

	/**
	 *	Returns tax of article (one or many).
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get tax for
	 *	@return		float
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getTax( string $articleId, int $amount = 1 ): float
	{
		$amount		= abs( $amount );												//  sanitize amount
		$price		= $this->check( $articleId )->price;									//  get price of article
		$factor		= $this->taxPercent / 100;												//  calculate tax factor on top of price
		if( $this->taxIncluded )															//  tax is already included in price
			$factor	= $this->taxPercent / ( 100 + $this->taxPercent );						//  calculate tax factor within price
		return $price * $factor * $amount ;													//  calculate tax amount
	}

	/**
	 *	Returns title of article.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getTitle( string $articleId ): string
	{
		return $this->check( $articleId )->title;
	}

	/**
	 *	Returns weight of article (one or many).
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get weight for
	 *	@return		float
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getWeight( string $articleId, int $amount = 1 ): float
	{
		return (float) $this->check( $articleId )->weight * $amount;
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog_Bookstore( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.catalog_bookstore.', TRUE );
		$this->taxPercent	= (float) $this->env->getConfig()->get( 'module.shop.tax.percent' );
		$this->taxIncluded	= (float) $this->env->getConfig()->get( 'module.shop.tax.included' );
	}
}
