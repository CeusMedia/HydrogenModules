<?php
/**
 *	@todo	extract classes Logic_Upload and Alg_UnitParser
 */
class Logic_Catalog_Bookstore extends CMF_Hydrogen_Environment_Resource_Logic{

	/**	@var	CMM_SEA_Adapter_Abstract					$cache */
	protected $cache;

	/**	@var	Logic_Frontend								$frontend */
	protected $frontend;

	/**	@var	Model_Catalog_Bookstore_Article				$modelArticle */
	protected $modelArticle;

	/**	@var	Model_Catalog_Bookstore_Article_Author		$modelArticleAuthor */
	protected $modelArticleAuthor;

	/**	@var	Model_Catalog_Bookstore_Article_Category	$modelArticleCategory */
	protected $modelArticleCategory;

	/**	@var	Model_Catalog_Bookstore_Article_Document	$modelArticleDocument */
	protected $modelArticleDocument;

	/**	@var	Model_Catalog_Bookstore_Article_Tag			$modelArticleTag */
	protected $modelArticleTag;

	/**	@var	Model_Catalog_Bookstore_Article_Category	$modelAuthor */
	protected $modelAuthor;

	/**	@var	Model_Catalog_Bookstore_Category			$modelCategory */
	protected $modelCategory;

	/**	@var	Alg_List_Dictionary							$moduleConfig */
	protected $moduleConfig;

	protected $countArticlesInCategories;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment
	 *	@param		mixed		$a		Test argument
	 *	@return		void
	 */
	public function  __construct( CMF_Hydrogen_Environment_Abstract $env, $a = NULL ) {
		parent::__construct( $env, $a );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	protected function __onInit( $a = NULL ){
		$this->env->clock->profiler->tick( 'Logic_Catalog_Bookstore::init start' );
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
		if( NULL === ( $this->countArticlesInCategories = $this->cache->get( $cacheKey ) ) ){
			$list	= array();
			foreach( $this->getCategories() as $category )
				$list[$category->categoryId]	= $this->countArticlesInCategory( $category->categoryId, TRUE );
			$this->cache->set( $cacheKey, $this->countArticlesInCategories = $list );
		}
		$this->env->clock->profiler->tick( 'Logic_Catalog_Bookstore::init done' );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addArticle( $data ){
		$data['createdAt']	= time();
		$articleId	= $this->modelArticle->add( $data );
		$this->cache->remove( 'catalog.bookstore.tinymce.images.articles' );
		$this->cache->remove( 'catalog.bookstore.tinymce.links.articles' );
		return $articleId;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addArticleDocument( $articleId, $sourceFile, $title, $mimeType ){
		if( !file_exists( $sourceFile ) )
			throw new RuntimeException( 'File is not existing' );
		if( !is_readable( $sourceFile ) )
			throw new RuntimeException( 'File is not readable' );

		$logicBucket	= new Logic_FileBucket( $this->env );
		$options		= $this->moduleConfig->getAll( 'article.document.', TRUE );
		$extension		= pathinfo( $sourceFile, PATHINFO_EXTENSION );
		$article		= $this->getArticle( $articleId );
		$fileName		= Logic_Upload::sanitizeFileNameStatic( $article->title.' - '.$title.'.'.$extension );
		$logicBucket->add( $sourceFile, 'bookstore/document/'.$fileName, $mimeType, 'catalog_bookstore' );

		$data	= array(
			'articleId'	=> $articleId,
			'status'	=> 0,
			'type'		=> 0,
			'url'		=> $fileName,
			'title'		=> $title,
		);
		$this->clearCacheForArticle( $articleId );													//
		$this->cache->remove( 'catalog.bookstore.tinymce.links.documents' );
		return $this->modelArticleDocument->add( $data );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addArticleTag( $articleId, $tag ){
		$data	= array(
			'articleId'	=> $articleId,
			'tag'		=> $tag,
		);
		$this->clearCacheForArticle( $articleIdId );												//
		return $this->modelArticleTag->add( $data );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addAuthor( $data ){
//		$data['createdAt']	= time();
		$this->clearCacheForAuthor( 0 );
		return  $this->modelAuthor->add( $data );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addAuthorImage( $authorId, $sourceFile, $mimeType ){
		if( !file_exists( $sourceFile ) )
			throw new RuntimeException( 'File is not existing' );
		if( !is_readable( $sourceFile ) )
			throw new RuntimeException( 'File is not readable' );

		$image			= new UI_Image( $sourceFile );
		$processor		= new UI_Image_Processing( $image );
		$logicBucket	= new Logic_FileBucket( $this->env );
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
		$this->editAuthor( $authorId, array( 'image' => $title ) );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addAuthorToArticle( $articleId, $authorId, $role ){
		$data		= array(
			'articleId'	=> $articleId,
			'authorId'	=> $authorId,
			'editor'	=> $role,
		);
		$relationId	= $this->modelArticleAuthor->add( $data );
		$this->clearCacheForArticle( $categoryId );													//
		$this->clearCacheForAuthor( $authorId );													//
		return $relationId;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addCategory( $data ){
//		$data['registeredAt']	= time();
		$this->clearCacheForCategory( 0 );
		return $this->modelCategory->add( $data );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addCategoryToArticle( $articleId, $categoryId, $volume = NULL ){
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= array(
			'articleId'		=> $articleId,
			'categoryId'	=> $categoryId,
			'volume'		=> $volume,
		);
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelArticleCategory->add( $indices );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function checkArticleId( $articleId, $throwException = FALSE ){
		if( $this->modelArticle->has( (int) $articleId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid article ID '.$articleId );
		return FALSE;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function checkAuthorId( $authorId, $throwException = FALSE ){
		if( $this->modelAuthor->has( (int) $authorId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid author ID '.$authorId );
		return FALSE;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function checkCategoryId( $categoryId, $throwException = FALSE ){
		if( $this->modelCategory->has( (int) $categoryId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid category ID '.$categoryId );
		return FALSE;
	}

	/**
	 *	Removes cache files related to article after changes.
	 *	Uses clearCacheForCategory to invalidate category cache.
	 *	Attention: MUST NO call clearCacheForAuthor.
	 *	@access		public
	 *	@param		integer		$articleId			ID of article to clear cache files for
	 *	@return		void
	 */
	protected function clearCacheForArticle( $articleId ){
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
	}

	/**
	 *	Removes cache files related to article after changes.
	 *	Uses clearCacheForArticle to invalidate article cache.
	 *	@access		public
	 *	@param		integer		$authorId			ID of author
	 *	@return		void
	 */
	protected function clearCacheForAuthor( $authorId ){
		$relations	= $this->modelArticleAuthor->getAllByIndex( 'authorId', $authorId );			//  get all articles of author
		foreach( $relations as $relation ){															//  iterate article relations
			$this->clearCacheForArticle( $relation->articleId );									//  clear article cache
		}
		$this->cache->remove( 'catalog.bookstore.search.authors' );
		$this->cache->remove( 'catalog.bookstore.tinymce.images.authors' );
		$this->cache->remove( 'catalog.bookstore.tinymce.links.authors' );
	}

	/**
	 *	Removes cache files related to categories after changes.
	 *	Attention: MUST NO call clearCacheForArticle.
	 *	@access		public
	 *	@param		integer		$categoryId			ID of category
	 *	@return		void
	 */
	protected function clearCacheForCategory( $categoryId ){
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
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function countArticles( $conditions = array() ){
		return $this->modelArticle->count( $conditions );
	}

	/**
	 *	Returns number of articles within a category or its sub categories, if enabled.
	 *	Uses cache 'catalog.count.categories.articles' in recursive mode.
	 *	@access		public
	 *	@param 		integer		$categoryId		ID of category to count articles for
	 *	@param 		boolean		$recursive		Flag: count in sub categories, default: FALSE
	 *	@return		integer						Number of found articles in category
	 */
	public function countArticlesInCategory( $categoryId, $recursive = FALSE ){
		if( $recursive && isset( $this->countArticlesInCategories[$categoryId] ) )
			return $this->countArticlesInCategories[$categoryId];
		$number		= count( $this->modelArticleCategory->getAllByIndex( 'categoryId', $categoryId ) );
		if( $recursive ){
			$categories	= $this->getCategories( array( 'parentId' => $categoryId ) );
			foreach( $categories as $category )
				$number += $this->countArticlesInCategory( $category->categoryId );
		}
		return $number;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function editArticle( $articleId, $data ){
		$this->checkArticleId( $articleId, TRUE );
//		$data['modifiedAt']	= time();
		$this->modelArticle->edit( $articleId, $data );
		$this->clearCacheForArticle( $articleId, TRUE );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function editAuthor( $authorId, $data ){
		$this->checkAuthorId( $authorId, TRUE );
//		$data['modifiedAt']	= time();																//
		$this->clearCacheForAuthor( $authorId );													//
		$this->modelAuthor->edit( $authorId, $data );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function editCategory( $categoryId, $data ){
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
	 *	@todo		kriss: code doc
	 */
	public function getArticle( $articleId ){
		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.article.'.$articleId ) ) )
			return $data;
		$this->checkArticleId( $articleId, TRUE );
		$data	= $this->modelArticle->get( $articleId );
		$this->cache->set( 'catalog.bookstore.article.'.$articleId, $data );
		return $data;
	}

	/**
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function getArticles( $conditions = array(), $orders = array(), $limits = array() ){
#		$cacheKey	= md5( json_encode( array( $conditions, $orders, $limits ) ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.articles.'.$cacheKey ) ) )
#			return $data;
		$list	= array();
		foreach( $this->modelArticle->getAll( $conditions, $orders, $limits ) as $article )
			$list[$article->articleId]	= $article;
#		$this->cache->set( 'catalog.articles.'.$cacheKey, $list );
		return $list;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticlesFromAuthor( $author, $orders = array(), $limits = array() ){
		$articles	= $this->modelArticleAuthor->getAllByIndex( 'authorId', $author->authorId );
		$articleIds	= array();
		foreach( $articles as $article )
			$articleIds[]	= $article->articleId;
		if( !$articles )
			return array();
		$conditions	= array( 'articleId' => $articleIds );
		$articles	= $this->getArticles( $conditions, $orders, $limits );
		return $articles;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticlesFromAuthorIds( $authorIds, $returnIds = FALSE ){
		$model		= new Model_Catalog_Bookstore_Article_Author( $this->env );
		$articles	= $model->getAll( array( 'authorId' => array_values( $authorIds ) ) );
		if( !$returnIds )
			return $articles;
		$ids	= array();
		foreach( $articles as $article )
			$ids[]	= $article->articleId;
		return $ids;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticlesFromAuthors( $authors, $returnIds = FALSE ){
		$authorIds	= array();
		foreach( $authors as $author )
			$authorIds[]	= $author->authorId;
		return $this->getArticlesFromAuthorIds( $authorIds, $returnIds );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticleUri( $articleOrId ){
		$article	= $articleOrId;
		if( is_int( $articleOrId ) )
			$article	= $this->getArticle( $articleOrId );
		if( !is_object( $article ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		$keywords	= $this->getUriPart( $article->title );
		return './catalog/article/'.$article->articleId.'-'.$keywords;
	}

	/**
	 *	@todo		kriss: use cache
	 */
	public function getAuthor( $authorId ){
		$this->checkAuthorId( $authorId, TRUE );
		return $this->modelAuthor->get( $authorId );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getAuthors( $conditions = array(), $orders = array() ){
		$list	= array();
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
	public function getAuthorsOfArticle( $articleId ){
		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.article.author.'.$articleId ) ) )
			return $data;
		$data	= $this->modelArticleAuthor->getAllByIndex( 'articleId', $articleId );
		$list	= array();
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
	 *	@todo		kriss: code doc
	 */
	public function getAuthorUri( $authorOrId, $absolute = FALSE ){
		$author = $authorOrId;
		if( is_int( $authorOrId ) )
			$author	= $this->getAuthor( $authorOrId, TRUE );
		else if( !is_object( $author ) )
			throw new InvalidArgumentException( 'Given author data is invalid' );
		$name	= $author->lastname;
		if( $author->firstname )
			$name	= $author->firstname." ".$name;
		$uri	= 'catalog/bookstore/author/'.$author->authorId.'-'.$this->getUriPart( $name );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@todo		kriss: clean up
	 */
	public function getCategories( $conditions = array(), $orders = array() ){
#		$cacheKey	= md5( json_encode( array( $conditions, $orders ) ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.categories.'.$cacheKey ) ) )
#			return $data;

		$list	= array();
		foreach( $this->modelCategory->getAll( $conditions, $orders ) as $category )
			$list[$category->categoryId]	= $category;
#		$this->cache->set( 'catalog.categories.'.$cacheKey, $list );
		return $list;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getCategoriesOfArticle( $articleId ){
		$this->checkArticleId( $articleId, TRUE );
		$list			= array();
		$categoryIds	= array();
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
	 *	@todo		kriss: code doc
	 */
	public function getCategory( $categoryId ){
		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.category.'.$categoryId ) ) )
			return $data;
		$this->checkCategoryId( $categoryId, TRUE );
		$data	= $this->modelCategory->get( $categoryId );
		$this->cache->set( 'catalog.bookstore.category.'.$categoryId, $data );
		return $data;
	}

	/**
	 *	@todo		kriss: clean up
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function getCategoryArticles( $category, $orders = array(), $limits = array() ){
#		$cacheKey	= md5( json_encode( array( $category->categoryId, $orders, $limits ) ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.bookstore.category.articles.'.$cacheKey ) ) )
#			return $data;
		$conditions	= array( 'categoryId' => $category->categoryId );
		$relations	= $this->modelArticleCategory->getAll( $conditions, $orders, $limits );
		$articles	= array();
		$volumes	= array();

		foreach( $relations as $relation ){
			$article			= $this->getArticle( $relation->articleId );
			$article->volume	= $relation->volume;
			$articles[]			= $article;
		}
#		$this->cache->set( 'catalog.bookstore.category.articles.'.$cacheKey, $articles );
		return $articles;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getCategoryOfArticle( $article ){
		$relation	= $this->modelArticleCategory->getByIndex( 'articleId', $article->articleId );
		$category			= $this->modelCategory->get( $relation->categoryId );
		$category			= $this->getCategory( $relation->categoryId );							//  @todo use this line for caching and remove line above
		$category->volume	= $relation->volume;
		$article->volume	= $relation->volume;
		return $category;
	}

	public function getDocuments( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelArticleDocument->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getDocumentsOfArticle( $articleId ){
		return $this->modelArticleDocument->getAllByIndex( 'articleId', $articleId );
	}

	public function getTags( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelArticleTag->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@todo		kriss: code doc
	 *	@todo		kriss: use cache by storing tags in article cache file
	 */
	public function getTagsOfArticle( $articleId, $sort = FALSE ){
		$tags	= $this->modelArticleTag->getAllByIndex( 'articleId', $articleId );
		$list	= array();
		foreach( $tags as $tag )
			$list[$tag->tag]	= $tag;
		if( $sort )
			ksort( $list );
		return $list;
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

	/**
	 *	Removes article with cover, documents, tags and relations to authors and categories.
	 *	Caches will be removed.
	 *	@todo		kriss: code doc
	 */
	public function removeArticle( $articleId ){
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
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeArticleCover( $articleId ){
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
		$this->editArticle( $articleId, array( 'cover' => NULL ) );
	}

	/**
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeArticleDocument( $documentId ){
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
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeArticleFromCategory( $articleId, $categoryId ){
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= array(
			'articleId'		=> $articleId,
			'categoryId'	=> $categoryId,
		);
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelArticleCategory->removeByIndices( $indices );
	}

	/**
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeArticleTag( $articleTagId ){
		$relation	= $this->modelArticleTag->get( $articleTagId );
		if( $relation ){
			$this->clearCacheForArticle( $relation->articleId );
			return $this->modelArticleTag->remove( $articleTagId );
		}
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function removeAuthor( $authorId ){
		$this->checkAuthorId( $authorId );
		$articles	= $this->getArticlesFromAuthorIds( array( $authorId ) );
		foreach( $articles as $article )
			$this->removeAuthorFromArticle( $article->articleId, $authorId );
		$this->removeAuthorImage( $authorId );
		$this->modelAuthor->remove( $authorId );
		$this->clearCacheForAuthor( $authorId );													//
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function removeAuthorFromArticle( $articleId, $authorId ){
		$this->checkArticleId( $articleId );
		$this->checkAuthorId( $authorId );
		$indices	= array(
			'articleId'	=> $articleId,
			'authorId'	=> $authorId,
		);
		$result	= $this->modelArticleAuthor->removeByIndices( $indices );
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForAuthor( $authorId );													//
		return $result;
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeAuthorImage( $authorId ){
		$author			= $this->getAuthor( $authorId );
		$logicBucket	= new Logic_FileBucket( $this->env );
		$prefix			= 'bookstore/author/';
		$moduleId		= 'catalog_bookstore';
		if( $file = $logicBucket->getByPath( $prefix.$author->image, $moduleId ) )
			$logicBucket->remove( $file->fileId );
		$this->editAuthor( $authorId, array( 'image' => NULL ) );
		$this->clearCacheForAuthor( $authorId );													//
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function removeCategory( $categoryId ){
		$this->checkCategoryId( $categoryId );
		if( $this->countArticlesInCategory( $categoryId, TRUE ) )
			throw new RuntimeException( 'Category not empty' );
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelCategory->remove( $categoryId );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function removeCategoryFromArticle( $articleId, $categoryId ){
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= array(
			'articleId'	=> $articleId,
			'categoryId'	=> $categoryId,
		);
		$this->clearCacheForArticle( $articleId );													//
		$this->clearCacheForCategory( $categoryId );												//
		return $this->modelArticleCategory->removeByIndices( $indices );
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function setArticleAuthorRole( $articleId, $authorId, $role ){
		$this->checkArticleId( $articleId );
		$this->checkAuthorId( $authorId );
		$indices	= array( 'articleId' => $articleId, 'authorId' => $authorId );
		$relation	= $this->modelArticleAuthor->getByIndices( $indices );
		if( $relation ){
			$this->modelArticleAuthor->edit( $relation->articleAuthorId, array( 'editor' => (int) $role ) );
			$this->clearCacheForArticle( $articleId );
			$this->clearCacheForAuthor( $authorId );
		}
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function setArticleCover( $articleId, $sourceFile, $mimeType ){
		if( !file_exists( $sourceFile ) )
			throw new RuntimeException( 'File is not existing' );
		if( !is_readable( $sourceFile ) )
			throw new RuntimeException( 'File is not readable' );

		$image			= new UI_Image( $sourceFile );
		$processor		= new UI_Image_Processing( $image );
		$logicBucket	= new Logic_FileBucket( $this->env );
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
		$this->editArticle( $articleId, array( 'cover' => $title ) );
		$this->cache->remove( 'catalog.bookstore.tinymce.images.articles' );
	}
}
?>
