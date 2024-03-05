<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;
use Psr\SimpleCache\CacheInterface;

class Logic_Catalog_GalleryManager
{
	/**	@var	CacheInterface							$pathImages */
	public CacheInterface $cache;

	protected Environment $env;

	/**	@var	Model_Catalog_Gallery_Category	$modelCategory */
	protected Model_Catalog_Gallery_Category $modelCategory;
	/**	@var	Model_Catalog_Gallery_Image		$modelCategory */
	protected Model_Catalog_Gallery_Image $modelImage;
	protected Dictionary $moduleConfig;
	/**	@var	string							$pathImages */
	public string $pathImages;
	/**	@var	string							$pathImport */
	public string $pathImport;
	/**	@var	string							$pathModule */
	public string $pathModule;

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

	public function countCategoryImages( int|string $categoryId ): ?int
	{
		return count( $this->getCategoryImages( $categoryId ) );
	}

	public function getCategory( int|string $categoryId ): ?object
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

	public function getCategoryImages( int|string $categoryId ): array
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

	public function getCategories(): array
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

	public function getImage( $imageId ): ?object
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
