<?php
class Logic_ShopBridge_Provision extends Logic_ShopBridge_Abstract{

	/**	@var	Logic_Provision						$logic */
	protected $logic;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected $taxRate = 19;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env
	 *	@return		void
	 */
	public function __onInit(){
		$this->logic		= Logic_Provision::getInstance( $this->env );
		$this->taxRate		= $this->env->getConfig()->get( 'module.catalog_provision.tax.rate' );
	}

	public function changeQuantity( $articleId, $change ){
	}

	/**
	 *	Checks existance of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param    	boolean		$strict			Flag: throw exception if article ID is invalid
	 *	@return		object						Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function check( $articleId, $strict = TRUE ){
		$article	= $this->logic->getProductLicense( $articleId );
		if( !$article )
			throw new RuntimeException( 'Article with ID '.$articleId.' is not existing' );
		return $article;
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
				'one'	=> $this->getTax( $articleId ),
				'rate'	=> $this->taxRate,
				'all'	=> $this->getTax( $articleId, $quantity ),
			),
			'single'		=> TRUE,
			'title'			=> $this->getTitle( $articleId ),
			'description'	=> $this->getDescription( $articleId ),
			'bridge'		=> $this->getBridgeClass(),
			'bridgeId'		=> $this->getBridgeId(),
		);
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getDescription( $articleId ){
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
	public function getLink( $articleId ){
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
	public function getPicture( $articleId, $absolute = FALSE ){
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
	public function getPrice( $articleId, $amount = 1 ){
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
	public function getTax( $articleId, $amount = 1 ){
		$productLicense		= $this->check( $articleId );
		return $productLicense->price * ( $this->taxRate / 100 ) * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getTitle( $articleId ){
		$productLicense		= $this->check( $articleId );
		return $productLicense->product->title.': '.$productLicense->title;
	}
}
?>
