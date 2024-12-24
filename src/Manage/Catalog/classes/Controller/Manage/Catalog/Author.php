<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\PartitionSession as HttpPartitionSession;
use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Author extends Controller
{
	protected Logic_Frontend $frontend;
	protected Logic_Catalog $logic;
	protected HttpRequest $request;
	protected HttpPartitionSession $session;
	protected MessengerResource $messenger;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'add' );
			$data	= $this->request->getAll();
			if( !strlen( $data['lastname'] ) )
				$this->messenger->noteError( $words->msgErrorLastnameMissing );
			else{
				$authorId	= $this->logic->addAuthor( $data );
				$this->restart( 'manage/catalog/author/edit/'.$authorId );
			}
		}
		$model		= new Model_Catalog_Author( $this->env );
		$author		= [];
		foreach( $model->getColumns() as $column )
			$author[$column]	= $this->request->get( $column );
		$this->addData( 'author', (object) $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
	}

	/**
	 *	@param		int|string		$authorId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $authorId ): void
	{
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'edit' );
			$data	= $this->request->getAll();
			if( !strlen( $data['lastname'] ) )
				$this->messenger->noteError( $words->msgErrorLastnameMissing );
			else{
				$this->uploadImage( $authorId, $this->request->get( 'image' ) );
				unset( $data['image'] );
				$this->logic->editAuthor( $authorId, $data );
				$this->restart( 'manage/catalog/author/edit/'.$authorId );
			}
		}
		$author		= $this->logic->getAuthor( $authorId );
		$this->addData( 'author', $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
		$this->addData( 'articles', $this->logic->getArticlesFromAuthor( $author ) );
	}

	/**
	 *	@return		void
	 */
	public function index(): void
	{
#		if( !( $authors	= $this->env->getCache()->get( 'authors' ) ) ){
			$authors	= $this->logic->getAuthors();
#			$this->env->getCache()->set( 'authors', $authors );
#		}
		$this->addData( 'authors', $authors );
	}

	/**
	 *	@param		int|string		$authorId
	 *	@return		void
	 */
	public function remove( int|string $authorId ): void
	{
		$words	= $this->getWords( 'remove' );
		if( $this->logic->getArticlesFromAuthor( $authorId ) )
			$this->messenger->noteError( $words->msgErrorNotEmpty );
		else{
			$this->logic->removeAuthor( $authorId );
			$this->restart( 'manage/catalog/author' );
		}
	}

	/**
	 *	@param		int|string		$authorId
	 *	@return		void
	 */
	public function removeImage( int|string $authorId ): void
	{
		$this->logic->removeAuthorImage( $authorId );
		$this->restart( 'manage/catalog/author/edit/'.$authorId );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Author::init start' );
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Catalog( $this->env );
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'pathAuthors', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.authors' ) );
		$this->addData( 'pathCovers', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.covers' ) );
		$this->addData( 'pathDocuments', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.documents' ) );
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Author::init done' );
	}

	protected function uploadImage( int|string $authorId, array $file ): bool
	{
		$words		= (object) $this->getWords( 'upload' );
		if( empty( $file['name'] ) )
			return FALSE;
		if( $file['error']	!= 0 ){
			$handler	= new UploadErrorHandler();
			$handler->setMessages( $this->getWords( 'uploadErrors' ) );
			$this->messenger->noteError( $handler->getErrorMessage( $file['error'] ) );
			return FALSE;
		}

		/*  --  CHECK NEW IMAGE  --  */
		$info		= pathinfo( $file['name'] );
		$extension	= $info['extension'];
		$extensions	= ['jpe', 'jpeg', 'jpg', 'png', 'gif'];
		if( !in_array( strtolower( $extension ), $extensions ) ){
			$this->messenger->noteError( $words->msgErrorExtensionInvalid );
			return FALSE;
		}

		try{
			$this->logic->removeAuthorImage( $authorId );											//  remove older image if set
			$this->logic->addAuthorImage( $authorId, $file );										//  set newer image
			return TRUE;
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->msgErrorUpload, $e->getMessage() );
		}
		return FALSE;
	}
}
