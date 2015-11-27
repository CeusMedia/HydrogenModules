<?php
class Controller_Admin_Mail_Attachment extends CMF_Hydrogen_Controller{

	protected $model;
	protected $path;
	protected $messenger;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Mail_Attachment( $this->env );
		$this->logicMail	= new Logic_Mail( $this->env );
		$this->logicUpload	= new Logic_Upload( $this->env );
		$pathApp			= '';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$pathApp		= Logic_Frontend::getInstance( $this->env )->getPath();
		$this->path			= $pathApp.$this->env->getConfig()->get( 'module.resource_mail.path.attachments' );
		$this->addData( 'path', $this->path );
		$this->addData( 'files', $this->listFiles() );

		$this->languages	= array();
		$select	= "SELECT DISTINCT(language) FROM ".$this->model->getName();
		foreach( $this->env->getDatabase()->query( $select )->fetchAll( PDO::FETCH_OBJ ) as $language )
			$this->languages[]	= $language->language;

		$this->addData( 'classes', $this->logicMail->getMailClassNames() );
		$this->addData( 'languages', $this->languages );
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
				$languages	= explode( ",", $language );
				foreach( $languages as $language ){
					if( strlen( trim( $language ) ) ){
						$data	= array(
							'status'	=> (int) (bool) $this->request->get( 'status' ),
							'language'	=> $language,
							'className'	=> $class,
							'filename'	=> $file,
							'mimeType'	=> $files[$file]->mimeType,
							'createdAt'	=> time(),
						);
					}
					$this->model->add( $data );
				}
				$this->messenger->noteSuccess(
					$words->successAdded,
					htmlentities( $this->request->get( 'file' ), ENT_QUOTES, 'UTF-8' ),
					htmlentities( $this->request->get( 'class' ), ENT_QUOTES, 'UTF-8' )
				);
			}
		}
//		$this->restart( NULL, TRUE );
	}

	public function filter( $reset = NULL ){
		$session	= $this->env->getSession();
		$prefix		= 'filter_admin_mail_attachment_';
		if( $reset ){
			$session->remove( $prefix.'status' );
			$session->remove( $prefix.'file' );
			$session->remove( $prefix.'class' );
			$session->remove( $prefix.'language' );
			$session->remove( $prefix.'limit' );
			$session->remove( $prefix.'order' );
			$session->remove( $prefix.'direction' );
		}
		if( $this->request->has( 'filter' ) ){
			$session->set( $prefix.'status', $this->request->get( 'status' ) );
			$session->set( $prefix.'file', $this->request->get( 'file' ) );
			$session->set( $prefix.'class', $this->request->get( 'class' ) );
			$session->set( $prefix.'language', $this->request->get( 'language' ) );
			$session->set( $prefix.'limit', $this->request->get( 'limit' ) );
			$session->set( $prefix.'order', $this->request->get( 'order' ) );
			$session->set( $prefix.'direction', $this->request->get( 'direction' ) );
		}
		$this->restart( NULL, TRUE );
	}

	protected function getMimeTypeOfFile( $fileName ){
		if( !file_exists( $this->path.$fileName ) )
			throw new RuntimeException( 'File "'.$fileName.'" is not existing is attachments folder.' );
		$info	= finfo_open( FILEINFO_MIME_TYPE/*, '/usr/share/file/magic'*/ );
		return finfo_file( $info, $this->path.$fileName );
	}

	public function index( $page = NULL ){
		$session	= $this->env->getSession();
		$prefix		= 'filter_admin_mail_attachment_';
		$this->addData( 'filterStatus', $filterStatus = $session->get( $prefix.'status' ) );
		$this->addData( 'filterFile', $filterFile = $session->get( $prefix.'file' ) );
		$this->addData( 'filterClass', $filterClass = $session->get( $prefix.'class' ) );
		$this->addData( 'filterLanguage', $filterLanguage = $session->get( $prefix.'language' ) );
		$this->addData( 'filterLimit', $filterLimit = $session->get( $prefix.'limit' ) );
		$this->addData( 'filterOrder', $filterOrder = $session->get( $prefix.'order' ) );
		$this->addData( 'filterDirection', $filterDirection = $session->get( $prefix.'direction' ) );

		$conditions	= array();
		if( strlen( trim( $filterStatus ) ) )
			$conditions['status']		= $filterStatus;
		if( strlen( trim( $filterClass ) ) )
			$conditions['className']	= $filterClass;
		if( strlen( trim( $filterFile ) ) )
			$conditions['filename']		= $filterFile;
		if( strlen( trim( $filterLanguage ) ) )
			$conditions['language']		= $filterLanguage;

		$orders	= array();
		$limit	= max( (int) $filterLimit, 10 );
		$limits	= array( (int) $page * $limit, $limit );

		$this->addData( 'limit', $limit );
		$this->addData( 'page', (int) $page );
		$this->addData( 'total', $this->model->count( $conditions ) );
		$this->addData( 'attachments', $this->model->getAll( $conditions, $orders, $limits ) );
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
		ksort( $list );
		return $list;
	}

	public function remove( $fileName ){
		$words		= (object) $this->getWords( 'msg' );
		if( $this->model->getAllByIndex( 'filename', $fileName ) )
			$this->messenger->noteError( $words->errorFileInUse, $fileName );
		else if( !file_exists( $this->path.$fileName ) )
			$this->messenger->noteError( $words->errorFileNotExisting, $fileName );
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
		$this->restart( 'upload', TRUE );
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
		if( $this->request->has( 'upload' ) ){
			$file		= (object) $this->request->get( 'file' );
			$this->upload->setUpload( $this->request->get( 'file' ) );
			$maxSize	= min( $this->upload->getMaxUploadSize() );
			if( !$this->upload->checkSize( $maxSize ) ){
				$this->messenger->noteError( $words->errorFileTooLarge, Alg_UnitFormater::formatBytes( $maxSize ) );
			}
			else if( $file->error ){
				$handler    = new Net_HTTP_UploadErrorHandler();
				$handler->setMessages( $this->getWords( 'msgErrorUpload' ) );
				$this->messenger->noteError( $handler->getErrorMessage( $file->error ) );
			}
			else{
				try{
					$this->upload->saveTo( $this->path.$file->name );
					$this->messenger->noteSuccess(
						$words->successUploaded,
						htmlentities( $file->name, ENT_QUOTES, 'UTF-8' )
					);
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $words->failureUploadFailed );
				}
			}
			$this->restart( 'upload', TRUE );
		}
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
