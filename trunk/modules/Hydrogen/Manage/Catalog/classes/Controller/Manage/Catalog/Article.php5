<?php
class Controller_Manage_Catalog_Article extends Controller_Manage_Catalog{
	
	public function add(){
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
		$article	= array();
		foreach( $model->getColumns() as $column )
			$article[$column]	= $this->request->get( $column );
		$this->addData( 'article', (object) $article );
		$this->addData( 'articles', $this->getFilteredArticles() );
	}

	public function addAuthor( $articleId ){
		$authorId	= $this->request->get( 'authorId' );
		$editor		= $this->request->get( 'editor' );
		$this->logic->addAuthorToArticle( $articleId, $authorId, $editor );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function addCategory( $articleId ){
		$categoryId		= $this->request->get( 'categoryId' );
		$volume			= $this->request->get( 'volume' );
		$this->logic->addCategoryToArticle( $articleId, $categoryId, $volume );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function addDocument( $articleId ){
		$file		= $this->request->get( 'document' );
		$title		= $this->request->get( 'title' );
		$words		= (object) $this->getWords( 'upload' );
		if( !strlen( trim( $title ) ) )
			$this->messenger->noteError( $words->msgErrorTitleMissing );
		else if( isset( $file['name'] ) && !empty( $file['name'] ) ){
			if( $file['error']	!= 0 ){
				$handler	= new Net_HTTP_UploadErrorHandler();
				$handler->setMessages( $this->getWords( 'uploadErrors' ) );
				$this->messenger->noteError( $file['error'].': '.$handler->getErrorMessage( $file['error'] ) );
			}
			else{
				/*  --  CHECK NEW DOCUMENT  --  */
				$info		= pathinfo( $file['name'] );
				$extension	= $info['extension'];
				$extensions	= array( 'jpe', 'jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'doc', 'ppt', 'odt', 'ods' );
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

	public function addTag( $articleId, $tag ){
		$data	= array(
			'articleId'	=> $articleId,
			'tag'		=> $tag,
		);
		$this->logic->modelArticleTag->add( $data );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function edit( $articleId ){
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
		$this->addData( 'authors', $this->logic->getAuthors( array(), array( 'lastname' => 'ASC' ) ) );
		$this->addData( 'categories', $this->logic->getCategories() );
		$this->addData( 'filters', $this->session->getAll( 'module.manage_catalog_article.filter.' ) );
	}

	public function filter( $reset = FALSE ){
		$sessionPrefix	= 'module.manage_catalog_article.filter.';
		$this->session->set( $sessionPrefix.'term', trim( $this->request->get( 'term' ) ) );
		$this->session->set( $sessionPrefix.'author', trim( $this->request->get( 'author' ) ) );
		$this->session->set( $sessionPrefix.'isn', trim( $this->request->get( 'isn' ) ) );
		$this->session->set( $sessionPrefix.'new', $this->request->get( 'new' ) );
		$this->session->set( $sessionPrefix.'cover', $this->request->get( 'cover' ) );
		$this->session->set( $sessionPrefix.'order', $this->request->get( 'order' ) );
		$this->session->set( $sessionPrefix.'status', $this->request->get( 'status' ) );
#		print_m( $this->request->getAll() );
#		print_m( $this->session->getAll( $sessionPrefix ) );
#		die;
		if( $reset ){
			$this->session->remove( $sessionPrefix.'term' );
			$this->session->remove( $sessionPrefix.'author' );
			$this->session->remove( $sessionPrefix.'isn' );
			$this->session->remove( $sessionPrefix.'new' );
			$this->session->remove( $sessionPrefix.'cover' );
			$this->session->remove( $sessionPrefix.'order' );
			$this->session->remove( $sessionPrefix.'status' );
		}
		if( !$this->session->get( $sessionPrefix.'order' ) )
				$this->session->set( $sessionPrefix.'order', 'timestamp:DESC' );
		$this->restart( NULL, TRUE );
	}

	protected function getFilteredArticles(){
		$filters	= $this->session->getAll( 'module.manage_catalog_article.filter.' );
		$orders		= array();
		$conditions	= array();
		$articleIds	= array();
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
							$articleIds	= array();
					}
					break;
				case 'isn':
					if( strlen( $filterValue ) )
						$conditions[$filterKey]	= '%'.str_replace( array( '*', ' ', ), "%", $filterValue ).'%';
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
		$articles	= $this->logic->getArticles( $conditions, $orders, array( $offset, 50 ) );
		return $articles;
	}
	
	public function index(){
		$articles	= $this->getFilteredArticles();
		if( count( $articles ) === 1 ){
			$article	= array_pop( $articles );
			$this->restart( './manage/catalog/article/edit/'.$article->articleId );
		}
		$this->addData( 'articles', $articles );
		$this->addData( 'filters', $this->session->getAll( 'module.manage_catalog_article.filter.' ) );
	}

	public function remove( $articleId ){
		$this->logic->removeArticle( $articleId );
	}

	public function removeAuthor( $articleId, $authorId ){
		$this->logic->removeAuthorFromArticle( $articleId, $authorId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeCategory( $articleId, $categoryId ){
		$this->logic->removeCategoryFromArticle( $articleId, $categoryId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeDocument( $articleId, $articleDocumentId ){
		$this->logic->removeArticleDocument( $articleDocumentId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function removeTag( $articleId, $articleTagId ){
		$this->logic->modelArticleTag->remove( $articleTagId );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function setAuthorRole( $articleId, $authorId, $role ){
		$this->logic->setArticleAuthorRole( $articleId, $authorId, $role );
		$this->restart( 'manage/catalog/article/edit/'.$articleId );
	}

	public function setCover( $articleId ){
		$file		= $this->request->get( 'image' );
		$words		= (object) $this->getWords( 'upload' );
		if( isset( $file['name'] ) && !empty( $file['name'] ) ){
			if( $file['error']	!= 0 ){
				$handler	= new Net_HTTP_UploadErrorHandler();
				$handler->setMessages( $this->getWords( 'uploadErrors' ) );
				$this->messenger->noteError( $file['error'].': '.$handler->getErrorMessage( $file['error'] ) );
			}
			else{
				/*  --  CHECK NEW IMAGE  --  */
				$info		= pathinfo( $file['name'] );
				$extension	= $info['extension'];
				$extensions	= array( 'jpe', 'jpeg', 'jpg', 'png', 'gif' );
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

	public function ajaxSetTab( $tabKey ){
		$this->session->set( 'manage.catalog.article.tab', $tabKey );
		exit;
	}
}
?>
