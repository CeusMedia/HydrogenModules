<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Info_Download extends Controller
{
	/**	@var	Messenger								$messenger	*/
	protected Messenger $messenger;

	/** @var	Logic_Download							$logic				Logic class for file and folder management */
	protected Logic_Download $logic;

	/**	@var	Dictionary								$options			Module configuration object */
	protected Dictionary $options;

	/**	@var	string									$path				Base path to download files */
	protected string $path;

	/**	@var	array									$rights				List of access rights of current user */
	protected array $rights		= [];

	/**
	 *	@param		int|string		$fileId
	 *	@return		never
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function deliver( int|string $fileId ): never
	{
		$file		= $this->logic->getFile( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid download file ID: '.$fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->logic->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$mimeType	= mime_content_type( $path.$file->title );
		header( 'Content-Type: '.$mimeType );
		header( 'Content-Length: '.filesize( $path.$file->title ) );
		header( 'Content-Disposition: inline; filename="'.addslashes( $file->title ).'"' );
		$fp = @fopen( $path.$file->title, "rb" );
		if( !$fp )
			header("HTTP/1.0 500 Internal Server Error");
		fpassthru( $fp );
		exit;
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		never
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function download( int|string $fileId ): never
	{
		$file		= $this->logic->getFile( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid download file ID: '.$fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->logic->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$this->logic->makeDownloadCount( $fileId );
		HttpDownload::sendFile( $path.$file->title );
		exit;
	}

	/**
	 *	@param		int|string|NULL		$folderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int|string|NULL $folderId = NULL ): void
	{
		$folderId	= (int) $folderId;
		$orders		= ['rank' => 'ASC'];
		if( $folderId ){
			$folder		= $this->logic->getFolder( $folderId );
			if( !$folder ){
				$this->messenger->noteError( sprintf( 'Invalid folder ID: '.$folderId ) );
				$this->restart( NULL, TRUE );
			}
		}
		$folders	= $this->logic->findFolders( ['parentId' => $folderId], $orders );
		$files		= $this->logic->findFiles( ['downloadFolderId' => $folderId], $orders );

		$this->addData( 'files', $files );
		$this->addData( 'folders', $folders );
		$this->addData( 'folderId', $folderId );
		$this->addData( 'pathBase', $this->path );
		$this->addData( 'folderPath', $this->logic->getPathFromFolderId( $folderId ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'steps', $this->logic->getStepsFromFolderId( $folderId ) );
	}

	/**
	 *	@param		int|string|NULL		$fileId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( int|string|NULL $fileId = NULL ): void
	{
		$file		= $this->logic->getFile( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid download file ID: '.$fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->logic->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$this->addData( 'file', $file );
		$this->addData( 'path', $path );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'filesize', filesize( $path.$file->title ) );
		$this->addData( 'type', pathinfo( $file->title, PATHINFO_EXTENSION ) );
		$this->addData( 'mimeType', mime_content_type( $path.$file->title ) );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->messenger	= $this->env->getMessenger();
		$this->options		= $this->env->getConfig()->getAll( 'module.info_downloads.', TRUE );
		$this->path			= $this->options->get( 'path' );
		$this->logic		= new Logic_Download( $this->env, $this->path );
		$this->rights		= $this->env->getAcl()->index( 'info/downloads' );
	}

	protected function checkFolder( int|string $folderId ): bool
	{
		if( (int) $folderId > 0 ){
			$folder		= $this->logic->getFolder( $folderId );
			if( $folder && file_exists( $this->path.$folder->title ) )
				return TRUE;
			if( !$folder )
				$this->messenger->noteError( 'Invalid folder with ID '.$folderId );
			else if( file_exists( $this->path.$folder->title ) )
				$this->messenger->noteError( 'Folder %s is not existing', $folder->title );
		}
		else{
			if( file_exists( $this->path ) )
				return TRUE;
			$this->messenger->noteError( 'Base folder %s is not existing', $this->path );
		}
		return FALSE;
	}
}
