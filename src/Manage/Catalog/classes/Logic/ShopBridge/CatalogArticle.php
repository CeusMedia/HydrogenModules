<?php

use CeusMedia\Common\ADT\Collection\Dictionary;

class Logic_ShopBridge_CatalogArticle extends Logic_ShopBridge_Abstract
{
	/**	@var	Logic_Frontend			$frontend */
	protected Logic_Frontend $frontend;

	/**	@var	Logic_Catalog			$logic */
	protected Logic_Catalog $logic;

	/**	@var	Dictionary		$moduleConfig */
	protected Dictionary $moduleConfig;

	public string $path		= "catalog/article/%articleId%";
	public float $taxPercent;
	public float $taxIncluded;

	public function changeQuantity( string $articleId, int $change ): int
	{
		return $this->logic->changeQuantity( $articleId, $change );
	}

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@return		object|FALSE				Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function check( string $articleId, bool $strict = TRUE )
	{
		$this->logic->checkArticleId( $articleId, $strict );
		return $this->logic->getArticle( $articleId );
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
				'rate'	=> $this->taxPercent,
				'one'	=> $this->getTax( $articleId ),
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
	 *	@param		string		$articleId		ID of article
	 *	@return		string
	 */
	public function getDescription( string $articleId ): string
	{
		$article	= $this->check( $articleId );
		$words		= $this->env->getLanguage()->getWords( 'catalog' );
		$label		= $words['article'][$article->series ? 'issn' : 'isbn'];
		return $label.': '.$article->isn;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		boolean		$absolute
	 *	@return		string
	 */
	public function getPicture( string $articleId, bool $absolute = FALSE ): string
	{
		$uri		= $this->env->getConfig()->get( 'path.images' )."no_picture.png";
		$article	= $this->logic->getArticle( $articleId );
		if( $article->cover ){
			$pathCovers	= $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.covers' );
			$id			= str_pad( $article->articleId, 5, 0, STR_PAD_LEFT );
			$uri		= $pathCovers.$id."__".$article->cover;
		}
		return $absolute ? $this->env->url.$uri : $uri;
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
		$amount		= abs( (integer) $amount );
		return (float) $this->check( $articleId )->price * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@return		string
	 */
	public function getLink( string $articleId ): string
	{
		return $this->logic->getArticleUri( $articleId );
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
		$amount		= abs( (integer) $amount );												//  sanitize amount
		$price		= $this->check( $articleId )->price;									//  get price of article
		if( $this->taxIncluded )															//  tax is already included in price
			return $price * $this->taxPercent / ( 100 + $this->taxPercent );				//  calculate tax within price
		return $price * ( $this->taxPercent / 100 ) * $amount ;								//  otherwise calculate tax on top of price
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@return		string
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
	 */
	public function getWeight( string $articleId, int $amount = 1 ): float
	{
		return (float) $this->check( $articleId )->weight * $amount;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog( $this->env );
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
		$this->taxPercent	= (float) $this->env->getConfig()->get( 'module.shop.tax.percent' );
		$this->taxIncluded	= (float) $this->env->getConfig()->get( 'module.shop.tax.included' );
	}
}
