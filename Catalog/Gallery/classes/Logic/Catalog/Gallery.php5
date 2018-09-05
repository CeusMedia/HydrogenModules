<?php
class Logic_Catalog_Gallery{

	/**	@var	string							$pathImages */
	public $cache;

	protected $env;

	/**	@var	Model_Catalog_Gallery_Category	$modelCategory */
	protected $modelCategory;
	/**	@var	Model_Catalog_Gallery_Image		$modelCategory */
	protected $modelImage;

	/**	@var	string							$pathImages */
	public $pathImages;
	/**	@var	string							$pathImport */
	public $pathImport;
	/**	@var	string							$pathModule */
	public $pathModule;

	protected $articleUriTemplate				= 'catalog/gallery/image/%2$d-%3$s';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env
	 *	@return		void
	 */
	public function __construct( $env ){
		$this->env				= $env;
		$this->cache			= $this->env->getCache();
		$this->modelCategory	= new Model_Catalog_Gallery_Category( $this->env );
		$this->modelImage		= new Model_Catalog_Gallery_Image( $this->env );
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.catalog_gallery.', TRUE );

		$this->pathModule		= $this->cache->get( 'catalog.gallery.path.module' );
		if( !$this->pathModule ){
			$this->pathModule		= './catalog/gallery/';
			if( $this->env->getModules()->has( 'Info_Pages' ) )
				if( ( $logic = new Logic_Page( $this->env ) ) )
					if( $page = $logic->getPageFromController( 'Catalog_Gallery' ) )
						$this->pathModule		= './'.$page->identifier.'/';
			$this->cache->set( 'catalog.gallery.path.module', $this->pathModule );
		}

		$basePath			= $this->env->getConfig()->get( 'path.images' );
		$this->pathImages	= $basePath.$this->moduleConfig->get( 'path.images' );
		$this->pathImport	= $basePath.$this->moduleConfig->get( 'path.import' );
	}

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$change			Negative value on payed order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function changeQuantity( $articleId, $change ){
		return 1;
		$change		= (int) $change;
		$article	= $this->modelArticle->get( $articleId );
		if( !$article && $strict )
			throw new RuntimeException( 'Article with ID '.$articleId.' is not existing' );
		if( !$article )
			return FALSE;
		$this->modelArticle->edit( $articleId, array(
			'quantity'	=> $article->quantity + $change
		) );
		return $article->quantity + $change;
	}

	public function countCategoryImages( $categoryId ){
		return count( $this->getCategoryImages( $categoryId ) );
	}

	public function getCategory( $categoryId ){
		$categoryId	= (int) $categoryId;
		$cacheKey	= 'catalog.gallery.category.'.$categoryId;
		$category	= $this->cache->get( $cacheKey );
		if( !$category ){
			if( !( $category = $this->modelCategory->get( $categoryId ) ) )
				return NULL;
			$this->cache->set( $cacheKey, $category );
		}
		return $category;
	}

	public function getCategoryImages( $categoryId ){
		$cacheKey	= 'catalog.gallery.category.'.$categoryId.'.images';
		$images		= $this->cache->get( $cacheKey );
		if( !$images ){
			$images	= $this->modelImage->getAll(
				array( 'galleryCategoryId' => $categoryId ),
				array( 'rank' => 'ASC' )
			);
			$this->cache->set( $cacheKey, $images );
		}
		return $images;
	}

	public function getCategories(){
		$cacheKey	= 'catalog.gallery.categories';
		$categories	= $this->cache->get( $cacheKey );
		if( !$categories ){
			$conditions	= array();
			$orders		= array();
			$categories	= $this->modelCategory->getAll( $conditions, $orders );
			$this->cache->set( $cacheKey, $categories );
		}
		return $categories;
	}

	public function getImage( $imageId ){
		$imageId	= (int) $imageId;
		$cacheKey	= 'catalog.gallery.image.'.$imageId;
		$image		= $this->cache->get( $cacheKey );
		if( !$image ){
			if( !( $image = $this->modelImage->get( $imageId ) ) )
				return NULL;
			$this->cache->set( $cacheKey, $image );
		}
		return $image;
	}

	public function getImageUri( $imageOrId, $absolute = FALSE ){
		$image		= $imageOrId;
		if( is_int( $imageOrId ) )
			$image	= $this->getImage( $imageOrId );
		if( !is_object( $productLicense ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		$uri	= vsprintf( $this->articleUriTemplate, array(
			$image->galleryCategoryId,
			$image->galleryImageId,
			$this->getUriPart( $image->title ),
		) );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getUriPart( $label, $delimiter = "_" ){
		$label	= str_replace( array( 'ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß' ), array( 'ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss' ), $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label	= preg_replace( "/ +/", $delimiter, $label );
		return $label;
	}

	public function setArticleUri( $articleUriTemplate ){
		$this->articleUriTemplate	= $articleUriTemplate;
	}
}
?>
