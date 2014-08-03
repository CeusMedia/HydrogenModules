<?php
class Controller_Admin_Mail_Attachment extends CMF_Hydrogen_Controller{

	protected $model;
	protected $path;

	public function __onInit(){
		$this->model	= new Model_Mail_Attachment( $this->env );
		$this->logic	= new Logic_Mail( $this->env );
		$this->path		= $this->env->getConfig()->get( 'module.resource_mail.path.attachments' );
	}

	public function add(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'msg' );
		if( $request->has( 'add' ) ){
			$files	= $this->listFiles();
			if( !strlen( $class = trim( $request->get( 'class' ) ) ) )
				$messenger->noteError( $words->errorClassMissing, htmlentities( $class, ENT_QUOTES, 'UTF-8' ) );
			if( !strlen( $file = trim( $request->get( 'file' ) ) ) )
				$messenger->noteError( $words->errorFileMissing, htmlentities( $file, ENT_QUOTES, 'UTF-8' ) );
			if( !strlen( $language = trim( $request->get( 'language' ) ) ) )
				$messenger->noteError( $words->errorLanguageMissing, htmlentities( $file, ENT_QUOTES, 'UTF-8' ) );
			$indices	= array(
				'className'	=> $class,
				'filename'	=> $file,
				'language'	=> $language
			);
			if( $this->model->count( $indices ) )
				$messenger->noteError(
					$words->errorAlreadyRegistered,
					htmlentities( $file, ENT_QUOTES, 'UTF-8' ),
					htmlentities( $class, ENT_QUOTES, 'UTF-8' )
				);
			if( !array_key_exists( $file, $files ) )
				$messenger->noteFailure(
					$words->errorFileNotExisting,
					htmlentities( $file, ENT_QUOTES, 'UTF-8' ),
					htmlentities( $class, ENT_QUOTES, 'UTF-8' )
			);
			if( !$messenger->gotError() ){
				$data	= array(
					'status'	=> (int) (bool) $request->get( 'status' ),
					'language'	=> $language,
					'className'	=> $class,
					'filename'	=> $file,
					'mimeType'	=> $files[$file]->mimeType,
					'createdAt'	=> time(),
				);
				$this->model->add( $data );
				$this->env->getMessenger()->noteSuccess(
					$words->successAdded,
					htmlentities( $request->get( 'file' ), ENT_QUOTES, 'UTF-8' ),
					htmlentities( $request->get( 'class' ), ENT_QUOTES, 'UTF-8' )
				);
			}
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
		$list	= array();
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
		$words		= (object) $this->getWords( 'msg' );
		$attachment	= $this->model->get( $attachmentId );
		if( !$attachment )
			$this->env->getMessenger()->noteError( $words->errorIdInvalid );
		else{
			$this->model->remove( $attachmentId );
			$this->env->getMessenger()->noteSuccess(
				$words->successRemoved,
				htmlentities( $attachment->filename, ENT_QUOTES, 'UTF-8' ),
				htmlentities( $attachment->className, ENT_QUOTES, 'UTF-8' )
			);
		}
		$this->restart( NULL, TRUE );
	}

	public function setStatus( $attachmentId, $status ){
		$words		= (object) $this->getWords( 'msg' );
		$attachment	= $this->model->get( $attachmentId );
		if( !$attachment )
			$this->env->getMessenger()->noteError( $words->errorIdInvalid );
		else{
			$this->model->edit( $attachmentId, array( 'status' => (int) $status ) );
			$this->env->getMessenger()->noteSuccess(
				(int) $status ? $words->successEnabled : $words->successDisabled,
				htmlentities( $attachment->filename, ENT_QUOTES, 'UTF-8' ),
				htmlentities( $attachment->className, ENT_QUOTES, 'UTF-8' )
			);
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	Stores a new attachment file via HTTP upload to attachment file folder.
	 *	@access		public
	 *	@return		void
	 *	@todo		kriss: handle failure (with mail to developer or exception log)
	 */
	public function upload(){
		$words		= (object) $this->getWords( 'msg' );
		$upload		= (object) $this->env->getRequest()->get( 'file' );
		$messenger	= $this->env->getMessenger();
		if( $upload->error ){
			$handler    = new Net_HTTP_UploadErrorHandler();
			$handler->setMessages( $this->getWords( 'msgErrorUpload' ) );
			$messenger->noteError( $handler->getErrorMessage( $upload->error ) );
		}
		else{
			if( !@move_uploaded_file( $upload->tmp_name, $this->path.$upload->name ) )
				$messenger->noteFailure( $words->errorUploadFailed );
			else
				$messenger->noteSuccess(
					$words->successUploaded,
					htmlentities( $upload->name, ENT_QUOTES, 'UTF-8' )
				);
		}
		$this->restart( NULL, TRUE );
	}
}
?>
