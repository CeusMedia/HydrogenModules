<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Mail_Attachment extends Controller
{
	protected $model;
	protected $path;
	protected $messenger;
	protected $languages;
	protected $logicMail;
	protected $logicUpload;

	public function add()
	{
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

	public function download( $fileName )
	{
		$fileName	= urldecode( $fileName );
		Net_HTTP_Download::sendFile( $this->path.$fileName, $fileName );
	}

	public function filter( $reset = NULL )
	{
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

	protected function getMimeTypeOfFile( $filePath )
	{
		if( !file_exists( $this->path.$filePath ) )
			throw new RuntimeException( 'File "'.$filePath.'" is not existing is attachments folder.' );
		$info	= finfo_open( FILEINFO_MIME_TYPE/*, '/usr/share/file/magic'*/ );
		return finfo_file( $info, $this->path.$filePath );
	}

	public function index( $page = NULL )
	{
		$session	= $this->env->getSession();
		$prefix		= 'filter_admin_mail_attachment_';
		$this->addData( 'filterStatus', $filterStatus = $session->get( $prefix.'status' ) );
		$this->addData( 'filterFile', $filterFile = $session->get( $prefix.'file' ) );
		$this->addData( 'filterClass', $filterClass = $session->get( $prefix.'class' ) );
		$this->addData( 'filterLanguage', $filterLanguage = $session->get( $prefix.'language' ) );
		$this->addData( 'filterLimit', $filterLimit = $session->get( $prefix.'limit' ) );
		$this->addData( 'filterOrder', $filterOrder = $session->get( $prefix.'order' ) );
		$this->addData( 'filterDirection', $filterDirection = $session->get( $prefix.'direction' ) );

		$conditions	= [];
		if( strlen( trim( $filterStatus ) ) )
			$conditions['status']		= $filterStatus;
		if( strlen( trim( $filterClass ) ) )
			$conditions['className']	= $filterClass;
		if( strlen( trim( $filterFile ) ) )
			$conditions['filename']		= $filterFile;
		if( strlen( trim( $filterLanguage ) ) )
			$conditions['language']		= $filterLanguage;

		$orders	= [];
		$limit	= max( (int) $filterLimit, 10 );
		$limits	= array( (int) $page * $limit, $limit );

		$this->addData( 'limit', $limit );
		$this->addData( 'page', (int) $page );
		$this->addData( 'total', $this->model->count( $conditions ) );
		$this->addData( 'attachments', $this->model->getAll( $conditions, $orders, $limits ) );
	}

	public function setStatus( $attachmentId, $status )
	{
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

	public function unregister( $attachmentId )
	{
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

	protected function __onInit()
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Mail_Attachment( $this->env );
		$this->logicMail	= Logic_Mail::getInstance( $this->env );
		$this->logicUpload	= new Logic_Upload( $this->env );
		$pathApp			= '';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$pathApp		= Logic_Frontend::getInstance( $this->env )->getPath();
		$this->path			= $pathApp.$this->env->getConfig()->get( 'module.resource_mail.path.attachments' );
		$this->addData( 'path', $this->path );
		$this->addData( 'files', $this->listFiles() );

		$this->languages	= [];
		$select	= "SELECT DISTINCT(language) FROM ".$this->model->getName();
		foreach( $this->env->getDatabase()->query( $select )->fetchAll( PDO::FETCH_OBJ ) as $language )
			$this->languages[]	= $language->language;

		$this->addData( 'classes', $this->logicMail->getMailClassNames( FALSE ) );
		$this->addData( 'languages', $this->languages );
	}

	protected function listFiles()
	{
		$list	= [];
		foreach( FS_Folder_RecursiveLister::getFileList( $this->path ) as $entry ){
			$pathName	= preg_replace( '@^'.preg_quote( $this->path, '@' ).'@', '', $entry->getPathName() );
			$list[$pathName]	= (object) [
				'fileName'		=> $entry->getFilename(),
				'filePath'		=> $pathName,
				'mimeType'		=> $this->getMimeTypeOfFile( $pathName )
			];
		}
		return $list;
	}
}
