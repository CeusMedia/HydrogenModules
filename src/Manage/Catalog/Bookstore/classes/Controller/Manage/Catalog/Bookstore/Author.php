<?php
class Controller_Manage_Catalog_Bookstore_Author extends Controller_Manage_Catalog_Bookstore
{
	/**
	 *	@return		void
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
				$this->restart( 'manage/catalog/bookstore/author/edit/'.$authorId );
			}
		}
		$model		= new Model_Catalog_Bookstore_Author( $this->env );
		$author		= [];
		foreach( $model->getColumns() as $column )
			$author[$column]	= $this->request->get( $column );
		$this->addData( 'author', (object) $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
	}

	/**
	 *	@param		string		$authorId
	 *	@return		void
	 */
	public function edit( string $authorId ): void
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
				$this->restart( 'manage/catalog/bookstore/author/edit/'.$authorId );
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
	 *	@param		string		$authorId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function remove( string $authorId ): void
	{
		$words	= (object) $this->getWords( 'remove' );
		if( $this->logic->getArticlesFromAuthor( $authorId ) )
			$this->messenger->noteError( $words->msgErrorNotEmpty );
		else{
			$this->logic->removeAuthor( $authorId );
			$this->restart( 'manage/catalog/bookstore/author' );
		}
	}

	/**
	 *	@param		string		$authorId
	 *	@return		void
	 */
	public function removeImage( string $authorId ): void
	{
		$this->logic->removeAuthorImage( $authorId );
		$this->restart( 'manage/catalog/bookstore/author/edit/'.$authorId );
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$pathFrontendContents	= $this->frontend->getPath( 'contents' );
		$paths					= $this->moduleConfig->getAll( 'path.', TRUE );
		$this->addData( 'pathAuthors', $pathFrontendContents.$paths->get( 'authors' ) );
		$this->addData( 'pathCovers', $pathFrontendContents.$paths->get( 'covers' ) );
		$this->addData( 'pathDocuments', $pathFrontendContents.$paths->get( 'documents' ) );
	}

	/**
	 *	@param		string		$authorId
	 *	@param		array		$file
	 *	@return		void
	 */
	protected function uploadImage( string $authorId, array $file ): void
	{
		$words		= (object) $this->getWords( 'upload' );
		$fileName	= $file['name'] ?? '';
		if( '' === $fileName )
			return;

		$extensions	= $this->moduleConfig->get( 'author.image.extensions' );
		$logic		= new Logic_Upload( $this->env );
		try{
			$logic->setUpload( $file );
			$logic->checkExtension( preg_split( '/\s*,\s*/', $extensions ), TRUE );
			$logic->checkIsImage( TRUE );
//			$logic->checkSize( $this->moduleConfig->get( 'article.image.size' )."M", TRUE );
//			$logic->sanitizeFileName();
			if( $logic->getError() ){
				$helper	= new View_Helper_UploadError( $this->env );
				$helper->setUpload( $logic );
				$this->messenger->noteError( $helper->render() );
			}
			else{
				$targetFile		= uniqid().'.'.$logic->getExtension( TRUE );
				$logic->saveTo( $targetFile );
				$this->logic->removeAuthorImage( $authorId );										//  remove older image if set
				$this->logic->addAuthorImage( $authorId, $targetFile, $logic->getMimeType() );		//  set newer image
				@unlink( $targetFile );																//  remove original
			}
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->msgErrorUpload, $e->getMessage() );
		}
	}
}
