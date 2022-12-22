<?php

use CeusMedia\Common\ADT\Collection\Dictionary;

class Logic_ShopBridge_Bookstore extends Logic_ShopBridge_Abstract
{
	public array $cache		= [];
	public string $path		= "catalog/bookstore/article/%articleId%";
	public float $taxPercent;
	public float $taxIncluded;

	/**	@var	Logic_Catalog_Bookstore		$logic		Bookstore logic instance */
	protected $logic;

	/**	@var	Dictionary			$moduleConfig */
	protected Dictionary $moduleConfig;

	public function changeQuantity( $articleId, $change )
	{
		return $this->logic->changeQuantity( $articleId, $change );
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
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$quantity		Amount of articles
	 *	@return		object
	 */
	public function get( $articleId, int $quantity = 1 ): object
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
	 *	@param		integer		$articleId		ID of article
	 *	@return		string
	 */
	public function getDescription( $articleId ): string
	{
		$article	= $this->check( $articleId );
		$words		= $this->env->getLanguage()->getWords( 'catalog/bookstore' );
		$label		= $words['article'][$article->series ? 'issn' : 'isbn'];
		return $label.': '.$article->isn;
	}

	/**
	 *	Returns link to article description.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		string
	 */
	public function getLink( $articleId ): string
	{
		return $this->logic->getArticleUri( (int) $articleId );
	}

	/**
	 *	Returns URL of article picture, if existing.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		boolean		$absolute
	 *	@return		string
	 */
	public function getPicture( $articleId, bool $absolute = FALSE ): string
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
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get price for
	 *	@return		float
	 */
	public function getPrice( $articleId, int $amount = 1 ): float
	{
		$amount		= abs( $amount );
		return $this->check( $articleId )->price * $amount;
	}

	/**
	 *	Returns tax of article (one or many).
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get tax for
	 *	@return		float
	 */
	public function getTax( $articleId, int $amount = 1 ): float
	{
		$amount		= abs( (integer) $amount );												//  sanitize amount
		$price		= $this->check( $articleId )->price;									//  get price of article
		$factor		= $this->taxPercent / 100;												//  calculate tax factor on top of price
		if( $this->taxIncluded )															//  tax is already included in price
			$factor	= $this->taxPercent / ( 100 + $this->taxPercent );						//  calculate tax factor within price
		return $price * $factor * $amount ;													//  calculate tax amount
	}

	/**
	 *	Returns title of article.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		string
	 */
	public function getTitle( $articleId ): string
	{
		return $this->check( $articleId )->title;
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
		return $this->check( $articleId )->weight * $amount;
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog_Bookstore( $this->env );
		$this->taxPercent	= (float) $this->env->getConfig()->get( 'module.shop.tax.percent' );
		$this->taxIncluded	= (float) $this->env->getConfig()->get( 'module.shop.tax.included' );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.catalog_bookstore.', TRUE );
	}
}
