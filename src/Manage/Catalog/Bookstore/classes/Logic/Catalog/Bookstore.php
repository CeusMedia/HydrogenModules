<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Cache\SimpleCacheInterface;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\Image\Processing as ImageProcessing;
use CeusMedia\HydrogenFramework\Environment\Resource\Logic;

/**
 *	@todo	extract classes Logic_Upload and CeusMedia\Common\Alg\UnitParser
 */
class Logic_Catalog_Bookstore extends Logic
{
	/**	@var	SimpleCacheInterface						$cache */
	protected SimpleCacheInterface $cache;

	/**	@var	Logic_Frontend								$frontend */
	protected Logic_Frontend $frontend;

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

	/**	@var	Dictionary							$moduleConfig */
	protected Dictionary $moduleConfig;

	protected array $countArticlesInCategories		= [];
	protected string $pathArticleCovers;
	protected string $pathArticleDocuments;
	protected string $pathAuthorImages;

	/**
	 *	@todo		code doc
	 */
	public function addArticle( array $data ): string
	{
		$data['createdAt']	= time();
		$articleId	= $this->modelArticle->add( $data );
		$this->clearCacheForArticle( $articleId );
		return $articleId;
	}

	/**
	 *	@todo		code doc
	 */
	public function addArticleDocument( string $articleId, string $sourceFile, string $title, string $mimeType ): string
	{
		if( !file_exists( $sourceFile ) )
			throw new RuntimeException( 'File is not existing' );
		if( !is_readable( $sourceFile ) )
			throw new RuntimeException( 'File is not readable' );

		$logicBucket	= new Logic_FileBucket( $this->env );
		$logicBucket->setHashFunction( Logic_FileBucket::HASH_UUID );
		$options		= $this->moduleConfig->getAll( 'article.document.', TRUE );
		$extension		= pathinfo( $sourceFile, PATHINFO_EXTENSION );
		$article		= $this->getArticle( $articleId );
		$fileName		= Logic_Upload::sanitizeFileNameStatic( $article->title.' - '.$title.'.'.$extension );
		$logicBucket->add( $sourceFile, 'bookstore/document/'.$fileName, $mimeType, 'catalog_bookstore' );

		$data	= [
			'articleId'	=> $articleId,
			'status'	=> 0,
			'type'		=> 0,
			'url'		=> $fileName,
			'title'		=> $title,
		];
		$this->clearCacheForArticle( $articleId );													//
		$this->cache->remove( 'catalog.bookstore.tinymce.links.documents' );
		return $this->modelArticleDocument->add( $data );
	}

	/**
	 *	@todo		code doc
	 */
	public function addArticleTag( string $articleId, string $tag ): string
	{
		$data	= [
			'articleId'	=> $articleId,
			'tag'		=> $tag,
		];
		$this->clearCacheForArticle( $articleId );												//
		return $this->modelArticleTag->add( $data );
	}

	/**
	 *	@todo		code doc
	 */
	public function addAuthor( array $data ): string
	{
//		$data['createdAt']	= time();
		$this->clearCacheForAuthor( 0 );
		return  $this->modelAuthor->add( $data );
	}

	/**
	 *	@todo		code doc
	 */
	public function addAuthorImage( string $authorId, string $sourceFile, string $mimeType ): void
	{
		if( !file_exists( $sourceFile ) )
			throw new RuntimeException( 'File is not existing' );
		if( !is_readable( $sourceFile ) )
			throw new RuntimeException( 'File is not readable' );

		$image			= new Image( $sourceFile );
		$processor		= new ImageProcessing( $image );
		$logicBucket	= new Logic_FileBucket( $this->env );
		$logicBucket->setHashFunction( Logic_FileBucket::HASH_UUID );
		$options		= $this->moduleConfig->getAll( 'author.image.', TRUE );
		$author			= $this->getAuthor( $authorId );
		$extension		= pathinfo( $sourceFile, PATHINFO_EXTENSION );
		$fileName		= $author->firstname.' '.$author->lastname;
		$title			= Logic_Upload::sanitizeFileNameStatic( $fileName.'.'.$extension );

		$processor->scaleDownToLimit(
			$options->get( 'width' ),
			$options->get( 'height' ),
			$options->get( 'quality' )
		);
		$image->save( $sourceFile );
		$logicBucket->add( $sourceFile, 'bookstore/author/'.$title, $mimeType, 'catalog_bookstore' );

		$this->clearCacheForAuthor( $authorId );
		$this->editAuthor( $authorId, ['image' => $title] );
	}

	/**
	 *	@todo		code doc
	 */
	public function addAuthorToArticle( string $articleId, string $authorId, $role ): string
	{
		$data		= [
			'articleId'	=> $articleId,
			'authorId'	=> $authorId,
			'editor'	=> $role,
		];
		$relationId	= $this->modelArticleAuthor->add( $data );
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForAuthor( $authorId );													//
		return $relationId;
	}

	/** 
	 *	@todo		code doc
	 */
	public function addCategory( array $data ): string
	{
//		$data['registeredAt']	= time();
		$this->clearCacheForCategory( 0 );
		return $this->modelCategory->add( $data );
	}

	/**
	 *	@todo		code doc
	 */
	public function addCategoryToArticle( string $articleId, string $categoryId, ?string $volume = NULL ): string
	{
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$rank		= count( $this->getCategoryArticles( $categoryId ) ) + 1;
		$indices	= [
			'articleId'		=> $articleId,
			'categoryId'	=> $categoryId,
			'rank'			=> $rank,
			'volume'		=> $volume,
		];
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelArticleCategory->add( $indices );
	}

	/**
	 *	@todo		code doc
	 */
	public function checkArticleId( string $articleId, bool $throwException = FALSE ): bool
	{
		if( $this->modelArticle->has( (int) $articleId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid article ID '.$articleId );
		return FALSE;
	}

	/**
	 *	@todo		code doc
	 */
	public function checkAuthorId( string $authorId, bool $throwException = FALSE ): bool
	{
		if( $this->modelAuthor->has( (int) $authorId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid author ID '.$authorId );
		return FALSE;
	}

	/**
	 *	@todo		code doc
	 */
	public function checkCategoryId( string $categoryId, bool $throwException = FALSE ): bool
	{
		if( $this->modelCategory->has( (int) $categoryId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid category ID '.$categoryId );
		return FALSE;
	}

	/**
	 *	Removes cache files related to article after changes.
	 *	Uses clearCacheForCategory to invalidate category cache.
	 *	Attention: MUST NOT call clearCacheForAuthor.
	 *	@access		public
	 *	@param		integer		$articleId			ID of article to clear cache files for
	 *	@return		void
	 */
	protected function clearCacheForArticle( string $articleId ): void
	{
		$article	= $this->modelArticle->get( $articleId );										//  get article
		$this->cache->remove( 'catalog.bookstore.article.'.$articleId );										//  remove article cache
		$this->cache->remove( 'catalog.bookstore.article.author.'.$articleId );								//  remove article author cache
		$categories	= $this->modelArticleCategory->getAllByIndex( 'articleId', $articleId );		//  get related categories of article
		foreach( $categories as $category ){														//  iterate assigned categories
			$categoryId	= $category->categoryId;													//  get category ID of related category
			$this->clearCacheForCategory( $categoryId );
		}
		$this->cache->remove( 'catalog.bookstore.tinymce.images.articles' );
		$this->cache->remove( 'catalog.bookstore.tinymce.links.articles' );
		$this->cache->remove( 'tinymce.links' );
	}

	/**
	 *	Removes cache files related to article after changes.
	 *	Uses clearCacheForArticle to invalidate article cache.
	 *	@access		public
	 *	@param		string		$authorId			ID of author
	 *	@return		void
	 */
	protected function clearCacheForAuthor( string $authorId ): void
	{
		$relations	= $this->modelArticleAuthor->getAllByIndex( 'authorId', $authorId );			//  get all articles of author
		foreach( $relations as $relation ){															//  iterate article relations
			$this->clearCacheForArticle( $relation->articleId );									//  clear article cache
		}
		$this->cache->remove( 'catalog.bookstore.search.authors' );
		$this->cache->remove( 'catalog.bookstore.tinymce.images.authors' );
		$this->cache->remove( 'catalog.bookstore.tinymce.links.authors' );
		$this->cache->remove( 'tinymce.links' );
	}

	/**
	 *	Removes cache files related to categories after changes.
	 *	Attention: MUST NOT call clearCacheForArticle.
	 *	@access		public
	 *	@param		string		$categoryId			ID of category
	 *	@return		void
	 */
	protected function clearCacheForCategory( string $categoryId ): void
	{
		while( $categoryId ){																		//  loop while category ID exists
			$category	= $this->modelCategory->get( $categoryId );									//  get category of category ID
			if( $category ){																		//  category exists
				$this->cache->remove( 'catalog.bookstore.category.'.$categoryId );					//  remove category cache
				$this->cache->remove( 'catalog.bookstore.html.categoryArticleList.'.$categoryId );	//  remove category view cache
				$categoryId	= (int) $category->parentId;											//  category parent ID is category ID for next loop
			}
			else																					//  category is not existing
				$categoryId	= 0;																	//  no further loops
		}
		$this->cache->remove( 'catalog.bookstore.categories' );
		$this->cache->remove( 'catalog.bookstore.tinymce.links.categories' );
		$this->cache->remove( 'catalog.bookstore.count.categories.articles' );
//		$this->cache->remove( 'admin.categories.list.html' );
		$this->cache->remove( 'tinymce.links' );
	}

	/**
	 *	@todo		code doc
	 */
	public function countArticles( array $conditions = [] ): int
	{
		return $this->modelArticle->count( $conditions );
	}

	/**
	 *	Returns number of articles within a category or its sub categories, if enabled.
	 *	Uses cache 'catalog.count.categories.articles' in recursive mode.
	 *	@access		public
	 *	@param 		string		$categoryId		ID of category to count articles for
	 *	@param 		boolean		$recursive		Flag: count in sub categories, default: FALSE
	 *	@return		integer						Number of found articles in category
	 */
	public function countArticlesInCategory( string $categoryId, bool $recursive = FALSE ): int
	{
		if( $recursive && isset( $this->countArticlesInCategories[$categoryId] ) )
			return $this->countArticlesInCategories[$categoryId];
		$number		= count( $this->modelArticleCategory->getAllByIndex( 'categoryId', $categoryId ) );
		if( $recursive ){
			$categories	= $this->getCategories( ['parentId' => $categoryId] );
			foreach( $categories as $category )
				$number += $this->countArticlesInCategory( $category->categoryId );
		}
		return $number;
	}

	/**
	 *	@todo		code doc
	 */
	public function editArticle( string $articleId, array $data ): void
	{
		$this->checkArticleId( $articleId, TRUE );
//		$data['modifiedAt']	= time();
		$this->modelArticle->edit( $articleId, $data );
		$this->clearCacheForArticle( $articleId, TRUE );
	}

	/**
	 *	@todo		code doc
	 */
	public function editAuthor( string $authorId, array $data ): void
	{
		$this->checkAuthorId( $authorId, TRUE );
//		$data['modifiedAt']	= time();																//
		$this->clearCacheForAuthor( $authorId );													//
		$this->modelAuthor->edit( $authorId, $data );
	}

	/**
	 *	@todo		code doc
	 */
	public function editCategory( string $categoryId, array $data ): void
	{
		$this->checkCategoryId( $categoryId, TRUE );
		$old	= $this->modelCategory->get( $categoryId );
//		$data['modifiedAt']	= time();																//
		$this->modelCategory->edit( $categoryId, $data );
		$new	= $this->modelCategory->get( $categoryId );
		$this->clearCacheForCategory( $categoryId );												//
		$this->clearCacheForCategory( $old->parentId );
		$this->clearCacheForCategory( $new->parentId );
	}

	/**
	 *	@todo		code doc
	 */
	public function getArticle( string $articleId, bool $strict = TRUE ): object
	{
		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.article.'.$articleId ) ) )
			return (object) $data;
		$this->checkArticleId( $articleId, $strict );
		$data	= $this->modelArticle->get( $articleId );
		$this->cache->set( 'catalog.bookstore.article.'.$articleId, $data );
		return $data;
	}

	/**
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 */
	public function getArticles( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
#		$cacheKey	= md5( json_encode( [$conditions, $orders, $limits] ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.articles.'.$cacheKey ) ) )
#			return $data;
		$list	= [];
		foreach( $this->modelArticle->getAll( $conditions, $orders, $limits ) as $article )
			$list[$article->articleId]	= $article;
#		$this->cache->set( 'catalog.articles.'.$cacheKey, $list );
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
		if( !$articles )
			return [];
		$conditions	= ['articleId' => $articleIds];
		$articles	= $this->getArticles( $conditions, $orders, $limits );
		return $articles;
	}

	/**
	 *	@todo		code doc
	 */
	public function getArticlesFromAuthorIds( array $authorIds, bool $returnIds = FALSE ): array
	{
		$model		= new Model_Catalog_Bookstore_Article_Author( $this->env );
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
	 *	@todo		code doc
	 */
	public function getArticleUri( $articleOrId ): string
	{
		$article	= $articleOrId;
		if( is_int( $articleOrId ) )
			$article	= $this->getArticle( $articleOrId );
		if( !is_object( $article ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		$keywords	= $this->getUriPart( $article->title );
		return './catalog/article/'.$article->articleId.'-'.$keywords;
	}

	/**
	 *	@todo		use cache
	 */
	public function getAuthor( string $authorId ): object
	{
		$this->checkAuthorId( $authorId, TRUE );
		return $this->modelAuthor->get( $authorId );
	}

	/**
	 *	@todo		code doc
	 */
	public function getAuthors( array $conditions = [], array $orders = [] ): array
	{
		$list	= [];
		foreach( $this->modelAuthor->getAll( $conditions, $orders ) as $author )
			$list[$author->authorId]	= $author;
		return $list;
	}

	/**
	 *	Returns list of article authors.
	 *	@access		public
	 *	@param		string		$articleId			Article ID
	 *	@return		array
	 */
	public function getAuthorsOfArticle( string $articleId ): array
	{
		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.article.author.'.$articleId ) ) )
			return $data;
		$data	= $this->modelArticleAuthor->getAllByIndex( 'articleId', $articleId );
		$list	= [];
		foreach( $data as $entry ){
			$author	= $this->modelAuthor->get( $entry->authorId );
			$author->editor	= $entry->editor;
			$list[$author->lastname.'_'.$author->firstname]	= $author;
		}
//		ksort( $list );
		$this->cache->set( 'catalog.bookstore.article.author.'.$articleId, $list );
		return $list;
	}

	/**
	 *	@todo		code doc
	 */
	public function getAuthorUri( $authorOrId, bool $absolute = FALSE ): string
	{
		$author = $authorOrId;
		if( is_int( $authorOrId ) )
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
	 *	@todo		clean up
	 */
	public function getCategories( array $conditions = [], array $orders = [] ): array
	{
#		$cacheKey	= md5( json_encode( [$conditions, $orders] ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.categories.'.$cacheKey ) ) )
#			return $data;

		$list	= [];
		foreach( $this->modelCategory->getAll( $conditions, $orders ) as $category )
			$list[$category->categoryId]	= $category;
#		$this->cache->set( 'catalog.categories.'.$cacheKey, $list );
		return $list;
	}

	/**
	 *	@todo		code doc
	 */
	public function getCategoriesOfArticle( string $articleId ): array
	{
		$this->checkArticleId( $articleId, TRUE );
		$list			= [];
		$categoryIds	= [];
		$relations		= $this->modelArticleCategory->getAllByIndex( 'articleId', $articleId );
		foreach( $relations as $relation ){
			$category	= $this->modelCategory->get( $relation->categoryId );
			if( $category ){
				if( $category->parentId )
					$category->parent	= $this->modelCategory->get( $category->parentId );
				$category->volume	= $relation->volume;
				$list[$category->categoryId]		= $category;
			}
			else{
				$list[$relation->categoryId]		= (object) [
					'categoryId'	=> $relation->categoryId,
					'parentId'		=> 0,
					'label_de'		=> '- verwaist -',
					'volume'		=> $relation->volume,
					'rank'			=> 0,
				];
			}

		}
		return $list;
	}

	/**
	 *	@todo		code doc
	 */
	public function getCategory( string $categoryId ): object
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
	 */
	public function getCategoryArticles( $category, array $orders = [], array $limits = [] ): array
	{
#		$cacheKey	= md5( json_encode( [$category->categoryId, $orders, $limits] ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.category.articles.'.$cacheKey ) ) )
#			return $data;
		$conditions	= ['categoryId' => $category];
		if( is_object( $category ) )
			$conditions	= ['categoryId' => $category->categoryId];
		$relations	= $this->modelArticleCategory->getAll( $conditions, $orders, $limits );
		$articles	= [];
		$volumes	= [];

		foreach( $relations as $relation ){
			$article			= $this->getArticle( $relation->articleId );
			$article->articleCategoryId	= $relation->articleCategoryId;
			$article->rank		= $relation->rank;
			$article->volume	= $relation->volume;
			$articles[]			= $article;
		}
#		$this->cache->set( 'catalog.bookstore.category.articles.'.$cacheKey, $articles );
		return $articles;
	}

	/**
	 *	@todo		code doc
	 */
	public function getCategoryOfArticle( object $article ): object
	{
		$relation	= $this->modelArticleCategory->getByIndex( 'articleId', $article->articleId );
		$category			= $this->modelCategory->get( $relation->categoryId );
		$category			= $this->getCategory( $relation->categoryId );							//  @todo use this line for caching and remove line above
		$category->volume	= $relation->volume;
		$article->volume	= $relation->volume;
		return $category;
	}

	public function getDocuments( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelArticleDocument->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		code doc
	 */
	public function getDocumentsOfArticle( string $articleId ): array
	{
		return $this->modelArticleDocument->getAllByIndex( 'articleId', $articleId );
	}

	public function getTags( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelArticleTag->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		code doc
	 *	@todo		use cache by storing tags in article cache file
	 */
	public function getTagsOfArticle( string $articleId, bool $sort = FALSE ): array
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
	 */
	public function getUriPart( string $label, string $delimiter = "_" ): string
	{
		$label	= str_replace( ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'], $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label	= preg_replace( "/ +/", $delimiter, $label );
		return $label;
	}

	/**
	 *	Removes article with cover, documents, tags and relations to authors and categories.
	 *	Caches will be removed.
	 *	@todo		code doc
	 */
	public function removeArticle( string $articleId ): void
	{
		$this->removeArticleCover( $articleId );
		foreach( $this->getCategoriesOfArticle( $articleId ) as $relation )
			$this->removeArticleFromCategory( $articleId, $relation->categoryId );
		foreach( $this->getTagsOfArticle( $articleId ) as $relation )
			$this->removeArticleTag( $relation->articleTagId );
		foreach( $this->getAuthorsOfArticle( $articleId ) as $relation )
			$this->removeAuthorFromArticle( $articleId, $relation->authorId );
		foreach( $this->getDocumentsOfArticle( $articleId ) as $relation )
			$this->removeArticleDocument( $relation->articleDocumentId );
		$this->clearCacheForArticle( $articleId );
		$this->modelArticle->remove( $articleId );
	}

	/**
	 *	@todo		check if this method is used or deprecated
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 */
	public function removeArticleCover( string $articleId ): void
	{
		$article		= $this->getArticle( $articleId );
		$logicBucket	= new Logic_FileBucket( $this->env );
		$prefix			= 'bookstore/article/';
		$moduleId		= 'catalog_bookstore';
		if( $fileLarge = $logicBucket->getByPath( $prefix.'l/'.$article->cover, $moduleId ) )
			$logicBucket->remove( $fileLarge->fileId );
		if( $fileMedium = $logicBucket->getByPath( $prefix.'m/'.$article->cover, $moduleId ) )
			$logicBucket->remove( $fileMedium->fileId );
		if( $fileSmall = $logicBucket->getByPath( $prefix.'s/'.$article->cover, $moduleId ) )
			$logicBucket->remove( $fileSmall->fileId );
		$this->clearCacheForArticle( $articleId );
		$this->editArticle( $articleId, ['cover' => NULL] );
	}

	/**
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 */
	public function removeArticleDocument( string $documentId ): bool
	{
		$document		= $this->modelArticleDocument->get( $documentId );
		$logicBucket	= new Logic_FileBucket( $this->env );
		$prefix			= 'bookstore/document/';
		$moduleId		= 'catalog_bookstore';
		if( $file = $logicBucket->getByPath( $prefix.$document->url, $moduleId ) )
			$logicBucket->remove( $file->fileId );
		$this->clearCacheForArticle( $document->articleId );
		$this->cache->remove( 'catalog.bookstore.tinymce.links.documents' );
		return $this->modelArticleDocument->remove( $documentId );
	}

	/**
	 *	@todo		check if this method is used or deprecated
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 */
	public function removeArticleFromCategory( string $articleId, string $categoryId ): int
	{
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= [
			'articleId'		=> $articleId,
			'categoryId'	=> $categoryId,
		];
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelArticleCategory->removeByIndices( $indices );
	}

	/**
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 */
	public function removeArticleTag( string $articleTagId ): ?bool
	{
		$relation	= $this->modelArticleTag->get( $articleTagId );
		if( $relation ){
			$this->clearCacheForArticle( $relation->articleId );
			return $this->modelArticleTag->remove( $articleTagId );
		}
		return FALSE;
	}

	/**
	 *	@throws		ReflectionException
	 *	@todo		code doc
	 */
	public function removeAuthor( string $authorId ): void
	{
		$this->checkAuthorId( $authorId );
		$articles	= $this->getArticlesFromAuthorIds( [$authorId] );
		foreach( $articles as $article )
			$this->removeAuthorFromArticle( $article->articleId, $authorId );
		$this->removeAuthorImage( $authorId );
		$this->modelAuthor->remove( $authorId );
		$this->clearCacheForAuthor( $authorId );													//
	}

	/**
	 *	@todo		code doc
	 */
	public function removeAuthorFromArticle( string $articleId, string $authorId ): int
	{
		$this->checkArticleId( $articleId );
		$this->checkAuthorId( $authorId );
		$indices	= [
			'articleId'	=> $articleId,
			'authorId'	=> $authorId,
		];
		$result	= $this->modelArticleAuthor->removeByIndices( $indices );
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForAuthor( $authorId );													//
		return $result;
	}

	/**
	 *	@todo		check if this method is used or deprecated
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 */
	public function removeAuthorImage( string $authorId ): void
	{
		$author			= $this->getAuthor( $authorId );
		$logicBucket	= new Logic_FileBucket( $this->env );
		$prefix			= 'bookstore/author/';
		$moduleId		= 'catalog_bookstore';
		if( $file = $logicBucket->getByPath( $prefix.$author->image, $moduleId ) )
			$logicBucket->remove( $file->fileId );
		$this->editAuthor( $authorId, ['image' => NULL] );
		$this->clearCacheForAuthor( $authorId );													//
	}

	/**
	 *	@todo		code doc
	 */
	public function removeCategory( string $categoryId ): bool
	{
		$this->checkCategoryId( $categoryId );
		if( $this->countArticlesInCategory( $categoryId, TRUE ) )
			throw new RuntimeException( 'Category not empty' );
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelCategory->remove( $categoryId );
	}

	/**
	 *	@todo		code doc
	 */
	public function removeCategoryFromArticle( string $articleId, string $categoryId ): int
	{
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= [
			'articleId'	=> $articleId,
			'categoryId'	=> $categoryId,
		];
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelArticleCategory->removeByIndices( $indices );
	}

	/**
	 *	@todo		check if this method is used or deprecated
	 *	@todo		use cache if possible
	 *	@todo		code doc
	 */
	public function setArticleAuthorRole( string $articleId, string $authorId, $role ): void
	{
		$this->checkArticleId( $articleId );
		$this->checkAuthorId( $authorId );
		$indices	= ['articleId' => $articleId, 'authorId' => $authorId];
		$relation	= $this->modelArticleAuthor->getByIndices( $indices );
		if( $relation ){
			$this->modelArticleAuthor->edit( $relation->articleAuthorId, array( 'editor' => (int) $role ) );
			$this->clearCacheForArticle( $articleId );
			$this->clearCacheForAuthor( $authorId );
		}
	}

	/**
	 *	@todo		code doc
	 */
	public function setArticleCover( string $articleId, string $sourceFile, string $mimeType ): void
	{
		if( !file_exists( $sourceFile ) )
			throw new RuntimeException( 'File is not existing' );
		if( !is_readable( $sourceFile ) )
			throw new RuntimeException( 'File is not readable' );

		$image			= new Image( $sourceFile );
		$processor		= new ImageProcessing( $image );
		$logicBucket	= new Logic_FileBucket( $this->env );
		$logicBucket->setHashFunction( Logic_FileBucket::HASH_UUID );
		$width			= $image->getWidth();
		$height			= $image->getHeight();
		$options		= $this->moduleConfig->getAll( 'article.image.', TRUE );
		$article		= $this->getArticle( $articleId );
		$extension		= pathinfo( $sourceFile, PATHINFO_EXTENSION );
		$title			= Logic_Upload::sanitizeFileNameStatic( $article->title.'.'.$extension );
		if( $width > $options->get( 'medium.width' ) || $height > $options->get( 'medium.height' ) ){
			if( $width > $options->get( 'large.width' ) || $height > $options->get( 'large.height' ) ){
				$processor->scaleDownToLimit(
					$options->get( 'large.width' ),
					$options->get( 'large.height' ),
					$options->get( 'large.quality' )
				);
				$image->save( $sourceFile );
			}
			$logicBucket->add( $sourceFile, 'bookstore/article/l/'.$title, $mimeType, 'catalog_bookstore' );
		}
		$processor->scaleDownToLimit(
			$options->get( 'medium.width' ),
			$options->get( 'medium.height' ),
			$options->get( 'medium.quality' )
		);
		$image->save( $sourceFile );
		$logicBucket->add( $sourceFile, 'bookstore/article/m/'.$title, $mimeType, 'catalog_bookstore' );

		$processor->scaleDownToLimit(
			$options->get( 'small.width' ),
			$options->get( 'small.height' ),
			$options->get( 'small.quality' )
		);
		$image->save( $sourceFile );
		$logicBucket->add( $sourceFile, 'bookstore/article/s/'.$title, $mimeType, 'catalog_bookstore' );
		$this->editArticle( $articleId, ['cover' => $title] );
		$this->cache->remove( 'catalog.bookstore.tinymce.images.articles' );
	}

	/**
	 *	@throws		ReflectionException
	 *	@todo		code doc
	 */
	protected function __onInit(): void
	{
		$this->env->getRuntime()->reach( 'Logic_Catalog_Bookstore::init start' );
		$this->config				= $this->env->getConfig();
		$this->frontend				= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig			= $this->config->getAll( 'module.manage_catalog_bookstore.', TRUE );
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

		$basePath					= $this->frontend->getPath( 'contents' );
		$this->pathArticleCovers	= $basePath.$this->moduleConfig->get( 'path.covers' );
		$this->pathArticleDocuments	= $basePath.$this->moduleConfig->get( 'path.documents' );
		$this->pathAuthorImages		= $basePath.$this->moduleConfig->get( 'path.authors' );

		$cacheKey	= 'catalog.bookstore.count.categories.articles';
		if( NULL === ( $this->countArticlesInCategories = $this->cache->get( $cacheKey, NULL ) ) ){
			$list	= [];
			foreach( $this->getCategories() as $category )
				$list[$category->categoryId]	= $this->countArticlesInCategory( $category->categoryId, TRUE );
			$this->cache->set( $cacheKey, $this->countArticlesInCategories = $list );
		}
		$this->env->getRuntime()->reach( 'Logic_Catalog_Bookstore::init done' );
	}
}
