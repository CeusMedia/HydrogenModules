<?php
class Controller_Manage_Catalog_Bookstore_Article extends Controller_Manage_Catalog_Bookstore
{
	protected string $sessionPrefix		= 'module.manage_catalog_bookstore_article.filter.';

	/**
	 *	@return		void
	 */
	public function add(): void
	{
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'add' );
			$data		= $this->request->getAll();
			if( 0 === strlen( trim( $data['title'] ) ) )
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

	/**
	 *	@param		string		$articleId
	 *	@return		void
	 */
	public function addAuthor( string $articleId ): void
	{
		$authorId	= $this->request->get( 'authorId' );
		$editor		= $this->request->get( 'editor' );
		$this->logic->addAuthorToArticle( $articleId, $authorId, $editor );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	/**
	 *	@param		string		$articleId
	 *	@return		void
	 */
	public function addCategory( string $articleId ): void
	{
		$categoryId		= $this->request->get( 'categoryId' );
		$volume			= $this->request->get( 'volume' );
		$this->logic->addCategoryToArticle( $articleId, $categoryId, $volume );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	/**
	 *	@param		string		$articleId
	 *	@return		void
	 */
	public function addDocument( string $articleId ): void
	{
		$upload	= $this->request->get( 'document' );
		$title		= $this->request->get( 'title' );
		$fileName	= $upload['name'] ?? '';
		$words		= (object) $this->getWords( 'upload' );
		if( '' !== $fileName ){
			if( 0 === strlen( trim( $title ) ) )
				$title	= pathinfo( $fileName, PATHINFO_FILENAME );

			$extensions	= $this->moduleConfig->get( 'article.document.extensions' );
			$logic		= new Logic_Upload( $this->env );
			try{
				$logic->setUpload( $upload );
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

	/**
	 *	@param		string			$articleId
	 *	@param		string|NULL		$tag
	 *	@return		void
	 */
	public function addTag( string $articleId, ?string $tag = NULL ): void
	{
		$tag	= $tag ?: $this->request->get( 'tag' );
		$this->logic->addArticleTag( $articleId, $tag );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	/**
	 *	@param		string		$articleId
	 *	@return		void
	 */
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

	/**
	 *	@param		$reset
	 *	@return		void
	 */
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

	/**
	 *	@return		void
	 */
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

	/**
	 *	@param		string		$articleId
	 *	@param		string		$authorId
	 *	@return		void
	 */
	public function removeAuthor( string $articleId, string $authorId ): void
	{
		$this->logic->removeAuthorFromArticle( $articleId, $authorId );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	/**
	 *	@param		string		$articleId
	 *	@param		string		$categoryId
	 *	@return		void
	 */
	public function removeCategory( string $articleId, string $categoryId ): void
	{
		$this->logic->removeCategoryFromArticle( $articleId, $categoryId );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	/**
	 *	@param		string		$articleId
	 *	@return		void
	 */
	public function removeCover( string $articleId ): void
	{
		$this->logic->removeArticleCover( $articleId );
		$this->restart( 'edit/'.$articleId, TRUE );
	}

	/**
	 *	@param		string		$articleId
	 *	@param		string		$articleDocumentId
	 *	@return		void
	 */
	public function removeDocument( string $articleId, string $articleDocumentId ): void
	{
		$this->logic->removeArticleDocument( $articleDocumentId );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	/**
	 *	@param		string		$articleId
	 *	@param		string		$articleTagId
	 *	@return		void
	 */
	public function removeTag( string $articleId, string $articleTagId ): void
	{
		$this->logic->removeArticleTag( $articleTagId );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	/**
	 *	@param		string		$articleId
	 *	@param		string		$authorId
	 *	@param		$role
	 *	@return		void
	 */
	public function setAuthorRole( string $articleId, string $authorId, $role ): void
	{
		$this->logic->setArticleAuthorRole( $articleId, $authorId, $role );
		$this->restart( 'manage/catalog/bookstore/article/edit/'.$articleId );
	}

	/**
	 *	@param		string		$articleId
	 *	@return		void
	 */
	public function setCover( string $articleId ): void
	{
		$file		= $this->request->get( 'image' );
		$name		= $this->request->get( 'name', '' );
		$words		= (object) $this->getWords( 'upload' );
		if( '' !== $name ){
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

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		parent::__onInit();

		$pathFrontendContents	= $this->frontend->getPath( 'contents' );
		$paths					= $this->moduleConfig->getAll( 'path.', TRUE );
		$this->addData( 'pathAuthors', $pathFrontendContents.$paths->get( 'authors' ) );
		$this->addData( 'pathCovers', $pathFrontendContents.$paths->get( 'covers' ) );
		$this->addData( 'pathDocuments', $pathFrontendContents.$paths->get( 'documents' ) );

		if( !$this->session->get( $this->sessionPrefix.'order' ) )
			$this->session->set( $this->sessionPrefix.'order', 'createdAt:DESC' );
	}

	/**
	 *	@return		array
	 */
	protected function getFilteredArticles(): array
	{
		$filters	= $this->session->getAll( 'module.manage_catalog_bookstore_article.filter.' );
		$orders		= [];
		$conditions	= [];
		$articleIds	= [];
		foreach( $filters as $filterKey => $filterValue ){
			switch( $filterKey ){
				case 'author':
					if( 0 !== strlen( trim( $filterValue ) ) ){
						$filterValue	= str_replace( ' ', '', $filterValue );
						$find			= ['CONCAT(firstname, lastname)' => '%'.str_replace( " ", "%", $filterValue ).'%'];
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
					if( 0 !== strlen( trim( $filterValue ) ) ){
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
					if( 0 !== strlen( $filterValue ) )
						$conditions[$filterKey]	= str_replace( ['*', ' ',], "%", $filterValue );
					break;
				case 'new':
				case 'status':
					if( 0 !== strlen( $filterValue ) )
						$conditions[$filterKey]	= (int) $filterValue;
					break;
				case 'cover':
					if( $filterValue )
						$conditions[$filterKey]	= "IS NOT NULL";
					break;
				case 'id':
					if( 0 !== strlen( $filterValue ) )
						$articleIds	= [$filterValue];
					break;
				case 'term':
					if( 0 !== strlen( $filterValue ) )
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
		$offset		= $filters['offset'] ?? 0;
		return $this->logic->getArticles( $conditions, $orders, [$offset, 50] );
	}
}
