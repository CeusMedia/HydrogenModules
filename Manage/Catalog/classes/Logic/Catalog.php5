<?php
/**
 *	@todo	extract classes Logic_Upload and Alg_UnitParser
 */
class Logic_Catalog extends CMF_Hydrogen_Environment_Resource_Logic{

	/**	@var	CMM_SEA_Adapter_Abstract			$cache */
	protected $cache;

	/**	@var	Model_Catalog_Article				$modelArticle */
	protected $modelArticle;

	/**	@var	Model_Catalog_Article_Author		$modelArticleAuthor */
	protected $modelArticleAuthor;

	/**	@var	Model_Catalog_Article_Category		$modelArticleCategory */
	protected $modelArticleCategory;

	/**	@var	Model_Catalog_Article_Document		$modelArticleDocument */
	protected $modelArticleDocument;

	/**	@var	Model_Catalog_Article_Tag			$modelArticleTag */
	protected $modelArticleTag;

	/**	@var	Model_Catalog_Article_Category		$modelAuthor */
	protected $modelAuthor;

	/**	@var	Model_Catalog_Category				$modelCategory */
	protected $modelCategory;

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
		$this->env->clock->profiler->tick( 'Logic_Catalog::init start' );
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
		$this->config				= $this->env->getConfig();
		$this->moduleConfig			= $this->config->getAll( 'module.manage_catalog.', TRUE );

		$paths						= $this->moduleConfig->getAll( 'path.', TRUE );
		$basePath					= $paths->get( 'frontend' );
		$this->pathArticleCovers	= $basePath.$paths->get( 'frontend.covers' );
		$this->pathArticleDocuments	= $basePath.$paths->get( 'frontend.documents' );
		$this->pathAuthorImages		= $basePath.$paths->get( 'frontend.authors' );
//		$this->clean();

		$cacheKey	= 'catalog.count.categories.articles';
		if( NULL === ( $this->countArticlesInCategories = $this->cache->get( $cacheKey ) ) ){
			$list	= array();
			foreach( $this->getCategories() as $category )
				$list[$category->categoryId]	= $this->countArticlesInCategory( $category->categoryId, TRUE );
			$this->cache->set( $cacheKey, $this->countArticlesInCategories = $list );
		}
		$this->env->clock->profiler->tick( 'Logic_Catalog::init done' );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addArticle( $data ){
		$data['createdAt']	= time();
		$articleId	= $this->modelArticle->add( $data );
		$this->cache->remove( 'catalog.tinymce.images.articles' );
		$this->cache->remove( 'catalog.tinymce.links.articles' );
		return $articleId;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addArticleCover( $articleId, $file ){
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
		$creator		= new UI_Image_ThumbnailCreator( $uriSource, $uriSource );
		$creator->thumbizeByLimit( $imageWidth, $imageHeight, $imageQuality );

		/*  --  CREATE THUMBNAIL IMAGE  --  */
		$uriThumb		= $this->pathArticleCovers.$id."__".$imagename;
		$thumbWidth		= $this->moduleConfig->get( 'article.image.thumb.maxWidth' );
		$thumbHeight	= $this->moduleConfig->get( 'article.image.thumb.maxHeight' );
		$thumbQuality	= $this->moduleConfig->get( 'article.image.thumb.quality' );
		$creator		= new UI_Image_ThumbnailCreator( $uriSource, $uriThumb );
		$creator->thumbizeByLimit( $thumbWidth, $thumbHeight, $thumbQuality );

		$this->editArticle( $articleId, array( 'cover' => $imagename ) );
		$this->cache->remove( 'catalog.tinymce.images.articles' );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addArticleDocument( $articleId, $file, $title ){
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

		$data	= array(
			'articleId'	=> $articleId,
			'type'			=> $extension,
			'url'			=> $filename,
			'title'			=> $title,
		);
//		$this->clearCacheForArticle( $articleId, FALSE );											//  @todo kriss: active after second argument is implemented
		$this->cache->remove( 'catalog.tinymce.links.documents' );
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
//		$this->clearCacheForArticle( $articleIdId, FALSE );											//  @todo kriss: active after second argument is implemented
		return $this->modelArticleTag->add( $data );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addAuthor( $data ){
//		$data['createdAt']	= time();
		$this->cache->remove( 'catalog.tinymce.links.authors' );
//		$this->cache->remove( 'catalog.tinymce.images.authors' );
		return  $this->modelAuthor->add( $data );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addAuthorImage( $authorId, $file ){
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
		$creator		= new UI_Image_ThumbnailCreator( $uriSource, $uriSource );
		$creator->thumbizeByLimit( $imageWidth, $imageHeight, $imageQuality );
		$this->editAuthor( $authorId, array( 'image' => $imagename ) );
		$this->cache->remove( 'catalog.tinymce.images.authors' );
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
//		$this->clearCacheForArticle( $categoryId );										//  @todo kriss: active after next line is activated
//		$this->clearCacheForAuthor( $authorId );										//  @todo kriss: active after method is implemented
		$this->cache->remove( 'catalog.article.'.$articleId );
		$this->cache->remove( 'catalog.article.author.'.$articleId );
		return $relationId;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function addCategory( $data ){
//		$data['registeredAt']	= time();
		$this->cache->remove( 'catalog.tinymce.links.categories' );
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
//		$this->clearCacheForArticle( $articleId );										//  @todo kriss: active after next line is activated
//		$this->clearCacheForCategory( $categoryId );									//  @todo kriss: active after method is implemented
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
	 *	Removed invalid relations between articles and categories.
	 *	@todo		kriss: point out what this method is for and when it should be used, make not in method description
	 *	@todo		kriss: rename method to clearInvalidRelationsOfArticlesAndCategories
	 *	@todo		kriss: handle cache invalidation for articles and categories
	 */
	protected function clean(){
		$list		= array();
		$articles	= $this->modelArticle->getAll();
		foreach( $articles as $article )
			$ids[]	= $article->articleId;
		$relations	= $this->modelArticleCategory->getAll();
		foreach( $relations as $relation )
			if( !in_array( $relation->articleId, $ids ) )
				$this->modelArticleCategory->remove( $relation->articleCategoryId );
	}

	/**
	 *	Removes cache files related to article after changes.
	 *	@access		public
	 *	@param		integer		$articleId			ID of article to clear cache files for
	 *	@return		void
	 */
	protected function clearCacheForArticle( $articleId ){
		$article	= $this->modelArticle->get( $articleId );											//  get article
		$this->cache->remove( 'catalog.article.'.$articleId );											//  remove article cache
		$categories	= $this->modelArticleCategory->getAllByIndex( 'articleId', $articleId );			//  get related categories of article
		foreach( $categories as $category ){															//  iterate assigned categories
			$categoryId	= $category->categoryId;														//  get category ID of related category
			while( $categoryId ){																		//  loop while category ID exists
				$category	= $this->modelCategory->get( $categoryId );									//  get category of category ID
				if( $category ){																		//  category exists
					$this->cache->remove( 'catalog.category.'.$categoryId );							//  remove category cache
					$this->cache->remove( 'catalog.html.categoryArticleList.'.$categoryId );			//  remove category view cache
					$categoryId	= (int) $category->parentId;											//  category parent ID is category ID for next loop
				}
				else																					//  category is not existing
					$categoryId	= 0;																	//  no further loops
			}
		}
	}

	/**
	 *	@todo		!implement
	 */
	protected function clearCacheForAuthor( $authorId ){
		throw new RuntimeException( 'Not implemented yet' );
	}

	/**
	 *	@todo		!implement
	 */
	protected function clearCacheForCategory( $categoryId ){
		throw new RuntimeException( 'Not implemented yet' );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function countArticles( $conditions = array() ){
		return $this->modelArticle->count( $conditions );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function countArticlesInCategory( $categoryId, $recursive = FALSE ){
		$numbers	= $this->countArticlesInCategories;
		if( isset( $numbers[$categoryId] ) )
			return $numbers[$categoryId];
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
		$this->cache->remove( 'catalog.tinymce.images.articles' );
		$this->cache->remove( 'catalog.tinymce.links.articles' );
//		$this->cache->remove( 'catalog.article.'.$articleId );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function editAuthor( $authorId, $data ){
		$this->checkAuthorId( $authorId, TRUE );
//		$data['modifiedAt']	= time();													//  @todo kriss: why is this line disabled?
//		$this->clearCacheForAuthor( $authorId );										//  @todo kriss: active after method is implemented
		$this->modelAuthor->edit( $authorId, $data );
		$this->cache->remove( 'catalog.article.author.'.$authorId );
		$this->cache->remove( 'catalog.tinymce.images.authors' );
		$this->cache->remove( 'catalog.tinymce.links.authors' );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function editCategory( $categoryId, $data ){
		$this->checkCategoryId( $categoryId, TRUE );
		$old	= $this->modelCategory->get( $categoryId );
//		$data['modifiedAt']	= time();													//  @todo kriss: why is this line disabled?
//		$this->clearCacheForCategory( $categoryId );									//  @todo kriss: active after method is implemented
		$this->modelCategory->edit( $categoryId, $data );
		$new	= $this->modelCategory->get( $categoryId );
		$this->cache->remove( 'catalog.category.'.$categoryId );
		$this->cache->remove( 'catalog.categories' );
		$this->cache->remove( 'catalog.categories.'.$old->parentId );
		$this->cache->remove( 'catalog.categories.'.$new->parentId );
		$this->cache->remove( 'catalog.tinymce.links.categories' );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticle( $articleId ){
		if( NULL !== ( $data = $this->cache->get( 'catalog.article.'.$articleId ) ) )
			return $data;
		$this->checkArticleId( $articleId, TRUE );
		$data	= $this->modelArticle->get( $articleId );
		$this->cache->set( 'catalog.article.'.$articleId, $data );
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
		$model		= new Model_Catalog_Article_Author( $this->env );
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
	 *	Alias for self::getCategoryArticles()
	 */
/*	public function getArticlesFromCategory( $categoryId ){
		return $this->getCategoryArticles( $categoryId );
	}*/

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
		if( NULL !== ( $data = $this->cache->get( 'catalog.article.author.'.$articleId ) ) )
			return $data;
		$data	= $this->modelArticleAuthor->getAllByIndex( 'articleId', $articleId );
		$list	= array();
		foreach( $data as $entry ){
			$author	= $this->modelAuthor->get( $entry->authorId );
			$author->editor	= $entry->editor;
			$list[$author->lastname]	= $author;
		}
//		ksort( $list );
		$this->cache->set( 'catalog.article.author.'.$articleId, $list );
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
		$uri	= 'catalog/author/'.$author->authorId.'-'.$this->getUriPart( $name );
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
		if( NULL !== ( $data = $this->cache->get( 'catalog.category.'.$categoryId ) ) )
			return $data;
		$this->checkCategoryId( $categoryId, TRUE );
		$data	= $this->modelCategory->get( $categoryId );
		$this->cache->set( 'catalog.category.'.$categoryId, $data );
		return $data;
	}

	/**
	 *	@todo		kriss: clean up
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function getCategoryArticles( $category, $orders = array(), $limits = array() ){
#		$cacheKey	= md5( json_encode( array( $category->categoryId, $orders, $limits ) ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.category.articles.'.$cacheKey ) ) )
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
#		$this->cache->set( 'catalog.category.articles.'.$cacheKey, $articles );
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

/*  -------------------------------------------------  */

	/**
	 *	Indicates whether an Article is to be releases in future.
	 *	@access		public
	 *	@param		int			$articleId			ID of Article
	 *	@return		void
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function isFuture( $articleId )
	{
		$tc		= new Alg_Time_Converter;
		$model	= new Model_Article( $articleId );
		$data	= $model->getData( true );
		if( strpos( $data['publication'], "." ) )
			$time	= $tc->convertToTimestamp( $data['publication'], 'date' );
		else
			$time	= $tc->convertToTimestamp( $data['publication'], 'year' );
		$future	= $time > time();
		return $future;
	}
/*  -------------------------------------------------  */

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 *	@todo		kriss: implement and clean up
	 */
	/**
	 *	@todo	Implement!
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
		$this->modelArticle->remove( $articleId );
		$this->cache->remove( 'catalog.tinymce.images.articles' );
		$this->cache->remove( 'catalog.tinymce.links.articles' );
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeArticleCover( $articleId ){
		$article	= $this->getArticle( $articleId );
		$id			= str_pad( $articleId, 5, 0, STR_PAD_LEFT );
		if( $article->cover ){
			@unlink( $this->pathArticleCovers.$id."__".$article->cover );
			@unlink( $this->pathArticleCovers.$id."_".$article->cover );
			$this->editArticle( $articleId, array( 'cover' => NULL ) );
			$this->cache->remove( 'catalog.tinymce.images.articles' );
		}
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeArticleDocument( $documentId ){
		$document	= $this->modelArticleDocument->get( $documentId );
		$id			= str_pad( $document->articleId, 5, 0, STR_PAD_LEFT );
		@unlink( $this->pathArticleDocuments.$id."_".$document->url );
		$this->cache->remove( 'catalog.tinymce.links.documents' );
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
//		$this->clearCacheForArticle( $articleId );										//  @todo kriss: active after next line has been activated
//		$this->clearCacheForCategory( $categoryId );									//  @todo kriss: active after method is implemented
		return $this->modelArticleCategory->removeByIndices( $indices );
	}

	/**
	 *	@deprecated		use removeArticeCover instead
	 */
	public function removeArticleImage( $articleId ){
		throw new Exception( 'Deprecated: use removeArticeCover instead' );
		$article	= $this->modelArticle->get( $articleId );
		$id			= str_pad( $articleId, 5, 0, STR_PAD_LEFT );
		@unlink( $this->pathArticleCovers.$id."_".$article->cover );
		return $this->modelArticle->edit( $articleId, array( 'cover' => NULL ) );
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeArticleTag( $articleTagId ){
		return $this->modelArticleTag->remove( $articleTagId );
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
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
//		$this->clearCacheForArticle( $articleId );										//  @todo kriss: active and remove next line
		$this->cache->remove( 'catalog.article.'.$articleId );
//		$this->clearCacheForAuthor( $authorId );										//  @todo kriss: active after method is implemented
		$this->cache->remove( 'catalog.article.author.'.$articleId );
		return $result;
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeAuthorImage( $authorId ){
		$author		= $this->getAuthor( $authorId );
		$id			= str_pad( $authorId, 5, 0, STR_PAD_LEFT );
		if( $author->image ){
			@unlink( $this->pathAuthorImages.$id."__".$author->image );
			@unlink( $this->pathAuthorImages.$id."_".$author->image );
			$this->editAuthor( $authorId, array( 'image' => NULL ) );
		}
//		$this->clearCacheForAuthor( $authorId );										//  @todo kriss: active after method is implemented
		$this->cache->remove( 'catalog.tinymce.images.authors' );
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeCategory( $categoryId ){
		$this->checkCategoryId( $categoryId );
		if( $this->countArticlesInCategory( $categoryId, TRUE ) )
			throw new RuntimeException( 'Category not empty' );
//		$this->clearCacheForCategory( $categoryId );									//  @todo kriss: active after method is implemented
		$this->cache->remove( 'catalog.tinymce.links.categories' );
		return $this->modelCategory->remove( $categoryId );
	}

	/**
	 *	@todo		kriss: check if this method is used or deprecated
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function removeCategoryFromArticle( $articleId, $categoryId ){
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= array(
			'articleId'	=> $articleId,
			'categoryId'	=> $categoryId,
		);
//		$this->clearCacheForCategory( $categoryId );									//  @todo kriss: active after method is implemented
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
		if( $relation )
			$this->modelArticleAuthor->edit( $relation->articleAuthorId, array( 'editor' => (int) $role ) );
	}

	/*  --  DEPRECATED METHOD  --  */

	/**
	 *	@deprecated	not used anymore, will be removed
	 *	@todo		kriss: remove method
	 */
	public function getFullCategoryName( $categoryId, $language = "de" ){
		throw new RuntimeException( 'Catalog logic method "getFullCategoryName" is deprecated.' );
		$data	= $this->modelCategory->get( $categoryId );
		$name	= $data->{"label_".$language};
		if( $data->parentId ){
			$parent	= $this->modelCategory->get( $data->parentId );
			$name	= $parent->{"label_".$language}." -> ".$name;
		}
		return $name;
	}

	/**
	 *	Indicates whether an Author is Editor of an Article.
	 *	@access		public
	 *	@param		int			$articleId			ID of Article
	 *	@param		int			$authorId			ID of Author
	 *	@return		bool
	 *	@deprecated	not used anymore, will be removed
	 *	@todo		kriss: remove method
	 */
	public function isArticleEditor( $articleId, $authorId )
	{
		throw new RuntimeException( 'Catalog logic method "isArticleEditor" is deprecated.' );
		$model	= new Model_ArticleAuthor();
		$model->focusForeign( "articleId", $articleId );
		$model->focusForeign( "authorId", $authorId );
		$data	= $model->getData( true );
		if( $data )
			return $data['editor'];
		return null;
	}

	/**
	 *	Indicates whether an Author is related to an Article.
	 *	@access		public
	 *	@param		int			$articleId			ID of Article
	 *	@param		int			$authorId			ID of Author
	 *	@return		bool
	 *	@deprecated	not used anymore, will be removed
	 *	@todo		kriss: remove method
	 */
	public function isAuthorOfArticle( $articleId, $authorId  )
	{
		throw new RuntimeException( 'Catalog logic method "isAuthorOfArticle" is deprecated.' );
		$model	= new Model_ArticleAuthor();
		$model->focusForeign( "articleId", $articleId );
		$model->focusForeign( "authorId", $authorId );
		$data	= $model->getData();
		return (bool)count( $data );
	}

	/**
	 *	Indicates whether an Author is related to an Article.
	 *	@access		public
	 *	@param		int			$articleId			ID of Article
	 *	@param		int			$authorId			ID of Author
	 *	@return		bool
	 *	@deprecated	not used anymore, will be removed
	 *	@todo		kriss: remove method
	 */
	public function isCategoryOfArticle( $articleId, $categoryId  )
	{
		throw new RuntimeException( 'Catalog logic method "isCategoryOfArticle" is deprecated.' );
		$model	= new Model_ArticleCategory();
		$model->focusForeign( "articleId", $articleId );
		$model->focusForeign( "categoryId", $categoryId );
		$data	= $model->getData();
		return (bool)count( $data );
	}
}
class Logic_Upload{
	static function getMaxUploadSize( $env, $configKey = NULL, $exceptedUnit = NULL ){
		$limits		= array(
			Alg_UnitParser::parse( ini_get( 'upload_max_filesize' ), "M" ),
			Alg_UnitParser::parse( ini_get( 'post_max_size' ), "M" ),
			Alg_UnitParser::parse( ini_get( 'memory_limit' ), "M" ),
		);
		if( $configKey ){
			$configValue	= trim( $env->getConfig()->get( $configKey ) );
			if( strlen( $configValue ) )
				$limits[]	= Alg_UnitParser::parse( $configValue, $exceptedUnit );
		}
		return min( $limits );
	}

	static function getTypes( $env, $configKey ){
		$configValue	= trim( $env->getConfig()->get( $configKey ) );
		if( !strlen( $configValue ) )
			throw new DomainException( 'No value for config key "'.$configKey.'" set' );
		$list	= array();
		foreach( explode( ",", $configValue ) as $extension )
			$list[]	= trim( $extension );
		return $list;
	}
}

class Alg_UnitParser{

	static public $rules	= array(
		'/^([0-9.,]+)$/'	=> 1,
		'/^([0-9.,]+)k$/'	=> 1000,
		'/^([0-9.,]+)kB$/'	=> 1000,
		'/^([0-9.,]+)K$/'	=> 1024,
		'/^([0-9.,]+)KB$/i'	=> 1024,
		'/^([0-9.,]+)m$/'	=> 1000000,
		'/^([0-9.,]+)M$/'	=> 1048576,
		'/^([0-9.,]+)MB$/i'	=> 1048576,
	);

	static public function parse( $string, $exceptedUnit = NULL ){
		$int	= (int) $string;
		if( $exceptedUnit && strlen( $int ) == strlen( $string ) && $int == $string )
			$string	.= $exceptedUnit;
		$string	= trim( $string );
		$factor	= NULL;
		foreach( self::$rules as $key => $value ){
			if( preg_match( $key, $string ) ){
				$string		= (float) preg_replace( $key, '\\1', $string );
				$factor		= $value;
				break;
			}
		}
		if( $factor !== NULL )																		//
			return $factor * $string;
		throw new DomainException( 'Given string is not matching any parser rules' );
	}
}
?>
