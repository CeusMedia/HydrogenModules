<?php
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

	protected function __onInit( $a = NULL ){
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
		$this->pathArticleCovers	= dirname( __FILE__ ).'/../../../Univerlag/contents/articles/covers/'; 
		$this->pathArticleDocuments	= dirname( __FILE__ ).'/../../../Univerlag/contents/articles/documents/';//$this->config['frontend.document.uri'];
		$this->pathAuthorImages		= dirname( __FILE__ ).'/../../../Univerlag/contents/authors/'; 
//		$this->clean();
	}

	public function addArticle( $data ){
		$data['createdAt']	= time();
		return $this->modelArticle->add( $data );
	}

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
		$imageWidth		= 180;//$this->config['frontend.cover.width'];
		$imageHeight	= 240;//$this->config['frontend.cover.height'];
		$imageQuality	= 85;
		$creator		= new UI_Image_ThumbnailCreator( $uriSource, $uriSource );
		$creator->thumbizeByLimit( $imageWidth, $imageHeight, $imageQuality );

		/*  --  CREATE THUMBNAIL IMAGE  --  */
		$uriThumb		= $this->pathArticleCovers.$id."__".$imagename;
		$thumbWidth		= 90;//$this->config['frontend.cover.thumb.width'];
		$thumbHeight	= 120;//$this->config['frontend.cover.thumb.height'];
		$thumbQuality	= 85;
		$creator		= new UI_Image_ThumbnailCreator( $uriSource, $uriThumb );
		$creator->thumbizeByLimit( $thumbWidth, $thumbHeight, $thumbQuality );

		$this->editArticle( $articleId, array( 'cover' => $imagename ) );
	}

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
		return $this->modelArticleDocument->add( $data );
	}

	public function addAuthor( $data ){
//		$data['createdAt']	= time();
		return  $this->modelAuthor->add( $data );
	}

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
		$imageWidth		= 240;//$this->config['frontend.author.width'];
		$imageHeight	= 180;//$this->config['frontend.author.height'];
		$imageQuality	= 80;//
		$creator		= new UI_Image_ThumbnailCreator( $uriSource, $uriSource );
		$creator->thumbizeByLimit( $imageWidth, $imageHeight, $imageQuality );
		$this->editAuthor( $authorId, array( 'image' => $imagename ) );
	}

	public function addAuthorToArticle( $articleId, $authorId, $role ){
		$data		= array(
			'articleId'	=> $articleId,
			'authorId'		=> $authorId,
			'editor'		=> $role,
		);
		return $this->modelArticleAuthor->add( $data );
	}

	public function addCategory( $data ){
//		$data['registeredAt']	= time();
		return $this->modelCategory->add( $data );
	}

	public function addCategoryToArticle( $articleId, $categoryId, $volume = NULL ){
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= array(
			'articleId'	=> $articleId,
			'categoryId'	=> $categoryId,
			'volume'		=> $volume,
		);
		return $this->modelArticleCategory->add( $indices );
	}

	public function checkArticleId( $articleId, $throwException = FALSE ){
		if( $this->modelArticle->has( (int) $articleId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid article ID '.$articleId );
		return FALSE;
	}

	public function checkAuthorId( $authorId, $throwException = FALSE ){
		if( $this->modelAuthor->has( (int) $authorId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid author ID '.$authorId );
		return FALSE;
	}

	public function checkCategoryId( $categoryId, $throwException = FALSE ){
		if( $this->modelCategory->has( (int) $categoryId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid category ID '.$categoryId );
		return FALSE;
	}

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

	public function countArticles( $conditions = array() ){
		return $this->modelArticle->count( $conditions );
	}

	public function countArticlesInCategory( $categoryId, $recursive = FALSE ){
		$number		= count( $this->modelArticleCategory->getAllByIndex( 'categoryId', $categoryId ) );
		if( $recursive ){
			$categories	= $this->getCategories( array( 'parentId' => $categoryId ) );
			foreach( $categories as $category )
				$number += $this->countArticlesInCategory( $category->categoryId );
		}
		return $number;
	}

	public function editArticle( $articleId, $data ){
		$this->checkArticleId( $articleId, TRUE );
//		$data['modifiedAt']	= time();
		$this->modelArticle->edit( $articleId, $data );
	}

	public function editAuthor( $authorId, $data ){
		$this->checkAuthorId( $authorId, TRUE );
//		$data['modifiedAt']	= time();
		$this->modelAuthor->edit( $authorId, $data );
	}

	public function editCategory( $categoryId, $data ){
		$this->checkCategoryId( $categoryId, TRUE );
//		$data['modifiedAt']	= time();
		$this->modelCategory->edit( $categoryId, $data );
	}

	public function getArticle( $articleId ){
		if( ( $data = unserialize( $this->cache->get( 'catalog.article.'.$articleId ) ) ) )
			return $data;
		$this->checkArticleId( $articleId, TRUE );
		$data	= $this->modelArticle->get( $articleId );
		$this->cache->set( 'catalog.article.'.$articleId, serialize( $data ) );
		return $data;
	}

	public function getArticles( $conditions = array(), $orders = array(), $limits = array() ){
		$cacheKey	= md5( json_encode( array( $conditions, $orders, $limits ) ) );
		if( ( $data = unserialize( $this->cache->get( 'catalog.articles.'.$cacheKey ) ) ) )
			return $data;
		$list	= array();
		foreach( $this->modelArticle->getAll( $conditions, $orders, $limits ) as $article )
			$list[$article->articleId]	= $article;
		$this->cache->set( 'catalog.articles.'.$cacheKey, serialize( $list ) );
		return $list;
	}

	public function getArticlesFromAuthor( $author, $orders = array(), $limits = array() ){
		$articles	= $this->modelArticleAuthor->getAllByIndex( 'authorId', $author->authorId );
		$articleIds	= array();
		foreach( $articles as $article )
			$articleIds[]	= $article->articleId;
		$conditions	= array( 'articleId' => $articleIds );
		$articles	= $this->getArticles( $conditions, $orders, $limits );
		return $articles;
	}
	
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

	public function getArticlesFromAuthors( $authors, $returnIds = FALSE ){
		$authorIds	= array();
		foreach( $authors as $author )
			$authorIds[]	= $author->authorId;
		return $this->getArticlesFromAuthorIds( $authorIds, $returnIds );
	}

	public function getAuthor( $authorId ){
		$this->checkAuthorId( $authorId, TRUE );
		return $this->modelAuthor->get( $authorId );
	}

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
		if( ( $data = unserialize( $this->cache->get( 'catalog.article.author.'.$articleId ) ) ) )
			return $data;
		$data	= $this->modelArticleAuthor->getAllByIndex( 'articleId', $articleId );
		$list	= array();
		foreach( $data as $entry ){
			$author	= $this->modelAuthor->get( $entry->authorId );
			$author->editor	= $entry->editor;
			$list[$author->lastname]	= $author;
		}
		ksort( $list );
		$this->cache->set( 'catalog.article.author.'.$articleId, serialize( $list ) );
		return $list;
	}

	public function getCategories( $conditions = array(), $orders = array() ){
		$cacheKey	= md5( json_encode( array( $conditions, $orders ) ) );
		if( ( $data = unserialize( $this->cache->get( 'catalog.categories.'.$cacheKey ) ) ) )
			return $data;

		$list	= array();
		foreach( $this->modelCategory->getAll( $conditions, $orders ) as $category )
			$list[$category->categoryId]	= $category;
		$this->cache->set( 'catalog.categories.'.$cacheKey, serialize( $list ) );
		return $list;
	}

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

	public function getCategory( $categoryId ){
		if( ( $data = unserialize( $this->cache->get( 'catalog.category.'.$categoryId ) ) ) )
			return $data;
		$this->checkCategoryId( $categoryId, TRUE );
		$data	= $this->modelCategory->get( $categoryId );
		$this->cache->set( 'catalog.category.'.$categoryId, serialize( $data ) );
		return $data;
	}

	public function getCategoryArticles( $category, $orders = array(), $limits = array() ){
		$cacheKey	= md5( json_encode( array( $category->categoryId, $orders, $limits ) ) );
		if( ( $data = unserialize( $this->cache->get( 'catalog.category.articles.'.$cacheKey ) ) ) )
			return $data;
		$conditions	= array( 'categoryId' => $category->categoryId );
		$relations	= $this->modelArticleCategory->getAll( $conditions, $orders, $limits );
		$articles	= array();
		$volumes	= array();

		foreach( $relations as $relation ){
			$article			= $this->getArticle( $relation->articleId );
			$article->volume	= $relation->volume;
			$articles[]			= $article;
		}
		$this->cache->set( 'catalog.category.articles.'.$cacheKey, serialize( $articles ) );
		return $articles;
	}
	
	public function getCategoryOfArticle( $article ){
		$relation	= $this->modelArticleCategory->getByIndex( 'articleId', $article->articleId );
		$category			= $this->modelCategory->get( $relation->categoryId );
		$category->volume	= $relation->volume;
		$article->volume	= $relation->volume;
		return $category;
	}

	public function getDocumentsOfArticle( $articleId ){
		return $this->modelArticleDocument->getAllByIndex( 'articleId', $articleId );
	}

	public function getFullCategoryName( $categoryId, $language = "de" ){
		$data	= $this->modelCategory->get( $categoryId );
		$name	= $data['label_'.$language];
		if( $data['parentId'] ){
			$parent	= $this->modelCategory->get( $data['parentId'] );
			$name	= $parent['label_'.$language]." -> ".$name;
		}
		return $name;
	}

	public function getTagsOfArticle( $articleId ){
		$tags	= $this->modelArticleTag->getAllByIndex( 'articleId', $articleId );
		$list	= array();
		foreach( $tags as $tag )
			$list[$tag->articleTagId]	= $tag->tag;
		return $list;
	}

/*  -------------------------------------------------  */
	/**
	 *	Indicates whether an Author is Editor of an Article.
	 *	@access		public
	 *	@param		int			$articleId			ID of Article
	 *	@param		int			$authorId			ID of Author
	 *	@return		bool
	 */
	public function isArticleEditor( $articleId, $authorId )
	{
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
	 */
	public function isAuthorOfArticle( $articleId, $authorId  )
	{
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
	 */
	public function isCategoryOfArticle( $articleId, $categoryId  )
	{
		$model	= new Model_ArticleCategory();
		$model->focusForeign( "articleId", $articleId );
		$model->focusForeign( "categoryId", $categoryId );
		$data	= $model->getData();
		return (bool)count( $data );
	}

	/**
	 *	Indicates whether an Article is to be releases in future.
	 *	@access		public
	 *	@param		int			$articleId			ID of Article
	 *	@return		void
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
	 *	@todo	Implement!
	 */
	public function removeArticle( $articleId ){
		$this->removeArticleCover( $articleId );
		$this->removeArticleImage( $articleId );
		
		foreach( $this->getCategoriesOfArticle( $articleId ) as $relation )
			$this->removeArticleFromCategory( $articleId, $relation->categoryId );
		foreach( $this->getTagsOfArticle( $article ) as $relation );
#			$this->removeAbc();
		foreach( $this->getAuthorsOfArticle( $article ) as $relation );
#			$this->removeAbc();
		
		
//		$this->removeArticleImage( $articleId );
//		$this->removeArticleCover( $articleId );
//		$this->modelArticleDocument->removeByIndex( array( 'articleId' => $articleId ) );

	}

	public function removeArticleCover( $articleId ){
		$article	= $this->getArticle( $articleId );
		$id			= str_pad( $articleId, 5, 0, STR_PAD_LEFT );
		if( $article->cover ){
			@unlink( $this->pathArticleCovers.$id."__".$article->cover );
			@unlink( $this->pathArticleCovers.$id."_".$article->cover );
			$this->editArticle( $articleId, array( 'cover' => NULL ) );
		}
	}

	public function removeArticleDocument( $documentId ){
		$document	= $this->modelArticleDocument->get( $documentId );
		$id			= str_pad( $document->articleId, 5, 0, STR_PAD_LEFT );
		@unlink( $this->pathArticleDocuments.$id."_".$document->url );
		return $this->modelArticleDocument->remove( $documentId );
	}

	public function removeArticleFromCategory( $articleId, $categoryId ){
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= array(
			'articleId'		=> $articleId,
			'categoryId'	=> $categoryId,
		);
		return $this->modelArticleCategory->removeByIndices( $indices );
	}

	public function removeArticleImage( $articleId ){
		$article	= $this->modelArticle->get( $articleId );
		$id			= str_pad( $articleId, 5, 0, STR_PAD_LEFT );
		@unlink( $this->pathArticleCovers.$id."_".$article->cover );
		return $this->modelArticle->edit( $articleId, array( 'cover' => NULL ) );
	}

	public function removeAuthorFromArticle( $articleId, $authorId ){
		$this->checkArticleId( $articleId );
		$this->checkAuthorId( $authorId );
		$indices	= array(
			'articleId'	=> $articleId,
			'authorId'		=> $authorId,
		);
		return $this->modelArticleAuthor->removeByIndices( $indices );
	}

	public function removeAuthorImage( $authorId ){
		$author		= $this->getAuthor( $authorId );
		$id			= str_pad( $authorId, 5, 0, STR_PAD_LEFT );
		if( $author->image ){
			@unlink( $this->pathAuthorImages.$id."__".$author->image );
			@unlink( $this->pathAuthorImages.$id."_".$author->image );
			$this->editAuthor( $authorId, array( 'image' => NULL ) );
		}
	}

	public function removeCategory( $categoryId ){
		$this->checkCategoryId( $categoryId );
		if( $this->getArticlesOfCategory( $categoryId ) )
			throw new RuntimeException( 'Category not empty' );
		return $this->modelCategory->remove( $categoryId );
	}

	public function removeCategoryFromArticle( $articleId, $categoryId ){
		$this->checkArticleId( $articleId );
		$this->checkCategoryId( $categoryId );
		$indices	= array(
			'articleId'	=> $articleId,
			'categoryId'	=> $categoryId,
		);
		return $this->modelArticleCategory->removeByIndices( $indices );
	}

	public function setArticleAuthorRole( $articleId, $authorId, $role ){
		$this->checkArticleId( $articleId );
		$this->checkAuthorId( $authorId );
		$indices	= array( 'articleId' => $articleId, 'authorId' => $authorId );
		$relation	= $this->modelArticleAuthor->getByIndices( $indices );
		if( $relation )
			$this->modelArticleAuthor->edit( $relation->articleAuthorId, array( 'editor' => (int) $role ) );
	}

	/**
	 *	Returns List of Authors of an Article.
	 *	@access		public
	 *	@param		int			$articleId			ID of Article
	 *	@param		array		$words				Array with 'Editors' Words
	 *	@return		void
	 */
/*	public function getArticleAuthors( $articleId, $words )
	{
		$model		= new Model_Article( $articleId );
		$data		= $model->getData( TRUE );
		$language	= substr( $data['language'], 0, 2 );

		$list		= array();
		$model		= new Model_ArticleAuthor();
		$authors	= $model->getArticleAuthors( $articleId );
		foreach( $authors as $author )
		{
			if( $author['editor'] )
				$author['name']	.= $words[$language];
			$list[$author['id']]	= $author['name'];
		}
		return $list;
	}
*/
/*
	public function getArticleCategories( $articleId )
	{
		$model	= new Model_ArticleCategory();
		$model->focusForeign( "articleId", $articleId );
		$data	= $model->getData( FALSE, array( 'volume' => "ASC" ) );
		$list	= array();
		foreach( $data as $entry )
		{
			$model	= new Model_Category();
			$name	= $model->getFullCategoryName( $entry['categoryId'] );
			$list[]	= array(
				'categoryId'		=> $entry['categoryId'],
				'category_label'	=> $name,
				'relationId'		=> $entry['articleCategoryId'],
				'relation_volume'	=> $entry['volume'],
			);
		}
		return $list;
	}
*/
/*	public function getArticleCategories( $articleId, $language = "de" ){
		$model	= new Model_ArticleCategory();
		$model->focusForeign( 'articleId', $articleId );
		$data	= $model->getData();
		$list	= array();
		foreach( $data as $entry )
		{
			$category	= new Model_Category( $entry['categoryId'] );
			$category	= $category->getData( TRUE );
			$category	= array_merge(
				$category,
				array(
					'id'		=> $entry['categoryId'],
					'label'		=> $category['label_'.$language],
					'volume'	=> $entry['volume'],
				)
			);
			if( $category['parentId'] )
			{
				$parent	= new Model_Category( $category['parentId'] );
				$parent	= $parent->getData( TRUE );
				$category['parentLabel']	= $parent['label_'.$language];
			}
			$list[]	= $category;
		}
		return $list;
	}
*/
	/**
	 *	Returns List of Article where an Author is related.
	 *	@access		public
	 *	@param		int			$authorId			ID of Author
	 *	@return		void
	 */
/*	function getArticlesFromAuthor( $authorId )
	{
		$model		= new Model_ArticleAuthor();
		$model->focusForeign( 'authorId', $authorId );
		$relations	= $model->getData();
		$list		= array();
		foreach( $relations as $relation )
			$list[]	= $relation['articleId'];
		return $list;
	}
*/
	/**
	 *	Returns the amount of Articles in a Category.
	 *	@access		public
	 *	@param		int			$branchId			ID of Category
	 *	@return		int
	 */
/*	public function countArticlesInCategory( $categoryId )
	{
		$config		= $this->registry->get( 'config' );
		$builder	= new Database_StatementBuilder( $config['database.prefix'] );
		$collection	= new Collection_Article( $builder );
		$collection->withCategoryIdOrNested( $categoryId );
		$collection->collectArticles();

		$query		= $builder->buildCountStatement();
		$count		= $this->dbc->execute( $query )->fetchArray();
		return $count['rowcount'];
	}
*/
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
