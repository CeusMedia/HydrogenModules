<?php

use CeusMedia\Cache\SimpleCacheInterface;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Catalog_Gallery extends Logic
{
	/**	@var	SimpleCacheInterface			$cache */
	public SimpleCacheInterface $cache;

	/**	@var	Environment						$env */
	protected Environment $env;

	protected Dictionary $moduleConfig;

	/**	@var	Model_Catalog_Gallery_Category	$modelCategory */
	protected Model_Catalog_Gallery_Category $modelCategory;

	/**	@var	Model_Catalog_Gallery_Image		$modelCategory */
	protected Model_Catalog_Gallery_Image $modelImage;

	/**	@var	string							$pathImages */
	public string $pathImages;

	/**	@var	string							$pathImport */
	public string $pathImport;

	/**	@var	string							$pathModule */
	public string $pathModule;

	protected string $articleUriTemplate		= 'catalog/gallery/image/%2$d-%3$s';

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$change			Negative value on paid order, positive value on restock.
	 *	@return		integer							Article quantity in stock after change
	 *	@throws		InvalidArgumentException		if not found
	 *	@todo		implement
	 */
	public function changeQuantity( int|string $articleId, int $change ): int
	{
		return 1;
		$article	= $this->modelArticle->get( $articleId );
		if( !$article && $strict )
			throw new RuntimeException( 'Article with ID '.$articleId.' is not existing' );
		if( !$article )
			return FALSE;
		$this->modelArticle->edit( $articleId, [
			'quantity'	=> $article->quantity + $change
		] );
		return $article->quantity + $change;
	}

	public function countCategoryImages( int|string $categoryId ): int
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
				['galleryCategoryId' => $categoryId],
				['rank' => 'ASC']
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

	public function getImage( int|string $imageId ): ?object
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

	public function getImageUri( int|string $imageOrId, bool $absolute = FALSE ): string
	{
		$image		= $imageOrId;
		if( is_int( $imageOrId ) )
			$image	= $this->getImage( $imageOrId );
		if( !is_object( $image ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		$uri	= vsprintf( $this->articleUriTemplate, [
			$image->galleryCategoryId,
			$image->galleryImageId,
			$this->getUriPart( $image->title ),
		] );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@todo		code doc
	 */
	public function getUriPart( string $label, string $delimiter = '_' ): string
	{
		$label	= str_replace( ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'], $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		return preg_replace( "/ +/", $delimiter, $label );
	}

	public function setArticleUri( string $articleUriTemplate ): self
	{
		$this->articleUriTemplate	= $articleUriTemplate;
		return $this;
	}

	/**
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->cache			= $this->env->getCache();
		$this->modelCategory	= new Model_Catalog_Gallery_Category( $this->env );
		$this->modelImage		= new Model_Catalog_Gallery_Image( $this->env );
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.catalog_gallery.', TRUE );

		$this->pathModule		= $this->cache->get( 'catalog.gallery.path.module' );
		if( !$this->pathModule ){
			$this->pathModule		= './catalog/gallery/';
			if( $this->env->getModules()->has( 'Info_Pages' ) ){
				$logic = new Logic_Page( $this->env );
				/** @var ?Entity_Page $page */
				$page = $logic->getPageFromController( 'Catalog_Gallery' );
				if( NULL !== $page )
					$this->pathModule		= './'.$page->identifier.'/';
			}
			$this->cache->set( 'catalog.gallery.path.module', $this->pathModule );
		}

		$basePath			= $this->env->getConfig()->get( 'path.images' );
		$this->pathImages	= $basePath.$this->moduleConfig->get( 'path.images' );
		$this->pathImport	= $basePath.$this->moduleConfig->get( 'path.import' );
	}
}
