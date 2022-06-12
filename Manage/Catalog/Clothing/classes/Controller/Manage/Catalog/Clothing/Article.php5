<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Catalog_Clothing_Article extends Controller
{
	protected $sessionPrefix	= 'filter_manage_catalog_clothing_';

	public function add()
	{
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$articleId	= $this->modelArticle->add( $data );
			$this->messenger->noteSuccess( 'Added.' );
			$this->restart( 'edit/'.$articleId, TRUE );
		}
	}

	public function edit( $articleId )
	{
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			if( class_exists( 'Logic_Localization' ) ){							//  localization module is installed
				$idTitle	= 'catalog.clothing.article.'.$articleId.'-title';
				$idContent	= 'catalog.clothing.article.'.$articleId.'-description';
				$title		= $this->request->get( 'title' );
				$content	= $this->request->get( 'description' );
				if( $title && $this->localization->translate( $idTitle, NULL, $title ) )
					unset( $data['title'] );
				if( $content && $this->localization->translate( $idContent, NULL, $content ) )
					unset( $data['description'] );
			}
			$this->modelArticle->edit( $articleId, $data );
			$this->messenger->noteSuccess( 'Saved.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'article', $this->modelArticle->get( $articleId ) );
	}

	public function filter( $reset = NULL )
	{
		if( $reset ){
			$this->session->remove( $this->sessionPrefix.'language' );
			$this->session->remove( $this->sessionPrefix.'categoryId' );
			$this->session->remove( $this->sessionPrefix.'size' );
			$this->session->remove( $this->sessionPrefix.'limit' );
		}
		else{
			$this->session->set( $this->sessionPrefix.'language', $this->request->get( 'language' ) );
			$this->session->set( $this->sessionPrefix.'categoryId', $this->request->get( 'categoryId' ) );
			$this->session->set( $this->sessionPrefix.'size', $this->request->get( 'size' ) );
			$this->session->set( $this->sessionPrefix.'limit', $this->request->get( 'limit' ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 )
	{
		$filterCategoryId	= $this->session->get( $this->sessionPrefix.'categoryId' );
		$filterSize			= $this->session->get( $this->sessionPrefix.'size' );
		$filterLimit		= $this->session->get( $this->sessionPrefix.'limit' );

		$conditions			= [];
		if( $filterCategoryId )
			$conditions['categoryId']	= $filterCategoryId;
		if( $filterSize )
			$conditions['size']			= $filterSize;

		$limits		= array( $page * $filterLimit, $filterLimit );

		$total		= $this->modelArticle->count( $conditions );
		$articles	= $this->modelArticle->getAll( $conditions, array(), $limits );
		foreach( $articles as $article )
			$article	= $this->translateArticle( $article );

		$this->addData( 'articles', $articles );
		$this->addData( 'categories', $this->modelCategory->getAll() );
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'filterCategoryId', $filterCategoryId );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'filterSize', $filterSize );
	}

	public function remove( $articleId )
	{
		$this->addData( 'article', $this->modelArticle->get( $articleId ) );
		$this->modelArticle->remove( $articleId );
		$this->messenger->noteSuccess( 'Removed.' );
		$this->restart( NULL, TRUE );
	}

	public function setImage( $articleId, $remove = NULL )
	{
		$article	= $this->modelArticle->get( $articleId );
		if( $remove ){
			unlink( $this->path.$article->image );
			$this->modelArticle->edit( $article->articleId, array(
				'image'			=> NULL,
				'modifiedAt'	=> time(),
			) );
			$this->restart( 'edit/'.$articleId, TRUE );
		}
		if( $this->request->get( 'upload' ) ){
			try{
				$logicUpload	= new Logic_Upload( $this->env );
				$logicUpload->setUpload( $this->request->get( 'upload' ) );
				$logicUpload->sanitizeFileName();
				$logicUpload->checkSize( Logic_Upload::getMaxUploadSize() );
				$logicUpload->checkVirus( TRUE );
				$extension		= $logicUpload->getExtension();
				$fileName		= $logicUpload->sanitizeFileName();
				$fileName		= Logic_Upload::sanitizeFileNameStatic( $article->title );
				if( $logicUpload->getError() ){
					$helper	= new View_Helper_UploadError( $this->env );
					$helper->setUpload( $logicUpload );
					$this->messenger->noteError( $helper->render() );
				}
				else{
					if( $article->image )
						unlink( $this->path.$article->image );
					$fileName	= $article->articleId.'-'.$fileName.'.'.$extension;
					$logicUpload->saveTo( $this->path.$fileName );
					$this->modelArticle->edit( $article->articleId, array(
						'image'			=> $fileName,
						'modifiedAt'	=> time(),
					) );
//					$this->messenger->noteSuccess( $words->successDocumentUploaded, $logicUpload->getFileName() );
				}
			}
			catch( Exception $e ){
				$messenger->noteFailure( $words->errorUploadFailed );
			}
		}
		$this->restart( 'edit/'.$articleId, TRUE );
	}

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->modelArticle		= new Model_Catalog_Clothing_Article( $this->env );
		$this->modelCategory	= new Model_Catalog_Clothing_Category( $this->env );

		$this->frontend			= Logic_Frontend::getInstance( $this->env );
		$this->languages		= $this->frontend->getLanguages();
		$this->defaultLanguage	= $this->frontend->getDefaultLanguage();
		if( !$this->session->get( $this->sessionPrefix.'language' ) ){
			if( $this->frontend->getDefaultLanguage() )
				$this->session->set( $this->sessionPrefix.'language', $this->defaultLanguage );
		}
		$this->localization		= new Logic_Localization( $this->env );
		$this->localization->setLanguage( $this->session->get( $this->sessionPrefix.'language' ) );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'languages', $this->languages );
		$this->addData( 'language', $this->session->get( $this->sessionPrefix.'language' ) );

		$this->categoryMap		= [];
		$categories		= $this->modelCategory->getAll( array(), array( 'title' => 'ASC' ) );
		foreach( $categories as $item )
			$this->categoryMap[$item->categoryId]	= $item;
		$this->addData( 'categoryMap', $this->categoryMap );

		if( !$this->session->get( $this->sessionPrefix.'limit' ) )
			$this->session->set( $this->sessionPrefix.'limit', 10 );

		$logicFrontend			= Logic_Frontend::getInstance( $this->env );
		$this->path				= $logicFrontend->getPath( 'images' ).'products/';
		$this->addData( 'path', $this->path );
	}

	protected function translateArticle( $article )
	{
		if( $this->session->get( $this->sessionPrefix.'language' ) === $this->defaultLanguage )
			return $article;
		$idTitle				= 'catalog.clothing.article.'.$article->articleId.'-title';
		$idDescription			= 'catalog.clothing.article.'.$article->articleId.'-description';
		$article->title			= $this->localization->translate( $idTitle, $article->title );
		$article->description	= $this->localization->translate( $idDescription, $article->description );
		return $article;
	}
}
