<?php
class Logic_ShopBridge_Provision extends Logic_ShopBridge_Abstract
{
	/**	@var	Logic_Catalog_Provision				$logic */
	protected Logic_Catalog_Provision $logic;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected int $taxRate = 19;

	/** @todo implement */
	public function changeQuantity( $articleId, $change ): int
	{
		return 0;
	}

	/** @todo implement */
	public function getWeight( $articleId, int $amount = 1 )
	{
		return 0;
	}

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param    	boolean		$strict			Flag: throw exception if article ID is invalid
	 *	@return		object						Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function check( $articleId, bool $strict = TRUE )
	{
		$article	= $this->logic->getProductLicense( $articleId );
		if( !$article )
			throw new RuntimeException( 'Article with ID '.$articleId.' is not existing' );
		return $article;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@param		integer		$quantity
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
			'price'	=> (object) [
				'one'	=> $this->getPrice( $articleId ),
				'all'	=> $this->getPrice( $articleId, $quantity ),
			],
			'tax'	=> (object) [
				'one'	=> $this->getTax( $articleId ),
				'rate'	=> $this->taxRate,
				'all'	=> $this->getTax( $articleId, $quantity ),
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
		$productLicense		= $this->check( $articleId );
		$descriptionLines	= explode( "\n", strip_tags ( $productLicense->description ) );
		return html_entity_decode( array_shift( $descriptionLines ) );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getLink( $articleId ): string
	{
		$productLicense		= $this->check( $articleId );
		return 'catalog/provision/license/view/'.$productLicense->productId.'/'.$articleId;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		boolean		$absolute
	 *	@return		string
	 *	@todo		implement absolute mode
	 */
	public function getPicture( $articleId, bool $absolute = FALSE )
	{
		return '';
		$productLicense		= $this->check( $articleId );
//		$category	= $this->modelCategory->get( $image->galleryCategoryId );
//		$uri		= $this->logic->pathImages.'thumbnail/'.$category->path.'/'.$image->filename;
//		return $absolute ? $this->env->url.ltrim( $uri, './' ) : $uri;
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
		$productLicense		= $this->check( $articleId );
		return $productLicense->price * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getTax( $articleId, int $amount = 1 )
	{
		$productLicense		= $this->check( $articleId );
		return $productLicense->price * ( $this->taxRate / 100 ) * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getTitle( $articleId ): string
	{
		$productLicense		= $this->check( $articleId );
		return $productLicense->product->title.': '.$productLicense->title;
	}

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic		= Logic_Catalog_Provision::getInstance( $this->env );
		$this->taxRate		= $this->env->getConfig()->get( 'module.catalog_provision.tax.rate' );
	}
}
