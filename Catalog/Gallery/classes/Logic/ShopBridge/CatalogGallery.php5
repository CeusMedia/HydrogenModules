<?php
class Logic_ShopBridge_CatalogGallery extends Logic_ShopBridge_Abstract{

	/**	@var	Logic_Catalog_Gallery				$logic			Gallery logic instanc */
	protected $logic;

	/**	@var	Model_Catalog_Gallery_Category		$modelCategory	Category model instance */
	protected $modelCategory;

	/**	@var	Model_Catalog_Gallery_Image			$modelImage		Gallery model instance */
	protected $modelImage;

	/**	@var	integer								$taxRate		Tax rate, configured by module */
	protected $taxRate = 7;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env
	 *	@return		void
	 */
	public function __onInit(){
		$this->logic			= new Logic_Catalog_Gallery( $this->env );
		$this->modelImage		= new Model_Catalog_Gallery_Image( $this->env );
		$this->modelCategory	= new Model_Catalog_Gallery_Category( $this->env );
		$this->taxRate			= $this->env->getConfig()->get( 'module.catalog_gallery.tax.rate' );
	}

	/**
	 *	Checks existance of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		object						Bridged article data object if found
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function check( $articleId, $strict = TRUE ){
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
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function get( $articleId, $quantity = 1 ){
		$image	= $this->check( $articleId );
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
		$image		= $this->check( $articleId );
		$category	= $this->modelCategory->get( $image->galleryCategoryId );
		return $category->title;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getLink( $articleId ){
		return $this->logic->pathModule.'image/'.$articleId;
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
		$image		= $this->check( $articleId );
		$category	= $this->modelCategory->get( $image->galleryCategoryId );
		$uri		= $this->logic->pathImages.'thumbnail/'.$category->path.'/'.$image->filename;
		return $absolute ? $this->env->url.ltrim( $uri, './' ) : $uri;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getPrice( $articleId, $amount = 1 ){
		$image	= $this->check( $articleId );
		return $image->price * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getTax( $articleId, $amount = 1 ){
		$image	= $this->check( $articleId );
		return $image->price * ( $this->taxRate / 100 ) * $amount;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getTitle( $articleId ){
		$image	= $this->check( $articleId );
		return $image->title ? $image->title : $image->filename;
	}
}
?>
