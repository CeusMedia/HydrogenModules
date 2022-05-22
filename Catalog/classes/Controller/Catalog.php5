<?php

use CeusMedia\HydrogenFramework\Environment;

class Controller_Catalog extends CMF_Hydrogen_Controller
{
	/**	@var	Logic_ShopBridge	$bridge */
	protected $bridge;

	/**	@var	integer				$bridgeId */
	protected $bridgeId;

	/**	@var	Logic_Catalog		$logic */
	protected $logic;

	public static function ___onRegisterSitemapLinks( Environment $env, $context, $module, $data )
	{
		$baseUrl	= $env->url.'catalog/';
		$logic		= new Logic_Catalog( $env );
		$articles	= $logic->getArticles( array(), array( 'articleId' => 'DESC' ) );
		foreach( $articles as $article ){
			$url	= $logic->getArticleUri( $article, TRUE );
			$date	= max( $article->createdAt, $article->modifiedAt );
			$context->addLink( $url, $date > 0 ? $data : NULL );
		}
		$authors	= $logic->getAuthors( array(), array( 'authorId' => 'DESC' ) );
		foreach( $authors as $author ){
			$url	= $logic->getAuthorUri( $author, TRUE );
			$date	= NULL;//max( $author->createdAt, $author->modifiedAt );
			$context->addLink( $url, $date );
		}
	}

	public function article( $articleId )
	{
		$articleId	= (int) $articleId;
		$article	= $this->logic->getArticle( $articleId );
		if( !$article ){
			$this->messenger->noteError( 'Der angeforderte Artikel existiert nicht.' );
			$this->restart( NULL, TRUE );
		}
		$logicShop	= new Logic_Shop( $this->env );
		$this->addData( 'article', $article );
		$this->addData( 'tags', $this->logic->getTagsOfArticle( $articleId, FALSE ) );				//  append article tags (unsorted)
		$this->addData( 'authors', $this->logic->getAuthorsOfArticle( $articleId ) );
		$this->addData( 'category', $this->logic->getCategoryOfArticle( $articleId ) );
		$this->addData( 'documents', $this->logic->getDocumentsOfArticle( $articleId ) );
		$this->addData( 'cart', (bool) $logicShop->countArticlesInCart() );
		$this->addData( 'inCart', $logicShop->countArticleInCart( $this->bridgeId, $articleId ) );
		$tags	= [];
		foreach( $this->logic->getTagsOfArticle( $articleId, FALSE ) as $tag )
			$tags[]	= $tag->tag;
		$relatedArticles	= [];
		if( $tags ){
			$relatedArticles	= $this->logic->getArticlesFromTags( $tags, array( $article->articleId ) );
			$this->addData( 'relatedArticles', $relatedArticles );
		}
	}

	public function articles()
	{
	}

	public function author( $authorId )
	{
//		$authorId	= preg_replace( "/-[a-z0-9_-]*$/", "", $authorId );
		$authorId	= (int) $authorId;
		$author		= $this->logic->getAuthor( $authorId );

		$this->addData( 'author', $author );

		$articles	= $this->logic->getArticlesFromAuthor( $author, array( 'createdAt' => 'DESC' ) );
		$this->addData( 'articles', $articles );
	}

	public function authors()
	{
		$this->addData( 'authors', $this->logic->getAuthors( array(), array( 'lastname' => 'ASC' ) ) );
	}

	public function categories()
	{
		$cache	= $this->env->getCache();
		if( NULL === ( $categories = $cache->get( 'catalog.categories' ) ) ){
			$orders		= array( 'rank' => 'ASC' );
			$conditions	= array( 'parentId' => 0, 'visible' => 1 );
			$categories	= $this->logic->getCategories( $conditions, $orders );
			foreach( $categories as $nr => $category ){
				$conditions	= array( 'parentId' => $category->categoryId, 'visible' => 1 );
				$categories[$nr]->categories	= $this->logic->getCategories( $conditions, $orders );
			}
			$cache->set( 'catalog.categories', $categories );
		}
		$this->addData( 'categories', $categories );
	}

	public function category( $categoryId )
	{
		$categoryId	= (int) $categoryId;
		$category	= $this->logic->getCategory( $categoryId );

		//  --  SUBCATEGORIES  --  //
		$conditions	= array( 'parentId' => $categoryId );
		$orders		= array( 'rank' => "ASC", 'label_de' => "ASC" );
		$category->children	= $this->logic->getCategories( $conditions, $orders );

		$this->addData( 'categoryId', $categoryId );
		$this->addData( 'category', $category );
	}

	public function index( $categoryId = NULL )
	{
		if( $categoryId && (int) $categoryId )
			$this->restart( 'category/'.$categoryId, TRUE );
		$this->restart( 'categories', TRUE );
	}

	/**
	 *	@todo		kriss: extract head and foot to module MerchantFeed with hook support
	 *	@todo		kriss: rename to (and implement as) ___onMerchantFeedEnlist after module MerchantFeed is implemented
	 *	@todo		kriss: extract labels
	 *	@todo		kriss: BONUS: draft resolution for Google categories and implement solution for hooked modules
	 */
	public function feed()
	{
		$options	= $this->env->getConfig()->getAll( 'module.catalog.feed.', TRUE );
		$language	= $this->env->getLanguage()->getLanguage();
		$words		= (object) $this->getWords( 'rss' );
		$helper		= new View_Helper_Catalog( $this->env );

		$builder	= new XML_RSS_GoogleBaseBuilder();
		$builder->setChannelData( array(
			'title'			=> $this->env->title,
			'link'			=> $this->env->url,
			'description'	=> $words->description,
			'pubDate'		=> date( 'r' ),
			'lastBuildDate'	=> date( 'r' ),
			'language'		=> $language,
		) );

		$builder->addItemElement( 'g:price', TRUE );
		$builder->addItemElement( 'g:condition', TRUE );
		$builder->addItemElement( 'g:price', TRUE );
		$builder->addItemElement( 'g:availability', TRUE );
		$builder->addItemElement( 'g:gtin', TRUE );
		$builder->addItemElement( 'g:image_link', FALSE );

		$availabilities	= array(
			-2		=> "Nicht auf Lager",
			-1		=> "Vorbestellt",
			0		=> "Auf Lager",
			1		=> "Bestellbar"
		);

		$conditions		= array( 'price' => '> 0', 'isn' => '> 0'/*, 'status' => array( 0, 1 )*/ );
		$orders			= array( 'createdAt' => 'DESC' );
		foreach( $this->logic->getArticles( $conditions, $orders ) as $article ){
			$pubDate	= strtotime( $article->publication );
			$categories	= [];
			foreach( $this->logic->getCategoriesOfArticle( $article->articleId ) as $category )
				$categories[]	= $category->{"label_".$language};
			$price	= (float) str_replace( ",", ".", $article->price );
			$item	= array(
				"title"				=> $article->title,
				"description"		=> $article->description,
				"link"				=> $helper->getArticleUri( $article->articleId, TRUE ),
				"category"			=> join( ', ', $categories ),
				"pubDate"			=> date( 'r', $pubDate ? $pubDate : $article->createdAt ),
				"guid"				=> $this->env->url.'catalog/article/'.$article->articleId ,
				"g:id"				=> $article->articleId,
				"g:price"			=> number_format( $price, 2, '.', '' ).' EUR',
				"g:category"		=> $article->series ? 'Media &gt; Zeitschriften' : 'Media &gt; Bücher',
				"g:condition"		=> 'neu',
				"g:availability"	=> $availabilities[(int) $article->status],
				"g:gtin"			=> $article->isn
			);
			if( $article->cover )
				$item['g:image_link']	= $this->logic->getArticleCoverUrl( $article, FALSE, TRUE );
			$builder->addItem( $item );
		}
		$xml	= $builder->build();
		if( !$this->request->has( 'headerless' ) && !$this->request->has( 'debug' ) ){
			header( 'Content-type: application/rss+xml, application/xml, text/xml' );
			header( 'Content-length: '.strlen( $xml ) );
		}
		$this->request->has( 'debug' ) ? xmp( $xml ) : print( $xml );
		exit;
	}

	public function news()
	{
		$articles	= $this->logic->getArticles( array( 'new' => 1 ), array( 'createdAt' => 'DESC' ) );
		$this->addData( 'articles', $articles );
	}

	public function order()
	{
		$request	= $this->env->getRequest();
		$articleId	= (int) $request->get( 'articleId' );
		$article	= $this->logic->getArticle( $articleId );
		$forwardUrl	= urlencode( $this->logic->getArticleUri( $articleId ) );
		$quantity	= (int) preg_replace( "/[^0-9-]/", "", $request->get( 'quantity' ) );
		$url		= 'shop/addArticle/'.$this->bridgeId.'/'.$articleId.'/'.$quantity.'?forwardTo='.$forwardUrl;
		if( $quantity < 1 )
			$url		= $this->logic->getArticleUri( $articleId );
		$this->restart( $url );
	}

	public function rss( $categoryId = NULL )
	{
		$options	= $this->env->getConfig()->getAll( 'module.catalog.feed.', TRUE );
		$language	= $this->env->getLanguage()->getLanguage();
		$categoryId	= (int) $categoryId;
		$words		= (object) $this->getWords( 'rss' );
		$helper		= new View_Helper_Catalog( $this->env );
		$rss		= new XML_RSS_Builder();
		$data		= array(
			'title'			=> $this->env->title,
			'link'			=> $this->env->url,
			'description'	=> $words->description,
			'pubDate'		=> date( 'r' ),
			'lastBuildDate'	=> date( 'r' ),
			'language'		=> $language,
		);
		if( $options->get( 'image.url' ) ){
			$data['imageUrl']	= $options->get( 'image.url' );
			if( $options->get( 'image.link' ) )
				$data['imageLink']	= $options->get( 'image.link' );
			if( $options->get( 'image.title' ) )
				$data['imageTitle']	= $options->get( 'image.title' );
			if( $options->get( 'image.width' ) > 0 )
				$data['imageWidth']	= $options->get( 'image.width' );
			if( $options->get( 'image.height' ) > 0 )
				$data['imageHeight']	= $options->get( 'image.height' );
		}
		$rss->setChannelData( $data );

		$conditions		= array(
			'status'	=> array( 0, 1 ),
			'new'		=> 1
		);
		if( $categoryId ){
			$categories	= array( $categoryId );
			$children	= $this->logic->getCategories( array( 'parentId' => $categoryId ) );
			foreach( $children as $category )
				$categories[]	= $category->categoryId;
			$model		= new Model_Catalog_Article_Category( $this->env );
			$articleIds	= [];
			foreach( $model->getAll( array( 'categoryId' => $categories ) ) as $relation )
				$articleIds[]	= $relation->articleId;
			if( $articleIds )
				$conditions['articleId']	= $articleIds;
		}
		$orders			= array( 'createdAt' => 'DESC' );
		foreach( $this->logic->getArticles( $conditions, $orders, array( 0, 35 ) ) as $article ){
			$pubDate	= strtotime( $article->publication );
			$categories	= [];
			foreach( $this->logic->getCategoriesOfArticle( $article->articleId ) as $category )
				$categories[]	= $category->{"label_".$language};
			$item	= array(
				"title"			=> $article->title,
				"description"	=> $article->description,
				"link"			=> $helper->getArticleUri( $article->articleId, TRUE ),
				"category"		=> join( ', ', $categories ),
				"pubDate"		=> date( 'r', $pubDate ? $pubDate : $article->createdAt ),
				"guid"			=> $this->env->url.'catalog/article/'.$article->articleId ,
				"source"		=> $this->env->url.'catalog/rss',
			);
			$rss->addItem($item);
		}
		$xml	= $rss->build();
		header( 'Content-type: application/rss+xml, application/xml, text/xml' );
		header( 'Content-length: '.strlen( $xml ) );
		print( $xml );
		exit;
	}

	public function search( $page = 0 )
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();

		if( $request->has( 'search' ) ){
			$session->set( 'catalog_search_term', $request->get( 'term' ) );
			$session->set( 'catalog_search_authorId', $request->get( 'authorId' ) );
			$session->set( 'catalog_search_categoryId', $request->get( 'categoryId' ) );
			$session->set( 'catalog_search_hasPicture', $request->get( 'picture' ) );
			$session->set( 'catalog_search_isAvailable', $request->get( 'status' ) );
		}

		$this->addData( 'searchTerm', $session->get( 'catalog_search_term' ) );
		$this->addData( 'searchAuthorId', $session->get( 'catalog_search_authorId' ) );
		$this->addData( 'searchCategoryId', $session->get( 'catalog_search_categoryId' ) );
		$this->addData( 'searchPicture', $session->get( 'catalog_search_hasPicture' ) );
		$this->addData( 'searchStatus', $session->get( 'catalog_search_isAvailable' ) );

		$limit		= 10;
		$offset		= $page * $limit;
		$cache		= $this->env->getCache();
//		$cache->flush();
#		print_m( $session->getAll() );
#		die;

		$database	= $this->env->getDatabase();
		$prefix		= $database->getPrefix();

		$total		= 0;
		$articles	= [];

		$idsTags	= [];
		$idsSearch	= [];

			$articleIds	= [];

		if( strlen( trim( $session->get( 'catalog_search_term' ) ) ) ){
			$terms		= explode( " ", trim( $session->get( 'catalog_search_term' ) ) );
			foreach( $terms as $term ){
				$tables		= array(
					$prefix."catalog_articles AS a",
					$prefix."catalog_article_tags AS c",
				);
				$conditions	= array(
					"a.articleId = c.articleId",
//					"c.tag LIKE '%".$term."%'"
					"c.tag LIKE '%".trim( $term )."%'"
				);
				$query		= "SELECT DISTINCT(a.articleId) FROM ".join( ', ', $tables )." WHERE ".join( ' AND ', $conditions );
				$results	= $database->query( $query );
				foreach( $results->fetchAll( PDO::FETCH_OBJ ) as $result )
					$idsTags[]	= $result->articleId;
			}
			foreach( $terms as $term ){
				$tables		= array(
					$prefix."catalog_articles AS a",
					$prefix."catalog_article_authors AS ab",
					$prefix."catalog_authors AS b",
				);
				$conditions	= array(
					"a.articleId = ab.articleId",
					"ab.authorId = b.authorId",
					"CONCAT(a.title, a.subtitle, a.description, a.isn, b.firstname, b.lastname) LIKE '%".$term."%'"
				);
				$query		= "SELECT DISTINCT(a.articleId) FROM ".join( ', ', $tables )." WHERE ".join( ' AND ', $conditions );
				$results	= $database->query( $query );
				foreach( $results->fetchAll( PDO::FETCH_OBJ ) as $result )
					$idsSearch[]	= $result->articleId;
			}
			if( $idsTags && $idsSearch )
				$articleIds	= array_merge( $idsTags, $idsSearch );
			else if( $idsTags )
				$articleIds	= $idsTags;
			else
				$articleIds	= $idsSearch;



			if( $articleIds ){
				$tables		= array(
					$prefix."catalog_articles AS a",
					$prefix."catalog_article_authors AS ab",
					$prefix."catalog_authors AS b",
				);
				$conditions	= array(
					"a.articleId = ab.articleId",
					"ab.authorId = b.authorId",
				);
				if( $session->get( 'catalog_search_isAvailable' ) )
					$conditions[]	= "a.status = 0";
				if( $session->get( 'catalog_search_hasPicture' ) )
					$conditions[]	= "a.cover IS NOT NULL";
				if( $session->get( 'catalog_search_categoryId' ) ){
					$tables[]		= $prefix."catalog_article_categories AS ac";
					$conditions[]	= "ac.categoryId = ".$session->get( 'catalog_search_categoryId' );
					$conditions[]	= "a.articleId = ac.articleId";
				}
				if( $session->get( 'catalog_search_authorId' ) )
					$conditions[]	= "b.authorId = ".$session->get( 'catalog_search_authorId' );
				$conditions[]	= "a.articleId IN (".join( ',', $articleIds ).")";

				$query		= "SELECT DISTINCT(a.articleId) FROM ".join( ', ', $tables )." WHERE ".join( ' AND ', $conditions );
				$results	= $database->query( $query );
				foreach( $results->fetchAll( PDO::FETCH_OBJ ) as $result )
					$articles[]	= $result->articleId;
//				$articles	= $articles !== NULL ? array_intersect( $articles, $articleIds ) : $articleIds;
			}
		}
		else if( $session->get( 'catalog_search_authorId' ) ){
			$model	= new Model_Catalog_Article_Author( $this->env );
			$relations	= $model->getAll( array( 'authorId' => $session->get( 'catalog_search_authorId' ) ) );
			foreach( $relations as $relation )
				$articles[]	= $relation->articleId;
		}
		if( $articles ){
			$articles	= array_unique( $articles );
			$model		= new Model_Catalog_Article( $this->env );
			$total		= count( $articles );
			$offset		= $offset >= $total ? 0 : $offset;
			$articles	= $model->getAll( array( 'articleId' => $articles ), array( 'articleId' => 'DESC' ), array( $offset, $limit ) );
		}

		if( NULL === ( $authors = $cache->get( 'catalog.search.authors' ) ) ){
			$authors	= $this->logic->getAuthors( array(), array( 'lastname' => 'ASC', 'firstname' => 'ASC' ) );
			$cache->set( 'catalog.search.authors', $authors );
		}

		if( NULL === ( $categories = $cache->get( 'catalog.search.categories' ) ) ){
			$conditions	= array( 'parentId' => 0, 'visible' => 1 );
			$categories	= $this->logic->getCategories( $conditions, array( 'label_de' => 'ASC' ) );
			$cache->set( 'catalog.search.categories', $categories );
		}

		$this->addData( 'total', $total );
		$this->addData( 'articles', $articles );
		$this->addData( 'page', $page );
		$this->addData( 'authors', $authors );
		$this->addData( 'categories', $categories );
		$this->addData( 'limit', $limit );
	}

	public function tag( $tagId = NULL )
	{
		if( !$tagId || !( $tag = $this->logic->getArticleTag( $tagId ) ) )
			$this->restart( NULL, TRUE );

		$articles	= $this->logic->getArticlesFromTags( array( $tag->tag ) );

		$this->addData( 'tag', $tag );
		$this->addData( 'tagId', $tagId );
		$this->addData( 'articles', $articles );
	}

	protected function __onInit()
	{
		$this->logic		= new Logic_Catalog( $this->env );
		$this->bridge		= new Logic_ShopBridge( $this->env );
		$this->bridgeId		= $this->bridge->getBridgeId( 'CatalogArticle' );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
	}
}
