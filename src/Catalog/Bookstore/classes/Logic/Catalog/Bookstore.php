<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\Time\Converter as TimeConverter;
use CeusMedia\HydrogenFramework\Environment\Resource\Logic;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

/**
 *	@todo			remove LUV context, replace by methods of module "Resource::Frontend"
 */
class Logic_Catalog_Bookstore extends Logic
{
	/**	@var	CacheInterface								$cache */
	protected CacheInterface $cache;

	/**	@var	Model_Catalog_Bookstore_Article				$modelArticle */
	protected Model_Catalog_Bookstore_Article $modelArticle;

	/**	@var	Model_Catalog_Bookstore_Article_Author		$modelArticleAuthor */
	protected Model_Catalog_Bookstore_Article_Author $modelArticleAuthor;

	/**	@var	Model_Catalog_Bookstore_Article_Category	$modelArticleCategory */
	protected Model_Catalog_Bookstore_Article_Category $modelArticleCategory;

	/**	@var	Model_Catalog_Bookstore_Article_Document	$modelArticleDocument */
	protected Model_Catalog_Bookstore_Article_Document $modelArticleDocument;

	/**	@var	Model_Catalog_Bookstore_Article_Tag			$modelArticleTag */
	protected Model_Catalog_Bookstore_Article_Tag $modelArticleTag;

	/**	@var	Model_Catalog_Bookstore_Author				$modelAuthor */
	protected Model_Catalog_Bookstore_Author $modelAuthor;

	/**	@var	Model_Catalog_Bookstore_Category			$modelCategory */
	protected Model_Catalog_Bookstore_Category $modelCategory;

	protected string $articleUriTemplate					= 'catalog/bookstore/article/%2$d-%3$s';

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$change			Negative value on paid order, positive value on restock.
	 *	@return		integer|FALSE				Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function changeQuantity( int|string $articleId, int $change, bool $strict = TRUE ): int|FALSE
	{
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

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function checkArticleId( int|string $articleId, bool $throwException = FALSE ): bool
	{
		if( $this->modelArticle->has( $articleId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid article ID '.$articleId );
		return FALSE;
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		code doc
	 */
	public function checkAuthorId( int|string $authorId, bool $throwException = FALSE ): bool
	{
		if( $this->modelAuthor->has( $authorId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid author ID '.$authorId );
		return FALSE;
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		code doc
	 */
	public function checkCategoryId( int|string $categoryId, bool $throwException = FALSE ): bool
	{
		if( $this->modelCategory->has( $categoryId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid category ID '.$categoryId );
		return FALSE;
	}

	/**
	 *	@todo		code doc
	 */
	public function countArticles( array $conditions = [] ): int
	{
		return $this->modelArticle->count( $conditions );
	}

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function countArticlesInCategory( string $categoryId, bool $recursive = FALSE ): int
	{
		$number		= count( $this->modelArticleCategory->getAllByIndex( 'categoryId', $categoryId ) );
		if( $recursive ){
			$categories	= $this->getCategories( ['parentId' => $categoryId] );
			foreach( $categories as $category )
				$number += $this->countArticlesInCategory( $category->categoryId );
		}
		return $number;
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		code doc
	 */
	public function getArticle( int|string $articleId ): object
	{
		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.article.'.$articleId ) ) )
			return (object) $data;
		$this->checkArticleId( $articleId, TRUE );
		/** @var object $data */
		$data	= $this->modelArticle->get( $articleId );
		$this->cache->set( 'catalog.bookstore.article.'.$articleId, $data );
		return $data;
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		code doc
	 */
	public function getArticleCoverUrl( object|int|string $articleOrId, string $size = 'm', bool $absolute = FALSE, bool $urlEncoded = FALSE ): ?string
	{
		if( is_bool( $size ) )																		//  @deprecated remove after migration
			$size	= $size ? 's' : 'm';															//  @deprecated remove after migration
		$article	= $articleOrId;
		if( is_int( $articleOrId ) || is_string( $articleOrId ) )
			$article	= $this->getArticle( $articleOrId );
		if( !is_object( $article ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		if( !$article->cover )
			return NULL;
		$path			= 'bookstore/article/'.$size.'/'.$article->cover;
		$logicBucket	= new Logic_FileBucket( $this->env );
		$bucketFile		= $logicBucket->getByPath( $path/*, 'Catalog_Bookstore'*/ );
//print_m( $bucketFile );die;
		if( !$bucketFile )
			return NULL;
		if( $urlEncoded )
			$path	= rawurlencode( $path );
		return $absolute ? $this->env->url.'file/'.$path : './file/'.$path;
	}

	/**
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getArticleTag( int|string $articleTagId ): ?object
	{
		return $this->modelArticleTag->get( $articleTagId );
	}

	/**
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 */
	public function getArticles( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
#		$cacheKey	= md5( json_encode( [$conditions, $orders, $limits] ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.articles.'.$cacheKey ) ) )
#			return $data;
		$list	= [];
		foreach( $this->modelArticle->getAll( $conditions, $orders, $limits ) as $article )
			$list[$article->articleId]	= $article;
#		$this->cache->set( 'catalog.bookstore.articles.'.$cacheKey, $list );
		return $list;
	}

	public function getArticlesFromTags( array $tags, array $excludeArticleIds = [], array $orders = [], array $limits = [] ): array
	{
		$articleIds		= [];
		$articleTagsMap	= [];
		$relations		= $this->modelArticleTag->getAll( ['tag' => $tags] );

		foreach( $relations as $relation ){
			if( in_array( $relation->articleId, $excludeArticleIds ) )
				continue;
			if( !isset( $articleTagsMap[$relation->articleId] ) )
				$articleTagsMap[$relation->articleId]	= [];
			$articleTagsMap[$relation->articleId][]	= $relation;
		}
		foreach( $articleTagsMap as $articleId => $articleTags )
			$articleIds[$articleId]	= count( $articleTags );

		$list	= [];
		if( count( $articleTagsMap ) ){
			arsort( $articleIds );
			$filteredArticleIds	= array_diff( array_keys( $articleIds ), $excludeArticleIds );
			$articles	= $this->getArticles( ['articleId' => $filteredArticleIds], $orders, $limits );
			foreach( $filteredArticleIds as $articleId ){
				$list[$articleId]	= (object) [
					'tags'		=> $articleTagsMap[$articleId],
					'article'	=> $articles[$articleId],
					'matches'	=> $articleIds[$articleId]
				];
			}
		}
		return $list;
	}

	/**
	 *	@todo		code doc
	 */
	public function getArticlesFromAuthor( object $author, array $orders = [], array $limits = [] ): array
	{
		$articles	= $this->modelArticleAuthor->getAllByIndex( 'authorId', $author->authorId );
		$articleIds	= [];
		foreach( $articles as $article )
			$articleIds[]	= $article->articleId;
		if( $articleIds ){
			$conditions	= ['articleId' => $articleIds];
			return $this->getArticles( $conditions, $orders, $limits );
		}
		return [];
	}

	/**
	 *	@todo		code doc
	 */
	public function getArticlesFromAuthorIds( array $authorIds, bool $returnIds = FALSE ): array
	{
		$model		= new Model_Catalog_Article_Author( $this->env );
		$articles	= $model->getAll( ['authorId' => array_values( $authorIds )] );
		if( !$returnIds )
			return $articles;
		$ids	= [];
		foreach( $articles as $article )
			$ids[]	= $article->articleId;
		return $ids;
	}

	/**
	 *	@todo		code doc
	 */
	public function getArticlesFromAuthors( array $authors, bool $returnIds = FALSE ): array
	{
		$authorIds	= [];
		foreach( $authors as $author )
			$authorIds[]	= $author->authorId;
		return $this->getArticlesFromAuthorIds( $authorIds, $returnIds );
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		code doc
	 */
	public function getArticleUri( object|int|string $articleOrId, bool $absolute = FALSE ): string
	{
		$article	= $articleOrId;
		if( is_int( $articleOrId ) || is_string( $articleOrId ) )
			$article	= $this->getArticle( $articleOrId );
		if( !is_object( $article ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		$uri		= vsprintf( $this->articleUriTemplate, [
			0,
			$article->articleId,
			$this->getUriPart( $article->title ),
		] );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		use cache
	 */
	public function getAuthor( int|string $authorId ): ?object
	{
		$this->checkAuthorId( $authorId, TRUE );
		return $this->modelAuthor->get( $authorId );
	}

	/**
	 *	@todo		code doc
	 */
	public function getAuthors( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= [];
		foreach( $this->modelAuthor->getAll( $conditions, $orders, $limits ) as $author )
			$list[$author->authorId]	= $author;
		return $list;
	}

	/**
	 *	Returns list of article authors.
	 *	@access		public
	 *	@param		int|string		$articleId			Article ID
	 *	@return		array
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getAuthorsOfArticle( int|string $articleId ): array
	{
		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.article.author.'.$articleId ) ) )
			return $data;
		$data	= $this->modelArticleAuthor->getAllByIndex( 'articleId', $articleId );
		$list	= [];
		foreach( $data as $entry ){
			$author	= $this->modelAuthor->get( $entry->authorId );
			$author->editor	= $entry->editor;
			$list[$author->lastname]	= $author;
		}
//		ksort( $list );
		$this->cache->set( 'catalog.bookstore.article.author.'.$articleId, $list );
		return $list;
	}

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getAuthorUri( object|int|string $authorOrId, bool $absolute = FALSE ): string
	{
		$author	= $authorOrId;
		if( is_int( $authorOrId ) || is_string( $authorOrId ) )
			$author	= $this->getAuthor( $authorOrId );
		else if( !is_object( $author ) )
			throw new InvalidArgumentException( 'Given author data is invalid' );
		$name	= $author->lastname;
		if( $author->firstname )
			$name	= $author->firstname." ".$name;
		$uri	= 'catalog/bookstore/author/'.$author->authorId.'-'.$this->getUriPart( $name );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@todo		code doc
	 */
	public function getCategories( array $conditions = [], array $orders = [] ): array
	{
#		$cacheKey	= md5( json_encode( [$conditions, $orders] ) );
#		if( ( $data = $this->cache->get( 'catalog.bookstore.categories.'.$cacheKey ) ) )
#			return $data;

		$list	= [];
		foreach( $this->modelCategory->getAll( $conditions, $orders ) as $category )
			$list[$category->categoryId]	= $category;
#		$this->cache->set( 'catalog.bookstore.categories.'.$cacheKey, $list );
		return $list;
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		code doc
	 */
	public function getCategoriesOfArticle( int|string $articleId ): array
	{
		$this->checkArticleId( $articleId, TRUE );
		$list			= [];
		$relations		= $this->modelArticleCategory->getAllByIndex( 'articleId', $articleId );
		foreach( $relations as $relation ){
			$category	= $this->modelCategory->get( $relation->categoryId );
			if( $category ){
				if( $category->parentId )
					$category->parent	= $this->modelCategory->get( $category->parentId );
				$category->volume	= $relation->volume;
				$list[$category->categoryId]		= $category;
			}
		}
		return $list;
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		code doc
	 */
	public function getCategory( int|string $categoryId ): object
	{
		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.category.'.$categoryId ) ) )
			return (object) $data;
		$this->checkCategoryId( $categoryId, TRUE );
		$data	= $this->modelCategory->get( $categoryId );
		$this->cache->set( 'catalog.bookstore.category.'.$categoryId, $data );
		return $data;
	}

	/**
	 *	@todo		clean up
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getCategoryArticles( object $category, array $orders = [], array $limits = [] ): array
	{
#		$cacheKey	= md5( json_encode( [$category->categoryId, $orders, $limits] ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.category.articles.'.$cacheKey ) ) )
#			return $data;
		$conditions	= ['categoryId' => $category->categoryId];
		$relations	= $this->modelArticleCategory->getAll( $conditions, $orders, $limits );
		$articles	= [];

		foreach( $relations as $relation ){
			$article			= $this->getArticle( $relation->articleId );
			$article->volume	= $relation->volume;
			$articles[]			= $article;
		}
#		$this->cache->set( 'catalog.bookstore.category.articles.'.$cacheKey, $articles );
		return $articles;
	}

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getCategoryOfArticle( int|string $articleId ): object
	{
		$relation	= $this->modelArticleCategory->getByIndex( 'articleId', $articleId );
		$category			= $this->modelCategory->get( $relation->categoryId );
		$category->volume	= $relation->volume;
//		$article->volume	= $relation->volume;
		return $category;
	}

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getCategoryUri( object|int|string $categoryOrId, string $language = 'en', bool $absolute = FALSE ): string
	{
		$category	= $categoryOrId;
		if( is_int( $categoryOrId ) || is_string( $categoryOrId ) )
			$category	= $this->getCategory( $categoryOrId );
		$uri		= 'catalog/bookstore';
		if( $category->categoryId ){
			$labelKey	= 'label_'.$language;
			$keywords	= $this->getUriPart( $category->$labelKey ?? '' );
			$uri		.= '/category/'.$category->categoryId.'-'.$keywords;
		}
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@todo		code doc
	 */
	public function getDocumentsOfArticle( int|string $articleId ): array
	{
		return $this->modelArticleDocument->getAllByIndex( 'articleId', $articleId );
	}

	/**
	 *	@todo		code doc
	 *	@todo		use cache by storing tags in article cache file
	 */
	public function getTagsOfArticle( int|string $articleId, bool $sort = FALSE ): array
	{
		$tags	= $this->modelArticleTag->getAllByIndex( 'articleId', $articleId );
		$list	= [];
		foreach( $tags as $tag )
			$list[$tag->tag]	= $tag;
		if( $sort )
			ksort( $list );
		return $list;
	}

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getTagUri( object|int|string $tagOrId, string $language = 'en', bool $absolute = FALSE ): string
	{
		$tag	= $tagOrId;
		if( is_int( $tagOrId ) || is_string( $tagOrId ) )
			$tag	= $this->getArticleTag( $tagOrId );
		$uri		= 'catalog';
		if( isset( $tag->articleTagId ) ){
			$labelKey	= 'label_'.$language;
			$keywords	= $this->getUriPart( $tag->tag );
			$uri		.= '/tag/'.$tag->articleTagId.'-'.$keywords;
		}
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

/*  -------------------------------------------------  */

	/**
	 *	Indicates whether an Article is to be releases in the future.
	 *	@access		public
	 *	@param		int|string		$articleId			ID of Article
	 *	@return		bool
	 *	@todo		check if this method is used or deprecated
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function isFuture( int|string $articleId ): bool
	{
		$tc			= new TimeConverter();
		$article	= $this->modelArticle->get( $articleId );
		$format		= strpos( $article->publication, "." ) ? 'date' : 'year';
		$time		= $tc->convertToTimestamp( $article->publication, $format );
		return $time > time();
	}

	/*  --  PROTECTED  --  */

	/**
	 *	@todo		code doc
	 */
	protected function __onInit(): void
	{
		$this->env->getRuntime()->reach( 'Logic_Catalog_Bookstore::init start' );
		$this->cache				= $this->env->getCache();

		$this->modelArticle			= new Model_Catalog_Bookstore_Article( $this->env );
		$this->modelArticleAuthor	= new Model_Catalog_Bookstore_Article_Author( $this->env );
		$this->modelArticleCategory	= new Model_Catalog_Bookstore_Article_Category( $this->env );
		$this->modelArticleDocument	= new Model_Catalog_Bookstore_Article_Document( $this->env );
#		$this->modelArticleReview	= new Model_Catalog_Bookstore_Article_Review( $this->env );
		$this->modelArticleTag		= new Model_Catalog_Bookstore_Article_Tag( $this->env );
		$this->modelAuthor			= new Model_Catalog_Bookstore_Author( $this->env );
		$this->modelCategory		= new Model_Catalog_Bookstore_Category( $this->env );
#		$this->modelReview			= new Model_Catalog_Review( $this->env );
	}
}
