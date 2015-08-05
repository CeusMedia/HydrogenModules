<?php
class Controller_Admin_Mail_Attachment extends CMF_Hydrogen_Controller{

	protected $model;
	protected $path;
	protected $messenger;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->path			= $this->env->getConfig()->get( 'module.resource_mail.path.attachments' );
		$this->model		= new Model_Mail_Attachment( $this->env );
		$this->logic		= new Logic_Mail( $this->env );
	}

	public function add(){
		$words		= (object) $this->getWords( 'msg' );
		if( $this->request->has( 'add' ) ){
			$files	= $this->listFiles();
			if( !strlen( $class = trim( $this->request->get( 'class' ) ) ) )
				$this->messenger->noteError( $words->errorClassMissing, htmlentities( $class, ENT_QUOTES, 'UTF-8' ) );
			if( !strlen( $file = trim( $this->request->get( 'file' ) ) ) )
				$this->messenger->noteError( $words->errorFileMissing, htmlentities( $file, ENT_QUOTES, 'UTF-8' ) );
			if( !strlen( $language = trim( $this->request->get( 'language' ) ) ) )
				$this->messenger->noteError( $words->errorLanguageMissing, htmlentities( $file, ENT_QUOTES, 'UTF-8' ) );
			$indices	= array(
				'className'	=> $class,
				'filename'	=> $file,
				'language'	=> $language
			);
			if( $this->model->count( $indices ) )
				$this->messenger->noteError(
					$words->errorAlreadyRegistered,
					htmlentities( $file, ENT_QUOTES, 'UTF-8' ),
					htmlentities( $class, ENT_QUOTES, 'UTF-8' )
				);
			if( !array_key_exists( $file, $files ) )
				$this->messenger->noteFailure(
					$words->errorFileNotExisting,
					htmlentities( $file, ENT_QUOTES, 'UTF-8' ),
					htmlentities( $class, ENT_QUOTES, 'UTF-8' )
			);
			if( !$this->messenger->gotError() ){
				$data	= array(
					'status'	=> (int) (bool) $this->request->get( 'status' ),
					'language'	=> $language,
					'className'	=> $class,
					'filename'	=> $file,
					'mimeType'	=> $files[$file]->mimeType,
					'createdAt'	=> time(),
				);
				$this->model->add( $data );
				$this->messenger->noteSuccess(
					$words->successAdded,
					htmlentities( $this->request->get( 'file' ), ENT_QUOTES, 'UTF-8' ),
					htmlentities( $this->request->get( 'class' ), ENT_QUOTES, 'UTF-8' )
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
			if( $entry->isDir() || $entry->isDot() || $entry->getFilename()[0] === "." )
				continue;
			$key	= strtolower( $entry->getFilename() );
			$list[$entry->getFilename()]	= (object) array(
				'fileName'		=> $entry->getFilename(),
				'mimeType'		=> $this->getMimeTypeOfFile( $entry->getFilename() )
			);
		}
		return $list;
	}

	/**
	 *	@todo	implemented but not used, yet -> show file list -> allow removal
	 */
	public function remove( $fileName ){
		$words		= (object) $this->getWords( 'msg' );
		if( $this->model->getAllByIndex( 'filename', $fileName ) )
			$this->messenger->noteError( $words->errorFileInUse );
		else if( !file_exists( $this->path.$fileName ) )
			$this->messenger->noteError( $words->errorFileNotExisting );
		else{
			@unlink( $this->path.$fileName );
			if( file_exists( $this->path.$fileName ) )
				$this->messenger->noteFailure(
					$words->failureRemoveFailed,
					htmlentities( $fileName, ENT_QUOTES, 'UTF-8' )
 				);
			else
				$this->messenger->noteSuccess(
					$words->successRemoved,
					htmlentities( $fileName, ENT_QUOTES, 'UTF-8' )
				);
		}
		$this->restart( NULL, TRUE );
	}

	public function setStatus( $attachmentId, $status ){
		$words		= (object) $this->getWords( 'msg' );
		$attachment	= $this->model->get( $attachmentId );
		if( !$attachment )
			$this->messenger->noteError( $words->errorIdInvalid );
		else{
			$this->model->edit( $attachmentId, array( 'status' => (int) $status ) );
			$this->messenger->noteSuccess(
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
		$upload		= (object) $this->request->get( 'file' );
		if( $upload->error ){
			$handler    = new Net_HTTP_UploadErrorHandler();
			$handler->setMessages( $this->getWords( 'msgErrorUpload' ) );
			$this->messenger->noteError( $handler->getErrorMessage( $upload->error ) );
		}
		else{
			if( !@move_uploaded_file( $upload->tmp_name, $this->path.$upload->name ) )
				$this->messenger->noteFailure( $words->failureUploadFailed );
			else
				$this->messenger->noteSuccess(
					$words->successUploaded,
					htmlentities( $upload->name, ENT_QUOTES, 'UTF-8' )
				);
		}
		$this->restart( NULL, TRUE );
	}

	public function unregister( $attachmentId ){
		$words		= (object) $this->getWords( 'msg' );
		$attachment	= $this->model->get( $attachmentId );
		if( !$attachment )
			$this->messenger->noteError( $words->errorIdInvalid );
		else{
			$this->model->remove( $attachmentId );
			$this->messenger->noteSuccess(
				$words->successUnregistered,
				htmlentities( $attachment->filename, ENT_QUOTES, 'UTF-8' ),
				htmlentities( $attachment->className, ENT_QUOTES, 'UTF-8' )
			);
		}
		$this->restart( NULL, TRUE );
	}
}
?>
