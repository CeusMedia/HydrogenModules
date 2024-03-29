<?php

use CeusMedia\HydrogenFramework\Environment;

class Logic_Catalog_Gallery
{
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

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment	$env
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
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

	public function countCategoryImages( $categoryId )
	{
		return count( $this->getCategoryImages( $categoryId ) );
	}

	public function getCategory( $categoryId )
	{
		$cacheKey	= 'catalog.gallery.category.'.$categoryId;
		$category	= $this->cache->get( $cacheKey );
		if( !$category ){
			if( !( $category = $this->modelCategory->get( $categoryId ) ) )
				return NULL;
			$this->cache->set( $cacheKey, $category );
		}
		return $category;
	}

	public function getCategoryImages( $categoryId )
	{
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

	public function getCategories()
	{
		$cacheKey	= 'catalog.gallery.categories';
		$categories	= $this->cache->get( $cacheKey );
		if( !$categories ){
			$conditions	= [];
			$orders		= [];
			$categories	= $this->modelCategory->getAll( $conditions, $orders );
			$this->cache->set( $cacheKey, $categories );
		}
		return $categories;
	}

	public function getImage( $imageId )
	{
		$cacheKey	= 'catalog.gallery.image.'.$imageId;
		$image		= $this->cache->get( $cacheKey );
		if( !$image ){
			if( !( $image = $this->modelImage->get( $imageId ) ) )
				return NULL;
			$this->cache->set( $cacheKey, $image );
		}
		return $image;
	}
}
