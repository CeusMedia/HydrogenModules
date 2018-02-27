<?php
class Logic_ShopBridge_Bookstore extends Logic_ShopBridge_Abstract {

	/**	@var	Logic_Catalog_Bookstore		$logic		Bookstore logic instance */
	protected $logic;

	public $path		= "catalog/bookstore/article/%articleId%";
	public $taxPercent;
	public $taxIncluded;

	public function __onInit(){
		$this->logic		= new Logic_Catalog_Bookstore( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.catalog_bookstore.', TRUE );
		$this->taxPercent	= $this->env->getConfig()->get( 'module.shop.tax.percent' );			//  @todo deprecated?
		$this->taxIncluded	= $this->env->getConfig()->get( 'module.shop.tax.included' );			//  @todo deprecated?
	}

	public function changeQuantity( $articleId, $change ){
		return $this->logic->changeQuantity( $articleId, $change );
	}


	/**
	 *	Checks existance of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		object						Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function check( $articleId, $strict = TRUE ){
		$article	= $this->logic->getArticle( $articleId );
		if( $article )
			return $article;
		if( !$strict )
			return FALSE;
		throw new Exception( 'Invalid article ID: '.$articleId );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function get( $articleId, $quantity = 1 ){
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
	 *	@param		integer		$articleId		ID of article
	 *	@return		string
	 */
	public function getDescription( $articleId ){
		$article	= $this->check( $articleId );
		$words		= $this->env->getLanguage()->getWords( 'catalog/bookstore' );
		$label		= $words['article'][$article->series ? 'issn' : 'isbn'];
		return $label.': '.$article->isn;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		boolean		$absolute
	 *	@return		string
	 */
	public function getPicture( $articleId, $absolute = FALSE ){
		$uri		= $this->env->getConfig()->get( 'path.images' )."bookstore/no_picture.png";
		$article	= $this->logic->getArticle( $articleId );
		if( $article->cover ){
/*			$pathCovers	= $this->env->getConfig()->get( 'path.contents' ).'articles/covers/';
			$id			= str_pad( $article->articleId, 5, 0, STR_PAD_LEFT );
			$uri		= $pathCovers.'s/'.$id.'_'.$article->cover;*/
			$uri		= './file/bookstore/article/s/'.$article->cover;
		}
		return $absolute ? $this->env->url.$uri : $uri;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getPrice( $articleId, $amount = 1 ){
		$amount		= abs( (integer) $amount );
		return $this->check( $articleId )->price * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		float
	 */
	public function getLink( $articleId ){
		return $this->logic->getArticleUri( (int) $articleId );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getTax( $articleId, $amount = 1 ){
		$amount		= abs( (integer) $amount );												//  sanitize amount
		$price		= $this->check( $articleId )->price;									//  get price of article
		if( $this->taxIncluded )															//  tax is already included in price
			return $price * $this->taxPercent / ( 100 + $this->taxPercent );				//  calculate tax within price
		return $price * ( $this->taxPercent / 100 ) * $amount ;								//  otherwise calculate tax on top of price
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getTitle( $articleId ){
		return $this->check( $articleId )->title;
	}
}
?>
