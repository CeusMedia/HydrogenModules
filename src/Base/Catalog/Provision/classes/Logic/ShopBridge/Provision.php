<?php
class Logic_ShopBridge_Provision extends Logic_ShopBridge_Abstract
{
	/**	@var	Logic_Catalog_Provision				$logic */
	protected Logic_Catalog_Provision $logic;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected int $taxRate = 19;

	/** @todo implement */
	public function changeQuantity( int|string $articleId, int $change ): int
	{
		return 0;
	}

	/** @todo implement */
	public function getWeight( int|string $articleId, int $amount = 1 ): float
	{
		return .0;
	}

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		boolean			$strict			Flag: throw exception if article ID is invalid
	 *	@return		object							Bridged article data object if found
	 *	@throws		InvalidArgumentException		if not found
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function check( int|string $articleId, bool $strict = TRUE ): object
	{
		$article	= $this->logic->getProductLicense( $articleId );
		if( !$article )
			throw new RuntimeException( 'Article with ID '.$articleId.' is not existing' );
		return $article;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@param		integer			$quantity
	 *	@return		Entity_Shop_Bridge_Article
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( int|string $articleId, int $quantity = 1 ): object
	{
		$entity	= new Entity_Shop_Bridge_Article( [
			'id'			=> $articleId,
			'link'			=> $this->getLink( $articleId ),
			'title'			=> $this->getTitle( $articleId ),
			'description'	=> $this->getDescription( $articleId ),
			'bridge'		=> $this->getBridgeClass(),
			'bridgeId'		=> $this->getBridgeId(),
		] );
		$entity->picture->relative	= $this->getPicture( $articleId );
		$entity->picture->absolute	= $this->getPicture( $articleId, TRUE );
		$entity->price->one			= $this->getPrice( $articleId );
		$entity->price->all			= $this->getPrice( $articleId, $quantity );
		$entity->tax->rate			= $this->taxRate;
		$entity->tax->one			= $this->getTax( $articleId );
		$entity->tax->all			= $this->getTax( $articleId, $quantity );
		$entity->weight->one		= $this->getWeight( $articleId );
		$entity->weight->all		= $this->getWeight( $articleId, $quantity );
		$entity->single		= TRUE;
		return $entity;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getDescription( int|string $articleId ): string
	{
		$productLicense		= $this->check( $articleId );
		$descriptionLines	= explode( "\n", strip_tags ( $productLicense->description ) );
		return html_entity_decode( array_shift( $descriptionLines ) );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getLink( int|string $articleId ): string
	{
		$productLicense		= $this->check( $articleId );
		return 'catalog/provision/license/view/'.$productLicense->productId.'/'.$articleId;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@param		boolean			$absolute
	 *	@return		string
	 *	@todo		implement absolute mode
	 */
	public function getPicture( int|string $articleId, bool $absolute = FALSE ): string
	{
		return '';
//		$productLicense		= $this->check( $articleId );
//		$category	= $this->modelCategory->get( $image->galleryCategoryId );
//		$uri		= $this->logic->pathImages.'thumbnail/'.$category->path.'/'.$image->filename;
//		return $absolute ? $this->env->url.ltrim( $uri, './' ) : $uri;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@param		integer			$amount
	 *	@return		float
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getPrice( int|string $articleId, int $amount = 1 ): float
	{
		$productLicense		= $this->check( $articleId );
		return (float) $productLicense->price * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@param		integer			$amount
	 *	@return		float
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getTax( int|string $articleId, int $amount = 1 ): float
	{
		$productLicense		= $this->check( $articleId );
		return $productLicense->price * ( $this->taxRate / 100 ) * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getTitle( int|string $articleId ): string
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
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= Logic_Catalog_Provision::getInstance( $this->env );
		$this->taxRate		= $this->env->getConfig()->get( 'module.catalog_provision.tax.rate' );
	}
}
