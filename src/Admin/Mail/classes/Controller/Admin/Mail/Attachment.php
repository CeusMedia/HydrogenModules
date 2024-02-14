<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Mail_Attachment extends Controller
{
	protected Dictionary $request;
	protected Model_Mail_Attachment $model;
	protected string $attachmentPath;
	protected MessengerResource $messenger;
	protected array $languages;
	protected Logic_Mail $logicMail;
	protected Logic_Upload $logicUpload;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
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
			$indices	= [
				'className'	=> $class,
				'filename'	=> $file,
				'language'	=> $language
			];
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
					if( 0 === strlen( trim( $language ) ) )
						continue;
					$data	= array(
						'status'	=> (int) (bool) $this->request->get( 'status' ),
						'language'	=> $language,
						'className'	=> $class,
						'filename'	=> $file,
						'mimeType'	=> $files[$file]->mimeType,
						'createdAt'	=> time(),
					);
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

	public function download( string $fileName ): void
	{
		$fileName	= urldecode( $fileName );
		HttpDownload::sendFile( $this->attachmentPath.$fileName, $fileName );
	}

	public function filter( $reset = NULL ): void
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

	public function index( int $page = 0 ): void
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
		if( strlen( trim( $filterStatus ?? '' ) ) )
			$conditions['status']		= $filterStatus;
		if( strlen( trim( $filterClass ?? '' ) ) )
			$conditions['className']	= $filterClass;
		if( strlen( trim( $filterFile ?? '' ) ) )
			$conditions['filename']		= $filterFile;
		if( strlen( trim( $filterLanguage ?? '' ) ) )
			$conditions['language']		= $filterLanguage;

		$orders	= [];
		if( $filterOrder && $filterDirection )
			$orders	= [$filterOrder, $filterDirection];
		$limit	= max( (int) $filterLimit, 10 );
		$limits	= array( $page * $limit, $limit );

		$this->addData( 'limit', $limit );
		$this->addData( 'page', $page );
		$this->addData( 'total', $this->model->count( $conditions ) );
		$this->addData( 'attachments', $this->model->getAll( $conditions, $orders, $limits ) );
	}

	/**
	 *	@param		string		$attachmentId
	 *	@param		$status
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setStatus( string $attachmentId, $status ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$attachment	= $this->model->get( $attachmentId );
		if( !$attachment )
			$this->messenger->noteError( $words->errorIdInvalid );
		else{
			$this->model->edit( $attachmentId, ['status' => (int) $status] );
			$this->messenger->noteSuccess(
				(int) $status ? $words->successEnabled : $words->successDisabled,
				htmlentities( $attachment->filename, ENT_QUOTES, 'UTF-8' ),
				htmlentities( $attachment->className, ENT_QUOTES, 'UTF-8' )
			);
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$attachmentId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function unregister( string $attachmentId ): void
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Mail_Attachment( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicMail	= Logic_Mail::getInstance( $this->env );
		$this->logicUpload	= new Logic_Upload( $this->env );
		$pathApp			= '';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$pathApp		= Logic_Frontend::getInstance( $this->env )->getPath();
		$this->attachmentPath			= $pathApp.$this->env->getConfig()->get( 'module.resource_mail.path.attachments' );
		$this->addData( 'path', $this->attachmentPath );
		$this->addData( 'files', $this->listFiles() );

		$this->languages	= [];
		$select	= "SELECT DISTINCT(language) FROM ".$this->model->getName();
		foreach( $this->env->getDatabase()->query( $select )->fetchAll( PDO::FETCH_OBJ ) as $language )
			$this->languages[]	= $language->language;

		$this->addData( 'classes', $this->logicMail->getMailClassNames( FALSE ) );
		$this->addData( 'languages', $this->languages );
	}

	/**
	 *	@param		string		$filePath
	 *	@return		bool|string
	 */
	protected function getMimeTypeOfFile( string $filePath ): bool|string
	{
		if( !file_exists( $this->attachmentPath.$filePath ) )
			throw new RuntimeException( 'File "'.$filePath.'" is not existing is attachments folder.' );
		$info	= finfo_open( FILEINFO_MIME_TYPE/*, '/usr/share/file/magic'*/ );
		return finfo_file( $info, $this->attachmentPath.$filePath );
	}

	/**
	 *	@return		array<string,object>
	 */
	protected function listFiles(): array
	{
		$list	= [];
		foreach( RecursiveFolderLister::getFileList( $this->attachmentPath ) as $entry ){
			$pathName	= preg_replace( '@^'.preg_quote( $this->attachmentPath, '@' ).'@', '', $entry->getPathName() );
			$list[$pathName]	= (object) [
				'fileName'		=> $entry->getFilename(),
				'filePath'		=> $pathName,
				'mimeType'		=> $this->getMimeTypeOfFile( $pathName )
			];
		}
		return $list;
	}
}
