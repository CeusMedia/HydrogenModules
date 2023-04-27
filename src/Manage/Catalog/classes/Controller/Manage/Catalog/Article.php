<?php

use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Manage_Catalog_Article extends Controller
{
	protected $frontend;
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;
	protected $sessionPrefix;

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		object		$env
	 *	@param		object		$context
	 *	@param		unknown		$module
	 *	@param		unknown		$arguments
	 *	@return		void
	 *	@todo		 code doc
	 */
	public static function ___onTinyMCE_getImageList( Environment $env, $context, $module, $arguments = [] )
	{
		$cache		= $env->getCache();
		if( !( $list = $cache->get( 'catalog.tinymce.images.articles' ) ) ){
			$logic		= new Logic_Catalog( $env );
			$frontend	= Logic_Frontend::getInstance( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );				//  focus module configuration
			$pathCovers	= $frontend->getPath( 'contents' ).$config->get( 'path.covers' );			//  get path to cover images
			$pathCovers	= substr( $pathCovers, strlen( $frontend->getPath() ) );					//  strip frontend base path
			$list       = [];
			$conditions	= ['cover' => '> 0'];
			$orders		= ['articleId' => 'DESC'];
			foreach( $logic->getArticles( $conditions, $orders, [0, 200] ) as $item ){
				$id		= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$list[] = (object) array(
					'title'	=> TextTrimmer::trimCentric( $item->title, 60 ),
					'value'	=> $pathCovers.$id.'__'.$item->cover,
				);
			}
			$cache->set( 'catalog.tinymce.images.articles', $list );
		}
		$context->list  = array_merge( $context->list, array( (object) array(		//  extend global collection by submenu with list of items
			'title'	=> 'Veröffentlichungen:',									//  label of submenu @todo extract
			'menu'	=> array_values( $list ),									//  items of submenu
		) ) );
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		object		$env
	 *	@param		object		$context
	 *	@param		unknown		$module
	 *	@param		unknown		$arguments
	 *	@return		void
	 *	@todo		 code doc
	 */
	public static function ___onTinyMCE_getLinkList( Environment $env, $context, $module, $arguments = [] )
	{
		$cache		= $env->getCache();
		$logic		= new Logic_Catalog( $env );
		$frontend	= Logic_Frontend::getInstance( $env );
		$config		= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );

		if( !( $articles = $cache->get( 'catalog.tinymce.links.articles' ) ) ){
			$orders		= ['articleId' => 'DESC'];
			$articles	= $logic->getArticles( [], $orders, [0, 200] );
			foreach( $articles as $nr => $item ){
/*				$category	= $logic->getCategoryOfArticle( $article->articleId );
				if( $category->volume )
					$item->title	.= ' - Band '.$category->volume;
*/				$articles[$nr]	= (object) array(
					'title'	=> TextTrimmer::trimCentric( $item->title, 80 ),
					'value'	=> $logic->getArticleUri( $item ),
				);
			}
			$cache->set( 'catalog.tinymce.links.articles', $articles );
		}
		$context->list	= array_merge( $context->list, array( (object) array(
			'title'	=> 'Veröffentlichungen:',
			'menu'	=> array_values( $articles ),
		) ) );

		if( !( $documents = $cache->get( 'catalog.tinymce.links.documents' ) ) ){
			$pathDocs	= $frontend->getPath( 'contents' ).$config->get( 'path.documents' );
			$documents	= $logic->getDocuments( [], ['articleDocumentId' => 'DESC'], [0, 200] );
			foreach( $documents as $nr => $item ){
				$id				= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$article		= $logic->getArticle( $item->articleId );
				$documents[$nr]	= (object) array(
					'title'	=> TextTrimmer::trimCentric( $article->title, 40 ).' - '.$item->title,
					'value'	=> $pathDocs.$id.'_'.$item->url,
				);
			}
			$cache->set( 'catalog.tinymce.links.documents', $documents );
		}
		$context->list	= array_merge( $context->list, array( (object) array(
			'title'	=> 'Dokuments:',
			'menu'	=> array_values( $documents ),
		) ) );
	}

	public function add()
	{
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'add' );
			$data		= $this->request->getAll();
			if( !strlen( trim( $data['title'] ) ) )
				$this->messenger->noteError( $words->msgErrorTitleMissing );
			else{
				$articleId	= $this->logic->addArticle( $data );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$this->restart( 'manage/catalog/article/edit/'.$articleId );
			}
		}
		$model		= new Model_Catalog_Article( $this->env );
		$article	= [];
		foreach( $model->getColumns() as $column )
			$article[$column]	= $this->request->get( $column );
		$this->addData( 'article', (object) $article );
		$this->addData( 'articles', $this->getFilteredArticles() );
	}

	public function addAuthor( $articleId )
	{
		$authorId	= $this->request->get( 'authorId' );
		$editor		= $this->request->get( 'editor' );
		$this->logic->addAuthorToArticle( $articleId, $authorId, $editor );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function addCategory( $articleId )
	{
		$categoryId		= $this->request->get( 'categoryId' );
		$volume			= $this->request->get( 'volume' );
		$this->logic->addCategoryToArticle( $articleId, $categoryId, $volume );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function addDocument( $articleId )
	{
		$file		= $this->request->get( 'document' );
		$title		= $this->request->get( 'title' );
		$words		= (object) $this->getWords( 'upload' );
		if( isset( $file['name'] ) && !empty( $file['name'] ) ){
			if( !strlen( trim( $title ) ) )
				$title	= $file['name'];
//				$this->messenger->noteError( $words->msgErrorTitleMissing );
			if( $file['error']	!= 0 ){
				$handler	= new UploadErrorHandler();
				$handler->setMessages( $this->getWords( 'uploadErrors' ) );
				$this->messenger->noteError( $file['error'].': '.$handler->getErrorMessage( $file['error'] ) );
			}
			else{
				/*  --  CHECK NEW DOCUMENT  --  */
				$info		= pathinfo( $file['name'] );
				$extension	= $info['extension'];
				$extensions	= ['jpe', 'jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'doc', 'ppt', 'odt', 'ods'];
				if( !in_array( strtolower( $extension ), $extensions ) )
					$this->messenger->noteError( $words->msgErrorExtensionInvalid );
				else{
					try{
						$this->logic->addArticleDocument( $articleId, $file, $title );										//  set newer image
					}
					catch( Exception $e ){
						$this->messenger->noteFailure( $words->msgErrorUpload );
					}
				}
			}
		}
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function addTag( $articleId, $tag = NULL )
	{
		$tag	= $tag ? $tag : $this->request->get( 'tag' );
		$this->logic->addArticleTag( $articleId, $tag );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function ajaxGetTags()
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

	public function ajaxGetIsns()
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

	public function ajaxSetTab( $tabKey )
	{
		$this->session->set( 'manage.catalog.article.tab', $tabKey );
		exit;
	}

	public function edit( $articleId )
	{
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'edit' );
			$data	= $this->request->getAll();
			if( !strlen( trim( $data['title'] ) ) )
				$this->messenger->noteError( $words->msgErrorTitleMissing );
			else{
				$this->logic->editArticle( $articleId, $data );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$this->restart( 'manage/catalog/article/edit/'.$articleId );
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
		$this->addData( 'filters', $this->session->getAll( 'module.manage_catalog_article.filter.' ) );
	}

	public function filter( $reset = FALSE )
	{
		$this->session->set( $this->sessionPrefix.'term', trim( $this->request->get( 'term' ) ) );
		$this->session->set( $this->sessionPrefix.'author', trim( $this->request->get( 'author' ) ) );
		$this->session->set( $this->sessionPrefix.'tag', trim( $this->request->get( 'tag' ) ) );
		$this->session->set( $this->sessionPrefix.'isn', trim( $this->request->get( 'isn' ) ) );
		$this->session->set( $this->sessionPrefix.'new', $this->request->get( 'new' ) );
		$this->session->set( $this->sessionPrefix.'cover', $this->request->get( 'cover' ) );
		$this->session->set( $this->sessionPrefix.'order', $this->request->get( 'order' ) );
		$this->session->set( $this->sessionPrefix.'status', $this->request->get( 'status' ) );
		if( $reset ){
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


	public function index()
	{
		$articles	= $this->getFilteredArticles();
		if( count( $articles ) === 1 ){
			$article	= array_pop( $articles );
			$this->restart( './manage/catalog/article/edit/'.$article->articleId );
		}
		$this->addData( 'articles', $articles );
		$this->addData( 'filters', $this->session->getAll( 'module.manage_catalog_article.filter.' ) );
	}

	/**
	 *	Removes article with images and relations to categories and authors.
	 *	@access		public
	 *	@param		$articleId
	 */
	public function remove( $articleId )
	{
		$this->logic->removeArticle( $articleId );
		$this->restart( NULL, TRUE );
	}

	public function removeAuthor( $articleId, $authorId )
	{
		$this->logic->removeAuthorFromArticle( $articleId, $authorId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeCategory( $articleId, $categoryId )
	{
		$this->logic->removeCategoryFromArticle( $articleId, $categoryId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeDocument( $articleId, $articleDocumentId )
	{
		$this->logic->removeArticleDocument( $articleDocumentId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeTag( $articleId, $articleTagId )
	{
		$this->logic->removeArticleTag( $articleTagId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function setAuthorRole( $articleId, $authorId, $role )
	{
		$this->logic->setArticleAuthorRole( $articleId, $authorId, $role );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function setCover( $articleId )
	{
		$file		= $this->request->get( 'image' );
		$words		= (object) $this->getWords( 'upload' );
		if( isset( $file['name'] ) && !empty( $file['name'] ) ){
			if( $file['error']	!= 0 ){
				$handler	= new UploadErrorHandler();
				$handler->setMessages( $this->getWords( 'uploadErrors' ) );
				$this->messenger->noteError( $file['error'].': '.$handler->getErrorMessage( $file['error'] ) );
			}
			else{
				/*  --  CHECK NEW IMAGE  --  */
				$info		= pathinfo( $file['name'] );
				$extension	= $info['extension'];
				$extensions	= ['jpe', 'jpeg', 'jpg', 'png', 'gif'];
				if( !in_array( strtolower( $extension ), $extensions ) )
					$this->messenger->noteError( $words->msgErrorExtensionInvalid );
				else{
					try{
						$this->logic->removeArticleCover( $articleId );
						$this->logic->addArticleCover( $articleId, $file );					//  set newer image
					}
					catch( Exception $e ){
						$this->messenger->noteFailure( $words->msgErrorUpload );
					}
				}
			}
		}
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Article::init start' );
		$this->messenger		= $this->env->getMessenger();
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->logic			= new Logic_Catalog( $this->env );
		$this->frontend			= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
		$this->sessionPrefix	= 'module.manage_catalog_article.filter.';
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'pathAuthors', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.authors' ) );
		$this->addData( 'pathCovers', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.covers' ) );
		$this->addData( 'pathDocuments', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.documents' ) );

		if( !$this->session->get( $this->sessionPrefix.'order' ) )
				$this->session->set( $this->sessionPrefix.'order', 'createdAt:DESC' );
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Article::init done' );
	}

	protected function getFilteredArticles()
	{
		$filters	= $this->session->getAll( 'module.manage_catalog_article.filter.' );
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
		$offset		= isset( $filter['offset'] ) ? $filter['offset'] : 0;
		$articles	= $this->logic->getArticles( $conditions, $orders, [$offset, 50] );
		return $articles;
	}
}
