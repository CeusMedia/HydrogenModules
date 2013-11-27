<?php
class Controller_Manage_Content_Document extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$config			= $this->env->getConfig()->getAll( "module.manage_content_documents.", TRUE );
		$this->path		= $config->get( 'path' );
		if( !$this->path )
			throw new RuntimeException( 'No document path set in module configuration' );
#		$words			= $this->getWords( "exceptions", "manage/content/documents" );
		if( !file_exists( $this->path ) || !is_dir( $this->path ) )
			throw new RuntimeException( 'Documents folder "'.$this->path.'" is not existing' );
		if( !is_writable( $this->path ) )
			throw new RuntimeException( 'Documents folder "'.$this->path.'" is not writable' );
		$this->model	= new Model_Document( $this->env, $this->path );
		$this->addData( 'documents', $this->model->index() );
	}

	public function add(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		if( $request->has( 'save' ) ){
			$upload	= (object) $request->get( 'upload' );
			if( $upload->error ){
                $handler    = new Net_HTTP_UploadErrorHandler();
                $handler->setMessages( $this->getWords( 'msgErrorUpload' ) );
				$messenger->noteError( $handler->getErrorMessage( $upload->error ) );
				$this->restart( NULL, TRUE );
			}
			else{
				if( !@move_uploaded_file( $upload->tmp_name, $this->path.$upload->name ) )
					$messenger->noteFailure( 'Moving uploaded file to documents folder failed' );
				else
					$messenger->noteSuccess( 'Datei "%s" hochgeladen.', $upload->name );
				$this->restart( NULL, TRUE );
			}
		}
	}

	public function index(){
		$this->addData( 'pathDocuments', $this->path );
	}

	public function remove(){
		$document	= base64_decode( $this->env->getRequest()->get( 'documentId' ) );
		if( file_exists( $this->path.$document ) )
			unlink( $this->path.$document );
		$this->restart( NULL, TRUE );
	}
}
?>
