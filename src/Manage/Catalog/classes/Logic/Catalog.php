<?php

use CeusMedia\Cache\SimpleCacheInterface;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\Image\ThumbnailCreator as ImageThumbnailCreator;
use CeusMedia\HydrogenFramework\Environment\Resource\Logic;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

/**
 *	@todo	extract classes Logic_Upload and CeusMedia\Common\Alg\UnitParser
 */
class Logic_Catalog extends Logic
{

	/**	@var	SimpleCacheInterface				$cache */
	protected SimpleCacheInterface $cache;

	/**	@var	Logic_Frontend						$frontend */
	protected Logic_Frontend $frontend;

	/**	@var	Model_Catalog_Article				$modelArticle */
	protected Model_Catalog_Article $modelArticle;

	/**	@var	Model_Catalog_Article_Author		$modelArticleAuthor */
	protected Model_Catalog_Article_Author $modelArticleAuthor;

	/**	@var	Model_Catalog_Article_Category		$modelArticleCategory */
	protected Model_Catalog_Article_Category $modelArticleCategory;

	/**	@var	Model_Catalog_Article_Document		$modelArticleDocument */
	protected Model_Catalog_Article_Document $modelArticleDocument;

	/**	@var	Model_Catalog_Article_Tag			$modelArticleTag */
	protected Model_Catalog_Article_Tag $modelArticleTag;

	/**	@var	Model_Catalog_Author				$modelAuthor */
	protected Model_Catalog_Author $modelAuthor;

	/**	@var	Model_Catalog_Category				$modelCategory */
	protected Model_Catalog_Category $modelCategory;

	/**	@var	Dictionary							$moduleConfig */
	protected Dictionary $moduleConfig;

	protected array $countArticlesInCategories		= [];
	protected string $pathArticleCovers;
	protected string $pathArticleDocuments;
	protected string $pathAuthorImages;

	/**
	 *	@todo		 code doc
	 */
	public function addArticle( $data )
	{
		$data['createdAt']	= time();
		$articleId	= $this->modelArticle->add( $data );
		$this->cache->delete( 'catalog.tinymce.images.articles' );
		$this->cache->delete( 'catalog.tinymce.links.articles' );
		return $articleId;
	}

	/**
	 *	@todo		 code doc
	 */
	public function addArticleCover( $articleId, $file )
	{
		if( !is_array( $file ) )
			throw new InvalidArgumentException( 'File must be an upload array' );
		if( !isset( $file['name'] ) || !isset( $file['tmp_name'] ) )
			throw new InvalidArgumentException( 'File must be a valid upload array' );
		$id			= str_pad( $articleId, 5, 0, STR_PAD_LEFT );
		$file		= (object) $file;
		$info		= (object) pathinfo( $file->name );
		$imagename	= $info->basename;
		$extension	= $info->extension;

		/*  --  STORE UPLOADED IMAGE  --  */
		$imagename	= md5( base64_encode( $file->name ) );
		$imagename	.= ".".$extension;
		$uriSource	= $this->pathArticleCovers.$id."_".$imagename;
		if( !move_uploaded_file( $file->tmp_name, $uriSource ) )
			throw new RuntimeException( 'Storing uploaded file failed' );

		/*  --  SCALE MAIN IMAGE  --  */
		$imageWidth		= $this->moduleConfig->get( 'article.image.maxWidth' );
		$imageHeight	= $this->moduleConfig->get( 'article.image.maxHeight' );
		$imageQuality	= $this->moduleConfig->get( 'article.image.quality' );
		$creator		= new ImageThumbnailCreator( $uriSource, $uriSource );
		$creator->thumbizeByLimit( $imageWidth, $imageHeight );

		/*  --  CREATE THUMBNAIL IMAGE  --  */
		$uriThumb		= $this->pathArticleCovers.$id."__".$imagename;
		$thumbWidth		= $this->moduleConfig->get( 'article.image.thumb.maxWidth' );
		$thumbHeight	= $this->moduleConfig->get( 'article.image.thumb.maxHeight' );
		$thumbQuality	= $this->moduleConfig->get( 'article.image.thumb.quality' );
		$creator		= new ImageThumbnailCreator( $uriSource, $uriThumb );
		$creator->thumbizeByLimit( $thumbWidth, $thumbHeight );

		$this->editArticle( $articleId, ['cover' => $imagename] );
		$this->cache->delete( 'catalog.tinymce.images.articles' );
	}

	/**
	 *	@todo		 code doc
	 */
	public function addArticleDocument( $articleId, $file, $title )
	{
		if( !is_array( $file ) )
			throw new InvalidArgumentException( 'File must be an upload array' );
		if( !isset( $file['name'] ) || !isset( $file['tmp_name'] ) )
			throw new InvalidArgumentException( 'File must be a valid upload array' );
		$id			= str_pad( $articleId, 5, 0, STR_PAD_LEFT );
		$file		= (object) $file;
		$info		= (object) pathinfo( $file->name );
		$imagename	= $info->basename;
		$extension	= $info->extension;

		/*  --  STORE UPLOADED DOCUMENT  --  */
		$filename	= md5( base64_encode( $file->name.time() ) );
		$filename	.= ".".$extension;
		$uri		= $this->pathArticleDocuments.$id."_".$filename;
		if( !move_uploaded_file( $file->tmp_name, $uri ) )
			throw new RuntimeException( 'Storing uploaded file failed' );

		$data	= [
			'articleId'	=> $articleId,
			'type'			=> $extension,
			'url'			=> $filename,
			'title'			=> $title,
		];
		$this->clearCacheForArticle( $articleId );													//
		$this->cache->delete( 'catalog.tinymce.links.documents' );
		return $this->modelArticleDocument->add( $data );
	}

	/**
	 *	@todo		 code doc
	 */
	public function addArticleTag( $articleId, $tag )
	{
		$data	= [
			'articleId'	=> $articleId,
			'tag'		=> $tag,
		];
		$this->clearCacheForArticle( $articleId );												//
		return $this->modelArticleTag->add( $data );
	}

	/**
	 *	@todo		 code doc
	 */
	public function addAuthor( $data )
	{
//		$data['createdAt']	= time();
		$this->clearCacheForAuthor( 0 );
		return  $this->modelAuthor->add( $data );
	}

	/**
	 *	@todo		 code doc
	 */
	public function addAuthorImage( $authorId, $file )
	{
		if( !is_array( $file ) )
			throw new InvalidArgumentException( 'File must be an upload array' );
		if( !isset( $file['name'] ) || !isset( $file['tmp_name'] ) )
			throw new InvalidArgumentException( 'File must be a valid upload array' );
		$id			= str_pad( $authorId, 5, 0, STR_PAD_LEFT );
		$file		= (object) $file;
		$info		= (object) pathinfo( $file->name );
		$imagename	= $info->basename;
		$extension	= $info->extension;

		/*  --  STORE UPLOADED IMAGE  --  */
		$imagename	= md5( base64_encode( $file->name ) );
		$imagename	.= ".".$extension;
		$uriSource	= $this->pathAuthorImages.$id."_".$imagename;
		if( !move_uploaded_file( $file->tmp_name, $uriSource ) )
			throw new RuntimeException( 'Storing uploaded file failed' );

		/*  --  SCALE MAIN IMAGE  --  */
		$imageWidth		= $this->moduleConfig->get( 'author.image.maxWidth' );
		$imageHeight	= $this->moduleConfig->get( 'author.image.maxHeight' );
		$imageQuality	= $this->moduleConfig->get( 'author.image.quality' );
		$creator		= new ImageThumbnailCreator( $uriSource, $uriSource );
		$creator->thumbizeByLimit( $imageWidth, $imageHeight );
		$this->clearCacheForAuthor( $authorId );
		$this->editAuthor( $authorId, ['image' => $imagename] );
	}

	/**
	 *	@todo		 code doc
	 */
	public function addAuthorToArticle( $articleId, $authorId, $role )
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
	 *	@todo		 code doc
	 */
	public function addCategory( $data )
	{
//		$data['registeredAt']	= time();
		$this->clearCacheForCategory( 0 );
		return $this->modelCategory->add( $data );
	}

	/**
	 *	@todo		 code doc
	 */
	public function addCategoryToArticle( $articleId, $categoryId, $volume = NULL )
	{
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= [
			'articleId'		=> $articleId,
			'categoryId'	=> $categoryId,
			'volume'		=> $volume,
		];
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelArticleCategory->add( $indices );
	}

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$change			Negative value on payed order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 */
	public function changeQuantity( $articleId, $change, bool $strict = TRUE )
	{
		$change		= (int) $change;
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
	 *	@todo		 code doc
	 */
	public function checkArticleId( $articleId, $throwException = FALSE )
	{
		if( $this->modelArticle->has( (int) $articleId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid article ID '.$articleId );
		return FALSE;
	}

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function checkAuthorId( $authorId, $throwException = FALSE ): bool
	{
		if( $this->modelAuthor->has( (int) $authorId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid author ID '.$authorId );
		return FALSE;
	}

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
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
	 *	@todo		 code doc
	 */
	public function countArticles( $conditions = [] ): int
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
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function editArticle( string $articleId, array $data ): void
	{
		$this->checkArticleId( $articleId, TRUE );
//		$data['modifiedAt']	= time();
		$this->modelArticle->edit( $articleId, $data );
		$this->clearCacheForArticle( $articleId );
	}

	/**
	 *	@todo		code doc
	 *	@throws		SimpleCacheInvalidArgumentException
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
	 *	@throws		SimpleCacheInvalidArgumentException
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
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getArticle( $articleId ): object
	{
		if( NULL !== ( $data = $this->cache->get( 'catalog.article.'.$articleId ) ) )
			return $data;
		$this->checkArticleId( $articleId, TRUE );
		$data	= $this->modelArticle->get( $articleId );
		$this->cache->set( 'catalog.article.'.$articleId, $data );
		return $data;
	}

	/**
	 *	@todo		 use cache if possible
	 *	@todo		 code doc
	 */
	public function getArticles( $conditions = [], $orders = [], $limits = [] ): array
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
	 *	@todo		 code doc
	 */
	public function getArticlesFromAuthor( $author, $orders = [], $limits = [] ): array
	{
		$articles	= $this->modelArticleAuthor->getAllByIndex( 'authorId', $author->authorId );
		$articleIds	= [];
		foreach( $articles as $article )
			$articleIds[]	= $article->articleId;
		if( !$articles )
			return [];
		$conditions	= ['articleId' => $articleIds];
		return $this->getArticles( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		 code doc
	 */
	public function getArticlesFromAuthorIds( $authorIds, $returnIds = FALSE ): array
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
	 *	@todo		 code doc
	 */
	public function getArticlesFromAuthors( $authors, $returnIds = FALSE ): array
	{
		$authorIds	= [];
		foreach( $authors as $author )
			$authorIds[]	= $author->authorId;
		return $this->getArticlesFromAuthorIds( $authorIds, $returnIds );
	}

	/**
	 *	@todo		 code doc
	 */
	public function getArticleUri( object|string $articleOrId ): string
	{
		$article	= $articleOrId;
		if( is_string( $articleOrId ) )
			$article	= $this->getArticle( $articleOrId );
		if( !is_object( $article ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		$keywords	= $this->getUriPart( $article->title );
		return './catalog/article/'.$article->articleId.'-'.$keywords;
	}

	/**
	 *	@todo		 use cache
	 */
	public function getAuthor( $authorId )
	{
		$this->checkAuthorId( $authorId, TRUE );
		return $this->modelAuthor->get( $authorId );
	}

	/**
	 *	@todo		 code doc
	 */
	public function getAuthors( $conditions = [], $orders = [] )
	{
		$list	= [];
		foreach( $this->modelAuthor->getAll( $conditions, $orders ) as $author )
			$list[$author->authorId]	= $author;
		return $list;
	}

	/**
	 *	Returns list of article authors.
	 *	@access		public
	 *	@param		integer		$articleId			Article ID
	 *	@return		array
	 */
	public function getAuthorsOfArticle( $articleId )
	{
		if( NULL !== ( $data = $this->cache->get( 'catalog.article.author.'.$articleId ) ) )
			return $data;
		$data	= $this->modelArticleAuthor->getAllByIndex( 'articleId', $articleId );
		$list	= [];
		foreach( $data as $entry ){
			$author	= $this->modelAuthor->get( $entry->authorId );
			$author->editor	= $entry->editor;
			$list[$author->lastname.'_'.$author->firstname]	= $author;
		}
//		ksort( $list );
		$this->cache->set( 'catalog.article.author.'.$articleId, $list );
		return $list;
	}

	/**
	 *	@todo		 code doc
	 */
	public function getAuthorUri( $authorOrId, $absolute = FALSE )
	{
		$author = $authorOrId;
		if( is_int( $authorOrId ) )
			$author	= $this->getAuthor( $authorOrId );
		else if( !is_object( $author ) )
			throw new InvalidArgumentException( 'Given author data is invalid' );
		$name	= $author->lastname;
		if( $author->firstname )
			$name	= $author->firstname." ".$name;
		$uri	= 'catalog/author/'.$author->authorId.'-'.$this->getUriPart( $name );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@todo		 clean up
	 */
	public function getCategories( $conditions = [], $orders = [] )
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
	 *	@todo		 code doc
	 */
	public function getCategoriesOfArticle( $articleId )
	{
		$this->checkArticleId( $articleId, TRUE );
		$list			= [];
		$categoryIds	= [];
		$relations		= $this->modelArticleCategory->getAllByIndex( 'articleId', $articleId );
		foreach( $relations as $relation ){
			$category	= $this->modelCategory->get( $relation->categoryId );
			if( $category->parentId )
				$category->parent	= $this->modelCategory->get( $category->parentId );
			$category->volume	= $relation->volume;
			$list[$category->categoryId]		= $category;
		}
		return $list;
	}

	/**
	 *	@todo		 code doc
	 */
	public function getCategory( string $categoryId )
	{
		if( NULL !== ( $data = $this->cache->get( 'catalog.category.'.$categoryId ) ) )
			return $data;
		$this->checkCategoryId( $categoryId, TRUE );
		$data	= $this->modelCategory->get( $categoryId );
		$this->cache->set( 'catalog.category.'.$categoryId, $data );
		return $data;
	}

	/**
	 *	@todo		 clean up
	 *	@todo		 use cache if possible
	 *	@todo		 code doc
	 */
	public function getCategoryArticles( $category, $orders = [], $limits = [] )
	{
#		$cacheKey	= md5( json_encode( [$category->categoryId, $orders, $limits] ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.category.articles.'.$cacheKey ) ) )
#			return $data;
		$conditions	= ['categoryId' => $category->categoryId];
		$relations	= $this->modelArticleCategory->getAll( $conditions, $orders, $limits );
		$articles	= [];
		$volumes	= [];

		foreach( $relations as $relation ){
			$article			= $this->getArticle( $relation->articleId );
			$article->volume	= $relation->volume;
			$articles[]			= $article;
		}
#		$this->cache->set( 'catalog.category.articles.'.$cacheKey, $articles );
		return $articles;
	}

	/**
	 *	@todo		 code doc
	 */
	public function getCategoryOfArticle( $article )
	{
		$relation	= $this->modelArticleCategory->getByIndex( 'articleId', $article->articleId );
		$category			= $this->modelCategory->get( $relation->categoryId );
		$category			= $this->getCategory( $relation->categoryId );							//  @todo use this line for caching and remove line above
		$category->volume	= $relation->volume;
		$article->volume	= $relation->volume;
		return $category;
	}

	public function getDocuments( $conditions = [], $orders = [], $limits = [] )
	{
		return $this->modelArticleDocument->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		 code doc
	 */
	public function getDocumentsOfArticle( $articleId )
	{
		return $this->modelArticleDocument->getAllByIndex( 'articleId', $articleId );
	}

	public function getTags( $conditions = [], $orders = [], $limits = [] )
	{
		return $this->modelArticleTag->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		 code doc
	 *	@todo		 use cache by storing tags in article cache file
	 */
	public function getTagsOfArticle( $articleId, $sort = FALSE )
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
	 *	@todo		 code doc
	 */
	public function getUriPart( $label, $delimiter = "_" )
	{
		$label	= str_replace( ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'], $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label	= preg_replace( "/ +/", $delimiter, $label );
		return $label;
	}

	/**
	 *	Removes article with cover, documents, tags and relations to authors and categories.
	 *	Caches will be removed.
	 *	@todo		 code doc
	 */
	public function removeArticle( $articleId )
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
	 *	@todo		 check if this method is used or deprecated
	 *	@todo		 use cache if possible
	 *	@todo		 code doc
	 */
	public function removeArticleCover( $articleId )
	{
		$article	= $this->getArticle( $articleId );
		$id			= str_pad( $articleId, 5, 0, STR_PAD_LEFT );
		if( $article->cover ){
			@unlink( $this->pathArticleCovers.$id."__".$article->cover );
			@unlink( $this->pathArticleCovers.$id."_".$article->cover );
			$this->clearCacheForArticle( $articleId );
			$this->editArticle( $articleId, ['cover' => NULL] );
		}
	}

	/**
	 *	@todo		 use cache if possible
	 *	@todo		 code doc
	 */
	public function removeArticleDocument( $documentId )
	{
		$document	= $this->modelArticleDocument->get( $documentId );
		$id			= str_pad( $document->articleId, 5, 0, STR_PAD_LEFT );
		@unlink( $this->pathArticleDocuments.$id."_".$document->url );
		$this->clearCacheForArticle( $document->articleId );
		$this->cache->delete( 'catalog.tinymce.links.documents' );
		return $this->modelArticleDocument->remove( $documentId );
	}

	/**
	 *	@todo		 check if this method is used or deprecated
	 *	@todo		 use cache if possible
	 *	@todo		 code doc
	 */
	public function removeArticleFromCategory( $articleId, $categoryId )
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
	 *	@todo		 use cache if possible
	 *	@todo		 code doc
	 */
	public function removeArticleTag( $articleTagId )
	{
		$relation	= $this->modelArticleTag->get( $articleTagId );
		if( $relation ){
			$this->clearCacheForArticle( $relation->articleId );
			return $this->modelArticleTag->remove( $articleTagId );
		}
	}

	/**
	 *	@todo		 code doc
	 */
	public function removeAuthor( $authorId )
	{
		$this->checkAuthorId( $authorId );
		$articles	= $this->getArticlesFromAuthorIds( [$authorId] );
		foreach( $articles as $article )
			$this->removeAuthorFromArticle( $article->articleId, $authorId );
		$this->modelAuthor->remove( $authorId );
		$this->clearCacheForAuthor( $authorId );													//
	}

	/**
	 *	@todo		 code doc
	 */
	public function removeAuthorFromArticle( $articleId, $authorId )
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
	 *	@todo		 check if this method is used or deprecated
	 *	@todo		 use cache if possible
	 *	@todo		 code doc
	 */
	public function removeAuthorImage( $authorId )
	{
		$author		= $this->getAuthor( $authorId );
		$id			= str_pad( $authorId, 5, 0, STR_PAD_LEFT );
		if( $author->image ){
			@unlink( $this->pathAuthorImages.$id."__".$author->image );
			@unlink( $this->pathAuthorImages.$id."_".$author->image );
			$this->editAuthor( $authorId, ['image' => NULL] );
		}
		$this->clearCacheForAuthor( $authorId );													//
	}

	/**
	 *	@todo		 code doc
	 */
	public function removeCategory( $categoryId )
	{
		$this->checkCategoryId( $categoryId );
		if( $this->countArticlesInCategory( $categoryId, TRUE ) )
			throw new RuntimeException( 'Category not empty' );
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelCategory->remove( $categoryId );
	}

	/**
	 *	@todo		 code doc
	 */
	public function removeCategoryFromArticle( $articleId, $categoryId )
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
	 *	@todo		 check if this method is used or deprecated
	 *	@todo		 use cache if possible
	 *	@todo		 code doc
	 */
	public function setArticleAuthorRole( $articleId, $authorId, $role )
	{
		$this->checkArticleId( $articleId );
		$this->checkAuthorId( $authorId );
		$indices	= ['articleId' => $articleId, 'authorId' => $authorId];
		$relation	= $this->modelArticleAuthor->getByIndices( $indices );
		if( $relation ){
			$this->modelArticleAuthor->edit( $relation->articleAuthorId, ['editor' => (int) $role] );
			$this->clearCacheForArticle( $articleId );
			$this->clearCacheForAuthor( $authorId );
		}
	}

	/**
	 *	@todo		 code doc
	 */
	protected function __onInit(): void
	{
		$this->env->getRuntime()->reach( 'Logic_Catalog::init start' );
		$this->config				= $this->env->getConfig();
		$this->frontend				= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig			= $this->config->getAll( 'module.manage_catalog.', TRUE );
		$this->cache				= $this->env->getCache();
		$this->modelArticle			= new Model_Catalog_Article( $this->env );
		$this->modelArticleAuthor	= new Model_Catalog_Article_Author( $this->env );
		$this->modelArticleCategory	= new Model_Catalog_Article_Category( $this->env );
		$this->modelArticleDocument	= new Model_Catalog_Article_Document( $this->env );
#		$this->modelArticleReview	= new Model_Catalog_Article_Review( $this->env );
		$this->modelArticleTag		= new Model_Catalog_Article_Tag( $this->env );
		$this->modelAuthor			= new Model_Catalog_Author( $this->env );
		$this->modelCategory		= new Model_Catalog_Category( $this->env );
#		$this->modelReview			= new Model_Catalog_Review( $this->env );

		$basePath					= $this->frontend->getPath( 'contents' );
		$this->pathArticleCovers	= $basePath.$this->moduleConfig->get( 'path.covers' );
		$this->pathArticleDocuments	= $basePath.$this->moduleConfig->get( 'path.documents' );
		$this->pathAuthorImages		= $basePath.$this->moduleConfig->get( 'path.authors' );

		$cacheKey	= 'catalog.count.categories.articles';
		if( NULL === ( $this->countArticlesInCategories = $this->cache->get( $cacheKey ) ) ){
			$list	= [];
			foreach( $this->getCategories() as $category )
				$list[$category->categoryId]	= $this->countArticlesInCategory( $category->categoryId, TRUE );
			$this->cache->set( $cacheKey, $this->countArticlesInCategories = $list );
		}
		$this->env->getRuntime()->reach( 'Logic_Catalog::init done' );
	}

	/**
	 *	Removes cache files related to article after changes.
	 *	Uses clearCacheForCategory to invalidate category cache.
	 *	Attention: MUST NO call clearCacheForAuthor.
	 *	@access		public
	 *	@param		integer		$articleId			ID of article to clear cache files for
	 *	@return		void
	 */
	protected function clearCacheForArticle( $articleId )
	{
		$article	= $this->modelArticle->get( $articleId );										//  get article
		$this->cache->delete( 'catalog.article.'.$articleId );										//  remove article cache
		$this->cache->delete( 'catalog.article.author.'.$articleId );								//  remove article author cache
		$categories	= $this->modelArticleCategory->getAllByIndex( 'articleId', $articleId );		//  get related categories of article
		foreach( $categories as $category ){														//  iterate assigned categories
			$categoryId	= $category->categoryId;													//  get category ID of related category
			$this->clearCacheForCategory( $categoryId );
		}
		$this->cache->delete( 'catalog.tinymce.images.articles' );
		$this->cache->delete( 'catalog.tinymce.links.articles' );
	}

	/**
	 *	Removes cache files related to article after changes.
	 *	Uses clearCacheForArticle to invalidate article cache.
	 *	@access		public
	 *	@param		integer		$authorId			ID of author
	 *	@return		void
	 */
	protected function clearCacheForAuthor( $authorId )
	{
		$relations	= $this->modelArticleAuthor->getAllByIndex( 'authorId', $authorId );			//  get all articles of author
		foreach( $relations as $relation ){															//  iterate article relations
			$this->clearCacheForArticle( $relation->articleId );									//  clear article cache
		}
		$this->cache->delete( 'catalog.search.authors' );
		$this->cache->delete( 'catalog.tinymce.images.authors' );
		$this->cache->delete( 'catalog.tinymce.links.authors' );
	}

	/**
	 *	Removes cache files related to categories after changes.
	 *	Attention: MUST NO call clearCacheForArticle.
	 *	@access		public
	 *	@param		integer		$categoryId			ID of category
	 *	@return		void
	 */
	protected function clearCacheForCategory( $categoryId )
	{
		while( $categoryId ){																		//  loop while category ID exists
			$category	= $this->modelCategory->get( $categoryId );									//  get category of category ID
			if( $category ){																		//  category exists
				$this->cache->delete( 'catalog.category.'.$categoryId );							//  remove category cache
				$this->cache->delete( 'catalog.html.categoryArticleList.'.$categoryId );			//  remove category view cache
				$categoryId	= (int) $category->parentId;											//  category parent ID is category ID for next loop
			}
			else																					//  category is not existing
				$categoryId	= 0;																	//  no further loops
		}
		$this->cache->delete( 'catalog.categories' );
		$this->cache->delete( 'catalog.tinymce.links.categories' );
		$this->cache->delete( 'catalog.count.categories.articles' );
	}
}
