<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Bookstore_Article extends Controller
{
	protected Logic_Frontend $frontend;
	protected Logic_Catalog_Bookstore $logic;
	protected MessengerResource $messenger;
	protected Dictionary $request;
	protected Dictionary $session;
	protected string $sessionPrefix;

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env
	 *	@param		object			$context
	 *	@param		object			$module
	 *	@param		array			$payload
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@todo		kriss: code doc
	 */
	public static function ___onTinyMCE_getImageList( Environment $env, object $context, object $module, array & $payload ): void
	{
		$cache		= $env->getCache();
		if( 1 || !( $list = $cache->get( 'catalog.tinymce.images.catalog.bookstore.articles' ) ) ){
			$logic		= new Logic_Catalog_Bookstore( $env );
			$frontend	= Logic_Frontend::getInstance( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );				//  focus module configuration
			$pathCovers	= $frontend->getPath( 'contents' ).$config->get( 'path.covers' );			//  get path to cover images
			$pathCovers	= substr( $pathCovers, strlen( $frontend->getPath() ) );					//  strip frontend base path
			$list		= [];
			$conditions	= ['cover' => '> 0'];
			$orders		= ['title' => 'ASC'];
			foreach( $logic->getArticles( $conditions, $orders, [0, 200] ) as $item ){
				$id		= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$list[] = (object) [
					'title'	=> TextTrimmer::trimCentric( $item->title, 60 ),
					'value'	=> 'file/bookstore/article/m/'.$item->cover,
				];
			}
			$cache->set( 'catalog.tinymce.images.catalog.bookstore.articles', $list );
		}
		$context->list	= array_merge( $context->list, [(object) [				//  extend global collection by submenu with list of items
			'title'	=> 'Veröffentlichungen:',									//  label of submenu @todo extract
			'menu'	=> array_values( $list ),									//  items of submenu
		]] );
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env
	 *	@param		object			$context
	 *	@param		object			$module
	 *	@param		array			$payload
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@todo		kriss: code doc
	 */
	public static function ___onTinyMCE_getLinkList( Environment $env, object $context, object $module, array & $payload ): void
	{
		$cache		= $env->getCache();
		$logic		= new Logic_Catalog_Bookstore( $env );
		$frontend	= Logic_Frontend::getInstance( $env );
		$config		= $env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );

		if( !( $articles = $cache->get( 'catalog.tinymce.links.catalog.bookstore.articles' ) ) ){
			$orders		= ['articleId' => 'DESC'];
			$limits		= [];//array( 0, 200 );
			$articles	= $logic->getArticles( [], $orders, $limits );
			foreach( $articles as $nr => $item ){
/*				$category	= $logic->getCategoryOfArticle( $article->articleId );
				if( $category->volume )
					$item->title	.= ' - Band '.$category->volume;
*/				$articles[$nr]	= (object) [
					'title'	=> TextTrimmer::trimCentric( $item->title, 80 ),
					'value'	=> $logic->getArticleUri( $item ),
				];
			}
			$cache->set( 'catalog.tinymce.links.catalog.bookstore.articles', $articles );
		}
		$words	= $env->getLanguage()->getWords( 'manage/catalog/bookstore' );
		$context->list	= array_merge( $context->list, [(object) [
			'title'	=> $words['tinymce-menu-links']['articles'],
			'menu'	=> array_values( $articles ),
		]] );

		if( 1 ||  !( $documents = $cache->get( 'catalog.tinymce.links.catalog.bookstore.documents' ) ) ){
			$pathDocs	= $frontend->getPath( 'contents' ).$config->get( 'path.documents' );
			$limits		= [];//array( 0, 200 );
			$orders		= ['articleDocumentId' => 'DESC'];
			$documents	= $logic->getDocuments( [], $orders, $limits );
			foreach( $documents as $nr => $item ){
				$id				= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$article		= $logic->getArticle( $item->articleId, FALSE );
				if( $article )
					$documents[$nr]	= (object) [
//					'title'	=> TextTrimmer::trimCentric( $article->title, 40 ).' - '.$item->title,
					'title'	=> $article->title.' - '.$item->title,
					'value'	=> 'file/bookstore/document/'.$item->url,
				];
			}
			$cache->set( 'catalog.tinymce.links.catalog.bookstore.documents', $documents );
		}
		$context->list	= array_merge( $context->list, [(object) [
			'title'	=> $words['tinymce-menu-links']['documents'],
			'menu'	=> array_values( $documents ),
		]] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'add' );
			$data		= $this->request->getAll();
			if( !strlen( trim( $data['title'] ) ) )
				$this->messenger->noteError( $words->msgErrorTitleMissing );
			else{
				$articleId	= $this->logic->addArticle( $data );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
			}
		}
		$model		= new Model_Catalog_Bookstore_Article( $this->env );
		$article	= [];
		foreach( $model->getColumns() as $column )
			$article[$column]	= $this->request->get( $column );
		$this->addData( 'article', (object) $article );
		$this->addData( 'articles', $this->getFilteredArticles() );
	}

	public function addAuthor( string $articleId ): void
	{
		$authorId	= $this->request->get( 'authorId' );
		$editor		= $this->request->get( 'editor' );
		$this->logic->addAuthorToArticle( $articleId, $authorId, $editor );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function addCategory( string $articleId ): void
	{
		$categoryId		= $this->request->get( 'categoryId' );
		$volume			= $this->request->get( 'volume' );
		$this->logic->addCategoryToArticle( $articleId, $categoryId, $volume );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function addDocument( string $articleId ): void
	{
		$file		= $this->request->get( 'document' );
		$title		= $this->request->get( 'title' );
		$words		= (object) $this->getWords( 'upload' );
		if( isset( $file['name'] ) && !empty( $file['name'] ) ){
			if( !strlen( trim( $title ) ) )
				$title	= pathinfo( $file['name'], PATHINFO_FILENAME );

			$extensions	= $this->moduleConfig->get( 'article.document.extensions' );
			$logic		= new Logic_Upload( $this->env );
			try{
				$logic->setUpload( $file );
				$logic->checkExtension( preg_split( '/\s*,\s*/', $extensions ), TRUE );
//				$logic->checkIsImage( TRUE );
				$logic->checkSize( $this->moduleConfig->get( 'article.document.size' )."M", TRUE );

//				$logic->sanitizeFileName();
				if( $logic->getError() ){
					$helper	= new View_Helper_UploadError( $this->env );
					$helper->setUpload( $logic );
					$this->messenger->noteError( $helper->render() );
				}
				else{
					$targetFile		= uniqid().'.'.$logic->getExtension( TRUE );
					$logic->saveTo( $targetFile );
					$this->logic->addArticleDocument( $articleId, $targetFile, $title, $logic->getMimeType() );
					@unlink( $targetFile );															//  remove original
				}
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( 'Upload Error: '.$e->getMessage() );
			}
		}
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function addTag( string $articleId, ?string $tag = NULL ): void
	{
		$tag	= $tag ?: $this->request->get( 'tag' );
		$this->logic->addArticleTag( $articleId, $tag );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function ajaxGetTags(): void
	{
		$startsWith	= $this->request->get( 'query' );
		$conditions	= ['tag' => $startsWith.'%'];
		$orders		= ['tag' => 'ASC'];
		$limits		= [0, 10];
		$tags		= $this->logic->getTags( $conditions, $orders, $limits );
		$list		= [];
		foreach( $tags as $tag )
			$list[$tag->tag]	= $tag->tag;
		ksort( $list );
		$json	= json_encode( array_keys( $list ) );
		header( 'Content-Type: application/json' );
		header( 'Content-Length: '.strlen( $json ) );
		print( $json );
		exit;
	}

	public function ajaxGetIsns(): void
	{
		$startsWith	= $this->request->get( 'query' );
		$conditions	= ['isn' => $startsWith.'%'];
		$orders		= ['isn' => 'ASC'];
		$limits		= [0, 10];
		$articles	= $this->logic->getArticles( $conditions, $orders, $limits );
		$list		= [];
		foreach( $articles as $article )
			$list[$article->isn]	= $article->isn;
		ksort( $list );
		$json	= json_encode( array_keys( $list ) );
		header( 'Content-Type: application/json' );
		header( 'Content-Length: '.strlen( $json ) );
		print( $json );
		exit;
	}

	public function ajaxSetTab( string $tabKey ): void
	{
		$this->session->set( 'manage.catalog.bookstore.article.tab', $tabKey );
		exit;
	}

	public function edit( string $articleId ): void
	{
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'edit' );
			$data	= $this->request->getAll();
			if( !strlen( trim( $data['title'] ) ) )
				$this->messenger->noteError( $words->msgErrorTitleMissing );
			else{
				$this->logic->editArticle( $articleId, $data );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
			}
		}
		$this->addData( 'article', $this->logic->getArticle( $articleId ) );
		$this->addData( 'articleAuthors', $this->logic->getAuthorsOfArticle( $articleId ) );
		$this->addData( 'articleTags', $this->logic->getTagsOfArticle( $articleId ) );
		$this->addData( 'articleDocuments', $this->logic->getDocumentsOfArticle( $articleId ) );
		$this->addData( 'articleCategories', $this->logic->getCategoriesOfArticle( $articleId ) );
		$this->addData( 'articles', $this->getFilteredArticles() );
		$this->addData( 'authors', $this->logic->getAuthors( [], ['lastname' => 'ASC'] ) );
		$this->addData( 'categories', $this->logic->getCategories() );
		$this->addData( 'filters', $this->session->getAll( $this->sessionPrefix ) );
	}

	public function filter( $reset = FALSE ): void
	{
		$this->session->set( $this->sessionPrefix.'id', trim( $this->request->get( 'id' ) ) );
		$this->session->set( $this->sessionPrefix.'term', trim( $this->request->get( 'term' ) ) );
		$this->session->set( $this->sessionPrefix.'term', trim( $this->request->get( 'term' ) ) );
		$this->session->set( $this->sessionPrefix.'author', trim( $this->request->get( 'author' ) ) );
		$this->session->set( $this->sessionPrefix.'tag', trim( $this->request->get( 'tag' ) ) );
		$this->session->set( $this->sessionPrefix.'isn', trim( $this->request->get( 'isn' ) ) );
		$this->session->set( $this->sessionPrefix.'new', $this->request->get( 'new' ) );
		$this->session->set( $this->sessionPrefix.'cover', $this->request->get( 'cover' ) );
		$this->session->set( $this->sessionPrefix.'order', $this->request->get( 'order' ) );
		$this->session->set( $this->sessionPrefix.'status', $this->request->get( 'status' ) );
		if( $reset ){
			$this->session->remove( $this->sessionPrefix.'id' );
			$this->session->remove( $this->sessionPrefix.'term' );
			$this->session->remove( $this->sessionPrefix.'author' );
			$this->session->remove( $this->sessionPrefix.'tag' );
			$this->session->remove( $this->sessionPrefix.'isn' );
			$this->session->remove( $this->sessionPrefix.'new' );
			$this->session->remove( $this->sessionPrefix.'cover' );
			$this->session->remove( $this->sessionPrefix.'order' );
			$this->session->remove( $this->sessionPrefix.'status' );
		}
		$this->restart( NULL, TRUE );
	}

	public function index(): void
	{
		$articles	= $this->getFilteredArticles();
		if( count( $articles ) === 1 ){
			$article	= array_pop( $articles );
			$this->restart( './manage/catalog/bookstore/article/edit/'.$article->articleId );
		}
		$this->addData( 'articles', $articles );
		$this->addData( 'filters', $this->session->getAll( 'module.manage_catalog_bookstore_article.filter.' ) );
	}

	/**
	 *	Removes article with images and relations to categories and authors.
	 *	@access		public
	 *	@param		string		$articleId
	 */
	public function remove( string $articleId ): void
	{
		$this->logic->removeArticle( $articleId );
		$this->restart( NULL, TRUE );
	}

	public function removeAuthor( string $articleId, string $authorId ): void
	{
		$this->logic->removeAuthorFromArticle( $articleId, $authorId );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function removeCategory( string $articleId, string $categoryId ): void
	{
		$this->logic->removeCategoryFromArticle( $articleId, $categoryId );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function removeCover( string $articleId ): void
	{
		$this->logic->removeArticleCover( $articleId );
		$this->restart( 'edit/'.$articleId, TRUE );
	}

	public function removeDocument( string $articleId, string $articleDocumentId ): void
	{
		$this->logic->removeArticleDocument( $articleDocumentId );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function removeTag( string $articleId, string $articleTagId ): void
	{
		$this->logic->removeArticleTag( $articleTagId );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function setAuthorRole( string $articleId, string $authorId, $role ): void
	{
		$this->logic->setArticleAuthorRole( $articleId, $authorId, $role );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	public function setCover( string $articleId ): void
	{
		$file		= $this->request->get( 'image' );
		$words		= (object) $this->getWords( 'upload' );
		if( isset( $file['name'] ) && !empty( $file['name'] ) ){
			$extensions	= $this->moduleConfig->get( 'article.image.extensions' );
			$logic		= new Logic_Upload( $this->env );
			try{
				$logic->setUpload( $file );
				$logic->checkExtension( preg_split( '/\s*,\s*/', $extensions ), TRUE );
				$logic->checkIsImage( TRUE );
				$logic->checkSize( $this->moduleConfig->get( 'article.image.size' )."M", TRUE );

//				$logic->sanitizeFileName();
				if( $logic->getError() ){
					$helper	= new View_Helper_UploadError( $this->env );
					$helper->setUpload( $logic );
					$this->messenger->noteError( $helper->render() );
				}
				else{
					$targetFile		= uniqid().'.'.$logic->getExtension( TRUE );
					$logic->saveTo( $targetFile );
					$this->logic->removeArticleCover( $articleId );										//  remove previously set cover
					$this->logic->setArticleCover( $articleId, $targetFile, $logic->getMimeType() );	//  set newer image
					@unlink( $targetFile );																//  remove original
				}
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( 'Upload Error: '.$e->getMessage() );
			}
		}
		$this->restart( 'edit/'.$articleId, TRUE );
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Bookstore_Article::init start' );
		$this->messenger		= $this->env->getMessenger();
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->logic			= new Logic_Catalog_Bookstore( $this->env );
		$this->frontend			= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
		$this->sessionPrefix	= 'module.manage_catalog_bookstore_article.filter.';
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'pathAuthors', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.authors' ) );
		$this->addData( 'pathCovers', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.covers' ) );
		$this->addData( 'pathDocuments', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.documents' ) );

		if( !$this->session->get( $this->sessionPrefix.'order' ) )
				$this->session->set( $this->sessionPrefix.'order', 'createdAt:DESC' );
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Bookstore_Article::init done' );
	}

	protected function getFilteredArticles(): array
	{
		$filters	= $this->session->getAll( 'module.manage_catalog_bookstore_article.filter.' );
		$orders		= [];
		$conditions	= [];
		$articleIds	= [];
		foreach( $filters as $filterKey => $filterValue ){
			switch( $filterKey ){
				case 'author':
					if( strlen( trim( $filterValue ) ) ){
						$filterValue	= str_replace( ' ', '', $filterValue );
						$find			= array( 'CONCAT(firstname, lastname)' => '%'.str_replace( " ", "%", $filterValue ).'%' );
						$authors		= $this->logic->getAuthors( $find );
					if( $authors ){
							$articles	= $this->logic->getArticlesFromAuthors( $authors, TRUE );
							$articleIds	= $articleIds ? array_intersect( $articleIds, $articles ) : $articles;
						}
						else
							$articleIds	= [];
					}
					break;
				case 'tag':
					if( strlen( trim( $filterValue ) ) ){
						$find	= ['tag' => $filterValue];
						$tags	= $this->logic->getTags( $find );
						if( $tags ){
							$list	= [];
							foreach( $tags as $tag )
								$list[]	= $tag->articleId;
							$articleIds	= $articleIds ? array_intersect( $articleIds, $list ) : $list;
						}
						else
							$articleIds	= [];
					}
					break;
				case 'isn':
					if( strlen( $filterValue ) )
						$conditions[$filterKey]	= str_replace( ['*', ' ',], "%", $filterValue );
					break;
				case 'new':
				case 'status':
					if( strlen( $filterValue ) )
						$conditions[$filterKey]	= (int) $filterValue;
					break;
				case 'cover':
					if( $filterValue )
						$conditions[$filterKey]	= "IS NOT NULL";
					break;
				case 'id':
					if( strlen( $filterValue ) )
						$articleIds	= [$filterValue];
					break;
				case 'term':
					if( strlen( $filterValue ) )
						$conditions['title']	= '%'.str_replace( "%", "\%", $filterValue ).'%';
					break;
				case 'order':
					$parts		= explode( ":", $filterValue );
					$orders[$parts[0]]	= strtoupper( $parts[1] );
					break;
	 		}
		}
		if( $articleIds )
			$conditions['articleId']	= $articleIds;
		$offset		= $filter['offset'] ?? 0;
		return $this->logic->getArticles( $conditions, $orders, [$offset, 50] );
	}
}
