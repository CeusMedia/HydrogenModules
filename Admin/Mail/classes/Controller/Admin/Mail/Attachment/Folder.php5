<?php
use FS_Folder_Lister as FolderIndex;
use FS_Folder_RecursiveLister as RecursiveFolderIndex;

class Controller_Admin_Mail_Attachment_Folder extends CMF_Hydrogen_Controller
{
	protected $model;
	protected $basePath;
	protected $messenger;
	protected $languages;
	protected $logicMail;
	protected $logicUpload;

	public function add()
	{
//		$words		= (object) $this->getWords( 'msg' );
		if( $this->request->getMethod()->isPost() && $this->request->has( 'folder' ) ){
			$path	= $this->request->get( 'path' );
			$path	= strlen( trim( $path ) ) ? $path.'/' : '';
			$folder	= $this->request->get( 'folder' );
			$folder	= str_replace( [' '], ['_'], $folder );
			new FS_Folder( $this->basePath.$path.$folder, TRUE );
			if( $path )
				$this->restart( 'index/'.base64_encode( $path ), TRUE );
		}
		$this->restart( NULL, TRUE );
	}

	public function download( string $filePathEncoded )
	{
		$filePath	= base64_decode( $filePathEncoded );
		if( !file_exists( $this->basePath.$filePath ) ){
			$this->messenger->noteError( 'Invalid file path.' );
			$this->restart( NULL, TRUE );
		}
		$fileName	= basename( $filePath );
		Net_HTTP_Download::sendFile( $this->basePath.$filePath, $fileName );
	}

	public function index( ?string $pathEncoded = NULL )
	{
		$path		= '';
//		remark( 'pathEncoded: '.$pathEncoded );
		if( $pathEncoded !== NULL && strlen( trim( $pathEncoded ) ) ){
			if( strlen( trim( base64_decode( $pathEncoded ) ) ) ){
				$path	= trim( base64_decode( $pathEncoded ) );
				$path	= $path ? rtrim( $path, '/' ).'/' : '';
			}
		}
		$this->addData( 'selectedPath', $path );

		$folders		= [];
		$files			= [];
		$paths			= [];
		$basePathRegex	= '@^'.preg_quote( $this->basePath, '@' ).'@';
		$pathRegex		= '@^'.preg_quote( $this->basePath.$path, '@' ).'@';
//		remark( $this->basePath.$path );die;
		foreach( FolderIndex::getFolderList( $this->basePath.$path ) as $item )
			$folders[]	= preg_replace( $pathRegex, '', $item->getPathName() );
		foreach( FolderIndex::getFileList( $this->basePath.$path ) as $item )
			$files[]	= $item->getFilename();
		foreach( RecursiveFolderIndex::getFolderList( $this->basePath ) as $item )
			$paths[]	= preg_replace( $basePathRegex, '', $item->getPathName() );
		$this->addData( 'folders', $folders );
		$this->addData( 'files', $files );
		$this->addData( 'paths', $paths );
	}

	public function remove( string $filePathEncoded )
	{
		$words		= (object) $this->getWords( 'msg' );

		$filePath	= base64_decode( $filePathEncoded );
		if( !file_exists( $this->basePath.$filePath ) ){
//			$this->messenger->noteError( $words->errorFileNotExisting, $filePath );
			$this->messenger->noteError( 'Invalid file or folder path' );
			$this->restart( NULL, TRUE );
		}
		if( is_dir( $this->basePath.$filePath ) ){
			try{
				FS_Folder_Editor::removeFolder( $this->basePath.$filePath );
			}
			catch( Exception $e ){
				$this->messenger->noteFailure(
					$words->failureRemoveFailed,
					htmlentities( $filePath, ENT_QUOTES, 'UTF-8' )
 				);
			}
			$path	= dirname( $filePath );
			$path	= $path === '.' ? '' : $path;
			$this->restart( 'index/'.base64_encode( $path ), TRUE );
		}
		else if( is_file( $this->basePath.$filePath ) ){
			@unlink( $this->basePath.$filePath );
			$path	= dirname( $filePath );
			$path	= $path === '.' ? '' : $path;
			if( file_exists( $this->basePath.$filePath ) )
				$this->messenger->noteFailure(
					$words->failureRemoveFailed,
					htmlentities( $filePath, ENT_QUOTES, 'UTF-8' )
 				);
			else{
				$this->messenger->noteSuccess(
					$words->successRemoved,
					htmlentities( $filePath, ENT_QUOTES, 'UTF-8' )
				);
				$this->restart( 'index/'.base64_encode( $path ), TRUE );
			}
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	Stores a new attachment file via HTTP upload to attachment file folder.
	 *	@access		public
	 *	@return		void
	 *	@todo		kriss: handle failure (with mail to developer or exception log)
	 */
	public function upload()
	{
		$words		= (object) $this->getWords( 'msg' );
		if( $this->request->getMethod()->isPost() && $this->request->has( 'file' ) ){
			$file		= (object) $this->request->get( 'file' );
			$this->logicUpload->setUpload( $this->request->get( 'file' ) );
			$maxSize	= $this->logicUpload->getMaxUploadSize();
			if( !$this->logicUpload->checkSize( $maxSize ) ){
				$this->messenger->noteError( $words->errorFileTooLarge, Alg_UnitFormater::formatBytes( $maxSize ) );
			}
			else if( $file->error ){
				$handler    = new Net_HTTP_UploadErrorHandler();
				$handler->setMessages( $this->getWords( 'msgErrorUpload' ) );
				$this->messenger->noteError( $handler->getErrorMessage( $file->error ) );
			}
			else{
				try{
					$path	= $this->request->get( 'path' );
					$path	= strlen( trim( $path ) ) ? $path.'/' : '';
					$this->logicUpload->saveTo( $this->basePath.$path.$file->name );
					$this->messenger->noteSuccess(
						$words->successUploaded,
						htmlentities( $file->name, ENT_QUOTES, 'UTF-8' )
					);
					if( $path )
						$this->restart( 'index/'.base64_encode( $path ), TRUE );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $words->failureUploadFailed );
				}
			}
		}
		$this->restart( NULL, TRUE );
	}

	//  --  PROTECTED  --  //

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
		$this->basePath		= $pathApp.$this->env->getConfig()->get( 'module.resource_mail.path.attachments' );
		$this->addData( 'basePath', $this->basePath );

//		$this->addData( 'files', $this->listFiles() );
	}

	protected function getMimeTypeOfFile( string $fileName )
	{
		if( !file_exists( $this->basePath.$fileName ) )
			throw new RuntimeException( 'File "'.$fileName.'" is not existing is attachments folder.' );
		$info	= finfo_open( FILEINFO_MIME_TYPE/*, '/usr/share/file/magic'*/ );
		return finfo_file( $info, $this->basePath.$fileName );
	}

	protected function listFiles(): array
	{
		$list	= [];
		$index	= new DirectoryIterator( $this->basePath );
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() || $entry->getFilename()[0] === "." )
				continue;
			$key	= strtolower( $entry->getFilename() );
			$list[$entry->getFilename()]	= (object) [
				'fileName'		=> $entry->getFilename(),
				'mimeType'		=> $this->getMimeTypeOfFile( $entry->getFilename() )
			];
		}
		ksort( $list );
		return $list;
	}
}
