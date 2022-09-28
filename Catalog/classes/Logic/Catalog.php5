<?php

use CeusMedia\HydrogenFramework\Environment\Resource\Logic;

class Logic_Catalog extends Logic
{
	/**	@var	\CeusMedia\Cache\AbstractAdapter	$cache */
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
	 *	@todo		kriss: code doc
	 */
	protected function __onInit( $a = NULL ){
		$this->env->getRuntime()->reach( 'Logic_Catalog::init start' );
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
	 *	@todo		kriss: code doc
	 */
	public function countArticles( $conditions = [] ){
		return $this->modelArticle->count( $conditions );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function countArticlesInCategory( $categoryId, $recursive = FALSE ){
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
	public function getArticle( $articleId ){
		if( NULL !== ( $data = $this->cache->get( 'catalog.article.'.$articleId ) ) )
			return $data;
		$this->checkArticleId( $articleId, TRUE );
		$data	= $this->modelArticle->get( $articleId );
		$this->cache->set( 'catalog.article.'.$articleId, $data );
		return $data;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticleCoverUrl( $articleOrId, $thumbnail = FALSE, $absolute = FALSE ){
		$article	= $articleOrId;
		if( is_int( $articleOrId ) )
			$article	= $this->getArticle( $articleOrId );
		if( !is_object( $article ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
//		$keywords	= $this->getUriPart( $article->title );
		$pathCovers	= $this->env->getConfig()->get( 'path.contents' ).'articles/covers/';
		if( !$article->cover )
			return NULL;
		$id		= str_pad( $article->articleId, 5, 0, STR_PAD_LEFT );
		$prefix	= $thumbnail ? "__" : "_";
		$uri	= $pathCovers.$id.$prefix.$article->cover;
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function getArticleTag( $articleTagId ){
		return $this->modelArticleTag->get( $articleTagId );
	}

	/**
	 *	@todo		kriss: use cache if possible
	 *	@todo		kriss: code doc
	 */
	public function getArticles( $conditions = [], $orders = [], $limits = [] ){
#		$cacheKey	= md5( json_encode( array( $conditions, $orders, $limits ) ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.articles.'.$cacheKey ) ) )
#			return $data;
		$list	= [];
		foreach( $this->modelArticle->getAll( $conditions, $orders, $limits ) as $article )
			$list[$article->articleId]	= $article;
#		$this->cache->set( 'catalog.articles.'.$cacheKey, $list );
		return $list;
	}

	public function getArticlesFromTags( $tags, $excludeArticleIds = [], $orders = [], $limits = [] ){
		$articleIds		= [];
		$articleTagsMap	= [];
		$relations		= $this->modelArticleTag->getAll( array( 'tag' => $tags ) );

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
			$articles	= $this->getArticles( array( 'articleId' => $filteredArticleIds ), $orders, $limits );
			foreach( $filteredArticleIds as $articleId ){
				$list[$articleId]	= (object) array(
					'tags'		=> $articleTagsMap[$articleId],
					'article'	=> $articles[$articleId],
					'matches'	=> $articleIds[$articleId]
				);
			}
		}
		return $list;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticlesFromAuthor( $author, $orders = [], $limits = [] ){
		$articles	= $this->modelArticleAuthor->getAllByIndex( 'authorId', $author->authorId );
		$articleIds	= [];
		foreach( $articles as $article )
			$articleIds[]	= $article->articleId;
		if( $articleIds ){
			$conditions	= array( 'articleId' => $articleIds );
			$articles	= $this->getArticles( $conditions, $orders, $limits );
			return $articles;
		}
		return array();
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticlesFromAuthorIds( $authorIds, $returnIds = FALSE ){
		$model		= new Model_Catalog_Article_Author( $this->env );
		$articles	= $model->getAll( array( 'authorId' => array_values( $authorIds ) ) );
		if( !$returnIds )
			return $articles;
		$ids	= [];
		foreach( $articles as $article )
			$ids[]	= $article->articleId;
		return $ids;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticlesFromAuthors( $authors, $returnIds = FALSE ){
		$authorIds	= [];
		foreach( $authors as $author )
			$authorIds[]	= $author->authorId;
		return $this->getArticlesFromAuthorIds( $authorIds, $returnIds );
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getArticleUri( $articleOrId, $absolute = FALSE ){
		$article	= $articleOrId;
		if( is_int( $articleOrId ) )
			$article	= $this->getArticle( $articleOrId );
		if( !is_object( $article ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		$keywords	= $this->getUriPart( $article->title );
		$uri		= 'catalog/article/'.$article->articleId.'-'.$keywords;
		return $absolute ? $this->env->url.$uri : './'.$uri;
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
	public function getAuthors( $conditions = [], $orders = [] ){
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
	public function getAuthorsOfArticle( $articleId ){
		if( NULL !== ( $data = $this->cache->get( 'catalog.article.author.'.$articleId ) ) )
			return $data;
		$data	= $this->modelArticleAuthor->getAllByIndex( 'articleId', $articleId );
		$list	= [];
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
		$author	= $authorOrId;
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
	 *	@todo		kriss: code doc
	 */
	public function getCategories( $conditions = [], $orders = [] ){
#		$cacheKey	= md5( json_encode( array( $conditions, $orders ) ) );
#		if( ( $data = $this->cache->get( 'catalog.categories.'.$cacheKey ) ) )
#			return $data;

		$list	= [];
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
	public function getCategoryArticles( $category, $orders = [], $limits = [] ){
#		$cacheKey	= md5( json_encode( array( $category->categoryId, $orders, $limits ) ) );
#		if( NULL !== ( $data = $this->cache->get( 'catalog.category.articles.'.$cacheKey ) ) )
#			return $data;
		$conditions	= array( 'categoryId' => $category->categoryId );
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
	 *	@todo		kriss: code doc
	 */
	public function getCategoryOfArticle( $articleId ){
		$relation	= $this->modelArticleCategory->getByIndex( 'articleId', $articleId );
		$category			= $this->modelCategory->get( $relation->categoryId );
		$category->volume	= $relation->volume;
//		$article->volume	= $relation->volume;
		return $category;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getCategoryUri( $categoryOrId, $language = "de", $absolute = FALSE ){
		$category	= $categoryOrId;
		if( is_int( $categoryOrId ) )
			$category	= $this->getCategory( $categoryOrId );
		$uri		= 'catalog';
		if( $category->categoryId ){
			$labelKey	= 'label_'.$language;
			$keywords	= $this->getUriPart( $category->$labelKey );
			$uri		.= '/category/'.$category->categoryId.'-'.$keywords;
		}
		return $absolute ? $this->env->url.$uri : './'.$uri;
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
		$list	= [];
		foreach( $tags as $tag )
			$list[$tag->tag]	= $tag;
		if( $sort )
			ksort( $list );
		return $list;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getTagUri( $tagOrId, $language = "de", $absolute = FALSE ){
		$tag	= $tagOrId;
		if( is_int( $tagOrId ) )
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
?>
