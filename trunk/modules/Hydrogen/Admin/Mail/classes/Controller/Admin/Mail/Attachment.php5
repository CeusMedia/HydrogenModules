<?php
class Controller_Mail_Attachment extends CMF_Hydrogen_Controller{

	protected $model;
	protected $path	= "contents/attachments/";

	public function __onInit(){
		$this->model	= new Model_Mail_Attachment( $this->env );
		$this->logic	= new Logic_Mail( $this->env );
	}

	public function add(){
		$request	= $this->env->getRequest();
		if( $request->has( 'add' ) ){
			$files	= $this->listFiles();
			$data	= array(
				'filename'	=> $request->get( 'file' ),
				'className'	=> $request->get( 'class' ),
				'mimeType'	=> $files[$request->get( 'file' )]->mimeType,
				'createdAt'	=> time(),
				'status'	=> (int) (bool) $request->get( 'status' ),
			);
			$this->model->add( $data );
			$this->env->getMessenger()->noteSuccess( 'Attachment added.' );
		}
		$this->restart( NULL, TRUE );
	}

	protected function getMimeTypeOfFile( $fileName ){
		if( !file_exists( $this->path.$fileName ) )
			throw new RuntimeException( 'File "'.$fileName.'" is not existing is attachments folder.' );
		$info	= finfo_open( FILEINFO_MIME_TYPE/*, '/usr/share/file/magic'*/ );
		return finfo_file( $info, $this->path.$fileName );
	}

	public function index(){
		$this->addData( 'attachments', $this->model->getAll() );
		$this->addData( 'files', $this->listFiles() );
		$this->addData( 'classes', $this->logic->getMailClassNames() );
	}

	protected function listFiles(){
		$index	= new DirectoryIterator( $this->path );
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() )
				continue;
			$key	= strtolower( $entry->getFilename() );
			$list[$entry->getFilename()]	= (object) array(
				'fileName'		=> $entry->getFilename(),
				'mimeType'		=> $this->getMimeTypeOfFile( $entry->getFilename() )
			);
		}
		return $list;
	}

	public function remove( $attachmentId ){
		$attachment	= $this->model->get( $attachmentId );
		if( !$attachment )
			$this->env->getMessenger()->noteError( 'Invalid attachment ID.' );
		else{
			$this->model->remove( $attachmentId );
			$this->env->getMessenger()->noteSuccess( 'Attachment removed.' );
		}
		$this->restart( NULL, TRUE );
	}

	public function setStatus( $attachmentId, $status ){
		$attachment	= $this->model->get( $attachmentId );
		if( !$attachment )
			$this->env->getMessenger()->noteError( 'Invalid attachment ID.' );
		else{
			$this->model->edit( $attachmentId, array( 'status' => (int) $status ) );
			$this->env->getMessenger()->noteSuccess( 'Attachment updated.' );
		}
		$this->restart( NULL, TRUE );
	}

	public function upload(){
		$upload		= (object) $this->env->getRequest()->get( 'file' );
		$messenger	= $this->env->getMessenger();
		if( $upload->error ){
			$handler    = new Net_HTTP_UploadErrorHandler();
			$handler->setMessages( $this->getWords( 'msgErrorUpload' ) );
			$messenger->noteError( $handler->getErrorMessage( $upload->error ) );
		}
		else{
			if( !@move_uploaded_file( $upload->tmp_name, $this->path.$upload->name ) )
				$messenger->noteFailure( 'Moving uploaded file to attachments folder failed' );
			else
				$messenger->noteSuccess( 'Datei wurde hochgeladen und als "%s" abgelegt.', $upload->name );
		}
		$this->restart( NULL, TRUE );
	}
}
?>
