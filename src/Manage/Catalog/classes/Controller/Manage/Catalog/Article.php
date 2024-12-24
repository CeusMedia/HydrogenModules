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

	public function addAuthor( int|string $articleId ): void
	{
		$authorId	= $this->request->get( 'authorId' );
		$editor		= $this->request->get( 'editor' );
		$this->logic->addAuthorToArticle( $articleId, $authorId, $editor );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function addCategory( int|string $articleId ): void
	{
		$categoryId		= $this->request->get( 'categoryId' );
		$volume			= $this->request->get( 'volume' );
		$this->logic->addCategoryToArticle( $articleId, $categoryId, $volume );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function addDocument( int|string $articleId ): void
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

	/**
	 *	@param		int|string		$articleId
	 *	@param		string|NULL		$tag
	 *	@return		void
	 */
	public function addTag( int|string $articleId, string $tag = NULL ): void
	{
		$tag	= $tag ?: $this->request->get( 'tag' );
		$this->logic->addArticleTag( $articleId, $tag );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	/**
	 *	@param		int|string		$articleId
	 *	@return		void
	 */
	public function edit( int|string $articleId ): void
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

	/**
	 *	@return		void
	 */
	public function index(): void
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
	 *	@param		int|string		$articleId
	 */
	public function remove( int|string $articleId ): void
	{
		$this->logic->removeArticle( $articleId );
		$this->restart( NULL, TRUE );
	}

	public function removeAuthor( int|string $articleId, int|string $authorId ): void
	{
		$this->logic->removeAuthorFromArticle( $articleId, $authorId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeCategory( int|string $articleId, int|string $categoryId ): void
	{
		$this->logic->removeCategoryFromArticle( $articleId, $categoryId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeDocument( int|string $articleId, int|string $articleDocumentId ): void
	{
		$this->logic->removeArticleDocument( $articleDocumentId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeTag( int|string $articleId, int|string $articleTagId ): void
	{
		$this->logic->removeArticleTag( $articleTagId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	/**
	 *	@param		int|string		$articleId
	 *	@param		int|string		$authorId
	 *	@param		int|string		$role
	 *	@return		void
	 */
	public function setAuthorRole( int|string $articleId, int|string $authorId, int|string $role ): void
	{
		$this->logic->setArticleAuthorRole( $articleId, $authorId, $role );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	/**
	 *	@param		int|string		$articleId
	 *	@return		void
	 */
	public function setCover( int|string $articleId ): void
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
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

	protected function getFilteredArticles(): array
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
		$offset		= $filter['offset'] ?? 0;
		return $this->logic->getArticles( $conditions, $orders, [$offset, 50] );
	}
}
