<?php
class Controller_Manage_Catalog_Author extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->logic		= new Logic_Catalog( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->config		= $this->env->getConfig();
		$this->addData( 'config', $this->config->getAll( 'module.manage_catalog.', TRUE ) );
	}

	public function add(){
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
		$author		= array();
		foreach( $model->getColumns() as $column )
			$author[$column]	= $this->request->get( $column );
		$this->addData( 'author', (object) $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
	}

	public function ajaxSetTab( $tabKey ){
		$this->session->set( 'manage.catalog.author.tab', $tabKey );
		exit;
	}

	public function edit( $authorId ){
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

	public function index(){
#		if( !( $authors	= $this->env->getCache()->get( 'authors' ) ) ){
			$authors	= $this->logic->getAuthors();
#			$this->env->getCache()->set( 'authors', $authors );
#		}
		$this->addData( 'authors', $authors );
	}

	public function remove( $authorId ){
		$words	= $this->getWords( 'remove' );
		if( $this->logic->getArticlesFromAuthor( $authorId ) )
			$this->messenger->noteError( $words->msgErrorNotEmpty );
		else
			$this->logic->removeAuthor( $authorId );
	}

	public function removeImage( $authorId ){
		$this->logic->removeAuthorImage( $authorId );
		$this->restart( 'manage/catalog/author/edit/'.$authorId );
	}

	protected function uploadImage( $authorId, $file ){
		$words		= (object) $this->getWords( 'upload' );
		if( !isset( $file['name'] ) || empty( $file['name'] ) )
			return;
		if( $file['error']	!= 0 ){
			$handler	= new Net_HTTP_UploadErrorHandler();
			$handler->setMessages( $this->getWords( 'uploadErrors' ) );
			$this->messenger->noteError( $handler->getErrorMessage( $file['error'] ) );
			return FALSE;
		}

		/*  --  CHECK NEW IMAGE  --  */
		$info		= pathinfo( $file['name'] );
		$extension	= $info['extension'];
		$extensions	= array( 'jpe', 'jpeg', 'jpg', 'png', 'gif' );
		if( !in_array( strtolower( $extension ), $extensions ) ){
			$this->messenger->noteError( $words->msgErrorExtensionInvalid );
			return FALSE;
		}

		try{
			$this->logic->removeAuthorImage( $authorId );											//  remove older image if set
			$this->logic->addAuthorImage( $authorId, $file );										//  set newer image
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->msgErrorUpload, $e->getMessage() );
		}
	}
}
?>
