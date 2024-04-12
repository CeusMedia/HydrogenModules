<?php /** @noinspection DuplicatedCode */

/** @noinspection SqlResolve */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\ADT\URL as Url;
use CeusMedia\Common\ADT\URL\Compare as UrlCompare;
use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\XML\RSS\Builder as RssBuilder;
use CeusMedia\Common\XML\RSS\GoogleBaseBuilder as RssGoogleBaseBuilder;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class Controller_Catalog_Bookstore extends Controller
{
	/**	@var	Logic_ShopBridge			$bridge */
	protected Logic_ShopBridge $bridge;

	/**	@var	integer						$bridgeId */
	protected int $bridgeId;

	/**	@var	Logic_Catalog_Bookstore		$logic */
	protected Logic_Catalog_Bookstore $logic;

	protected HttpRequest $request;

	protected MessengerResource $messenger;

	public function article( string $articleId ): void
	{
		try{
			$article	= $this->logic->getArticle( $articleId );
			$logicShop	= new Logic_Shop( $this->env );
			$this->addData( 'article', $article );
			$this->addData( 'tags', $this->logic->getTagsOfArticle( $articleId ) );				//  append article tags (unsorted)
			$this->addData( 'authors', $this->logic->getAuthorsOfArticle( $articleId ) );
			$this->addData( 'category', $this->logic->getCategoryOfArticle( $articleId ) );
			$this->addData( 'documents', $this->logic->getDocumentsOfArticle( $articleId ) );
			$this->addData( 'cart', (bool) $logicShop->countArticlesInCart() );
			$this->addData( 'inCart', $logicShop->countArticleInCart( $this->bridgeId, $articleId ) );

			$fileImageLarge	= $this->logic->getArticleCoverUrl( $article, 'l' );
//print_m( $fileImageLarge );die;
//		if( $fileImageLarge && !file_exists( $fileImageLarge ) )
//			$fileImageLarge = NULL;
			$this->addData( 'uriCoverLarge', $fileImageLarge );

			if( getEnv( 'HTTP_REFERER' ) ){
				$urlFrom	=  new Url( getEnv( 'HTTP_REFERER' ), new Url( $this->env->url ) );
				if( UrlCompare::sameBaseStatic( $urlFrom, $this->env->url ) ){
					$this->addData( 'from', $urlFrom->getRelative() );
				}
			}
			if( $this->request->get( 'from' ) ){
				$urlFrom	=  new Url( $this->request->get( 'from' ), new Url( $this->env->url ) );
				if( UrlCompare::sameBaseStatic( $urlFrom, $this->env->url ) ){
					$this->addData( 'from', $urlFrom->getRelative() );
				}
			}

			$tags	= [];
			foreach( $this->logic->getTagsOfArticle( $articleId ) as $tag )
				$tags[]	= $tag->tag;

			if( $tags ){
				$relatedArticles	= $this->logic->getArticlesFromTags( $tags, [$article->articleId] );
				$this->addData( 'relatedArticles', $relatedArticles );
			}
		}
		catch( Throwable ){
			$this->messenger->noteError( 'Der angeforderte Artikel existiert nicht.' );
			$this->restart( NULL, TRUE );
		}
	}

	public function articles(): void
	{
	}

	/**
	 *	@param		string		$authorId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function author( string $authorId ): void
	{
//		$authorId	= preg_replace( "/-[a-z0-9_-]*$/", "", $authorId );
		$authorId	= (int) $authorId;
		$author		= $this->logic->getAuthor( $authorId );

		$this->addData( 'author', $author );

		$articles	= $this->logic->getArticlesFromAuthor( $author, ['createdAt' => 'DESC'] );
		$this->addData( 'articles', $articles );
	}

	public function authors(): void
	{
		$this->addData( 'authors', $this->logic->getAuthors( [], ['lastname' => 'ASC'] ) );
	}

	/**
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function categories(): void
	{
		$cache	= $this->env->getCache();
		if( NULL === ( $categories = $cache->get( 'catalog.bookstore.categories' ) ) ){
			$orders		= ['rank' => 'ASC'];
			$conditions	= ['parentId' => 0, 'visible' => 1];
			$categories	= $this->logic->getCategories( $conditions, $orders );
			foreach( $categories as $category ){
				$conditions	= ['parentId' => $category->categoryId, 'visible' => 1];
				$category->categories	= $this->logic->getCategories( $conditions, $orders );
			}
			$cache->set( 'catalog.bookstore.categories', $categories );
		}
		$script	= 'ModuleCatalogBookstoreCategoryIndex.init("#categoryList");';
		$this->env->getPage()->js->addScriptOnReady( $script );
		$this->addData( 'categories', $categories );
	}

	/**
	 *	@param		string		$categoryId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function category( string $categoryId ): void
	{
		$category	= $this->logic->getCategory( $categoryId );

		//  --  SUBCATEGORIES  --  //
		$conditions	= ['parentId' => $categoryId];
		$orders		= ['rank' => "ASC", 'label_de' => "ASC"];
		$category->children	= $this->logic->getCategories( $conditions, $orders );

		$this->addData( 'categoryId', $categoryId );
		$this->addData( 'category', $category );
	}

	public function index( ?string $categoryId = NULL ): void
	{
		if( $categoryId && (int) $categoryId )
			$this->restart( 'category/'.$categoryId, TRUE );
		$this->restart( 'categories', TRUE );
	}

	/**
	 *	@todo		 extract head and foot to module MerchantFeed with hook support
	 *	@todo		 rename to (and implement as) ___onMerchantFeedEnlist after module MerchantFeed is implemented
	 *	@todo		 extract labels
	 *	@todo		 BONUS: draft resolution for Google categories and implement solution for hooked modules
	 *	@throws		DOMException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function feed(): void
	{
//		$options	= $this->env->getConfig()->getAll( 'module.catalog_bookstore.feed.', TRUE );
		$language	= $this->env->getLanguage()->getLanguage();
		$words		= (object) $this->getWords( 'rss' );
		$helper		= new View_Helper_Catalog_Bookstore( $this->env );

		$builder	= new RssGoogleBaseBuilder();
		$builder->setChannelData( [
			'title'			=> TextTrimmer::trim( $this->env->title, 150 ),
			'link'			=> $this->env->url,
			'description'	=> TextTrimmer::trim( $words->description, 5000 ),
			'pubDate'		=> date( 'r' ),
			'lastBuildDate'	=> date( 'r' ),
			'language'		=> $language,
		] );

		$builder->addItemElement( 'g:price', TRUE );
		$builder->addItemElement( 'g:condition', TRUE );
		$builder->addItemElement( 'g:price', TRUE );
		$builder->addItemElement( 'g:availability', TRUE );
		$builder->addItemElement( 'g:gtin', TRUE );
		$builder->addItemElement( 'g:image_link' );

		$availabilities	= [
			-2		=> "out of stock",
			-1		=> "preorder",
			0		=> "in stock",
			1		=> "in stock"
		];

		$conditions		= ['price' => '> 0', 'isn' => '> 0'/*, 'status' => array[ 0, 1]*/];
		$orders			= ['createdAt' => 'DESC'];
		foreach( $this->logic->getArticles( $conditions, $orders ) as $article ){
			$pubDate	= strtotime( $article->publication );
			$categories	= [];
			foreach( $this->logic->getCategoriesOfArticle( $article->articleId ) as $category )
				$categories[]	= $category->{"label_".$language};
			$price	= (float) str_replace( ",", ".", $article->price );
			$item	= [
				"title"				=> TextTrimmer::trim( $article->title, 150 ),
				"description"		=> TextTrimmer::trim( $article->description, 5000 ),
				"link"				=> $helper->getArticleUri( $article->articleId, TRUE ),
				"category"			=> join( ', ', $categories ),
				"pubDate"			=> date( 'r', $pubDate ?: $article->createdAt ),
				"guid"				=> $this->env->url.'catalog/bookstore/article/'.$article->articleId ,
				"g:id"				=> $article->articleId,
				"g:price"			=> number_format( $price, 2, '.', '' ).' EUR',
				"g:category"		=> $article->series ? 'Media &gt; Zeitschriften' : 'Media &gt; Bücher',
				"g:condition"		=> 'neu',
				"g:availability"	=> $availabilities[(int) $article->status],
				"g:gtin"			=> $article->isn
			];
			if( $article->status == -1 )
				$item['g:availability_date']	= date( "r", strtotime( $article->publication ) );
			if( $article->cover )
				$item['g:image_link']	= $this->logic->getArticleCoverUrl( $article, 'm', TRUE );
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

	public function news(): void
	{
		$articles	= $this->logic->getArticles( ['new' => 1], ['createdAt' => 'DESC'] );
		$this->addData( 'articles', $articles );
	}

	/**
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function order(): void
	{
		$request	= $this->env->getRequest();
		$articleId	= (int) $request->get( 'articleId' );
		$article	= $this->logic->getArticle( $articleId );
		$forwardUrl	= $this->logic->getArticleUri( $articleId );
		if( $request->get( 'from' ) )
			$forwardUrl	.= '?from='.$request->get( 'from' );
		$quantity	= (int) preg_replace( "/[^0-9-]/", "", $request->get( 'quantity' ) );
		$url		= 'shop/addArticle/'.$this->bridgeId.'/'.$articleId.'/'.$quantity.'?forwardTo='.urlencode( $forwardUrl );
		if( $quantity < 1 )
			$url		= $this->logic->getArticleUri( $articleId );
		$this->restart( $url );
	}

	/**
	 *	@param		string|NULL		$categoryId
	 *	@return		void
	 *	@throws		DOMException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function rss( ?string $categoryId = NULL ): void
	{
		/** @var Dictionary $options */
		$options	= $this->env->getConfig()->getAll( 'module.catalog_bookstore.feed.', TRUE );
		$language	= $this->env->getLanguage()->getLanguage();
		$categoryId	= (int) $categoryId;
		$words		= (object) $this->getWords( 'rss' );
		$helper		= new View_Helper_Catalog_Bookstore( $this->env );
		$rss		= new RssBuilder();
		$data		= [
			'title'			=> $this->env->title,
			'link'			=> $this->env->url,
			'description'	=> $words->description,
			'pubDate'		=> date( 'r' ),
			'lastBuildDate'	=> date( 'r' ),
			'language'		=> $language,
		];
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

		$conditions		= [
			'status'	=> [0, 1],
			'new'		=> 1
		];
		if( $categoryId ){
			$categories	= [$categoryId];
			$children	= $this->logic->getCategories( ['parentId' => $categoryId] );
			foreach( $children as $category )
				$categories[]	= $category->categoryId;
			$model		= new Model_Catalog_Article_Category( $this->env );
			$articleIds	= [];
			foreach( $model->getAll( ['categoryId' => $categories] ) as $relation )
				$articleIds[]	= $relation->articleId;
			if( $articleIds )
				$conditions['articleId']	= $articleIds;
		}
		$orders			= ['createdAt' => 'DESC'];
		foreach( $this->logic->getArticles( $conditions, $orders, [0, 35] ) as $article ){
			$pubDate	= strtotime( $article->publication );
			$categories	= [];
			foreach( $this->logic->getCategoriesOfArticle( $article->articleId ) as $category )
				$categories[]	= $category->{"label_".$language};
			$item	= [
				"title"			=> $article->title,
				"description"	=> $article->description,
				"link"			=> $helper->getArticleUri( $article->articleId, TRUE ),
				"category"		=> join( ', ', $categories ),
				"pubDate"		=> date( 'r', $pubDate ?: $article->createdAt ),
				"guid"			=> $this->env->url.'catalog/bookstore/article/'.$article->articleId ,
				"source"		=> $this->env->url.'catalog/bookstore/rss',
			];
			$rss->addItem($item);
		}
		$xml	= $rss->build();
		header( 'Content-type: application/rss+xml, application/xml, text/xml' );
		header( 'Content-length: '.strlen( $xml ) );
		print( $xml );
		exit;
	}

	/**
	 *	@param		integer		$page
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function search( int $page = 0 ): void
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();

		if( $request->has( 'search' ) ){
			$session->set( 'catalog_bookstore_search_term', $request->get( 'term' ) );
			$session->set( 'catalog_bookstore_search_authorId', $request->get( 'authorId' ) );
			$session->set( 'catalog_bookstore_search_categoryId', $request->get( 'categoryId' ) );
			$session->set( 'catalog_bookstore_search_hasPicture', $request->get( 'picture' ) );
			$session->set( 'catalog_bookstore_search_isAvailable', $request->get( 'status' ) );
		}

		$this->addData( 'searchTerm', $session->get( 'catalog_bookstore_search_term' ) );
		$this->addData( 'searchAuthorId', $session->get( 'catalog_bookstore_search_authorId' ) );
		$this->addData( 'searchCategoryId', $session->get( 'catalog_bookstore_search_categoryId' ) );
		$this->addData( 'searchPicture', $session->get( 'catalog_bookstore_search_hasPicture' ) );
		$this->addData( 'searchStatus', $session->get( 'catalog_bookstore_search_isAvailable' ) );

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

		if( strlen( trim( $session->get( 'catalog_bookstore_search_term' ) ) ) ){
			$terms		= explode( " ", trim( $session->get( 'catalog_bookstore_search_term' ) ) );
			foreach( $terms as $term ){
				$tables		= [
					$prefix."catalog_bookstore_articles AS a",
					$prefix."catalog_bookstore_article_tags AS c",
				];
				$conditions	= [
					"a.articleId = c.articleId",
//					"c.tag LIKE '%".$term."%'"
					"c.tag LIKE '%".trim( $term )."%'"
				];
				$query		= "SELECT DISTINCT(a.articleId) FROM ".join( ', ', $tables )." WHERE ".join( ' AND ', $conditions );
				$results	= $database->query( $query );
				foreach( $results->fetchAll( PDO::FETCH_OBJ ) as $result )
					$idsTags[]	= $result->articleId;
			}
			foreach( $terms as $term ){
				$tables		= [
					$prefix."catalog_bookstore_articles AS a",
					$prefix."catalog_bookstore_article_authors AS ab",
					$prefix."catalog_bookstore_authors AS b",
				];
				$conditions	= [
					"a.articleId = ab.articleId",
					"ab.authorId = b.authorId",
					"CONCAT(a.title, a.subtitle, a.description, a.isn, b.firstname, b.lastname) LIKE '%".$term."%'"
				];
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
				$tables		= [
					$prefix."catalog_bookstore_articles AS a",
					$prefix."catalog_bookstore_article_authors AS ab",
					$prefix."catalog_bookstore_authors AS b",
				];
				$conditions	= [
					"a.articleId = ab.articleId",
					"ab.authorId = b.authorId",
				];
				if( $session->get( 'catalog_bookstore_search_isAvailable' ) )
					$conditions[]	= "a.status = 0";
				if( $session->get( 'catalog_bookstore_search_hasPicture' ) )
					$conditions[]	= "a.cover IS NOT NULL";
				if( $session->get( 'catalog_bookstore_search_categoryId' ) ){
					$tables[]		= $prefix."catalog_bookstore_article_categories AS ac";
					$conditions[]	= "ac.categoryId = ".$session->get( 'catalog_bookstore_search_categoryId' );
					$conditions[]	= "a.articleId = ac.articleId";
				}
				if( $session->get( 'catalog_bookstore_search_authorId' ) )
					$conditions[]	= "b.authorId = ".$session->get( 'catalog_bookstore_search_authorId' );
				$conditions[]	= "a.articleId IN (".join( ',', $articleIds ).")";

				$query		= "SELECT DISTINCT(a.articleId) FROM ".join( ', ', $tables )." WHERE ".join( ' AND ', $conditions );
				$results	= $database->query( $query );
				foreach( $results->fetchAll( PDO::FETCH_OBJ ) as $result )
					$articles[]	= $result->articleId;
//				$articles	= $articles !== NULL ? array_intersect( $articles, $articleIds ) : $articleIds;
			}
		}
		else if( $session->get( 'catalog_bookstore_search_authorId' ) ){
			$model	= new Model_Catalog_Bookstore_Article_Author( $this->env );
			$relations	= $model->getAll( ['authorId' => $session->get( 'catalog_bookstore_search_authorId' )] );
			foreach( $relations as $relation )
				$articles[]	= $relation->articleId;
		}
		if( $articles ){
			$articles	= array_unique( $articles );
			$model		= new Model_Catalog_Bookstore_Article( $this->env );
			$total		= count( $articles );
			$offset		= $offset >= $total ? 0 : $offset;
			$articles	= $model->getAll( ['articleId' => $articles], ['articleId' => 'DESC'], [$offset, $limit] );
		}

		if( NULL === ( $authors = $cache->get( 'catalog.bookstore.search.authors' ) ) ){
			$authors	= $this->logic->getAuthors( [], ['lastname' => 'ASC', 'firstname' => 'ASC'] );
			$cache->set( 'catalog.bookstore.search.authors', $authors );
		}

		if( NULL === ( $categories = $cache->get( 'catalog.bookstore.search.categories' ) ) ){
			$conditions	= ['parentId' => 0, 'visible' => 1];
			$categories	= $this->logic->getCategories( $conditions, ['label_de' => 'ASC'] );
			$cache->set( 'catalog.bookstore.search.categories', $categories );
		}

		$this->addData( 'total', $total );
		$this->addData( 'articles', $articles );
		$this->addData( 'page', $page );
		$this->addData( 'authors', $authors );
		$this->addData( 'categories', $categories );
		$this->addData( 'limit', $limit );
	}

	/**
	 *	@param		string|NULL $tagId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function tag( string $tagId = NULL ): void
	{
		if( !$tagId )
			$this->restart( NULL, TRUE );
		if( !( $tag = $this->logic->getArticleTag( $tagId ) ) )
			$this->restart( NULL, TRUE );

		$articles	= $this->logic->getArticlesFromTags( [$tag->tag] );

		$this->addData( 'tag', $tag );
		$this->addData( 'tagId', $tagId );
		$this->addData( 'articles', $articles );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog_Bookstore( $this->env );
		$this->bridge		= new Logic_ShopBridge( $this->env );
		$this->bridgeId		= $this->bridge->getBridgeId( 'Bookstore' );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
	}
}
