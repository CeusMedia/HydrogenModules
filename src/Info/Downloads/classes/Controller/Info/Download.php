<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Info_Download extends Controller
{
	/**	@var	Messenger										$messenger	*/
	protected Messenger $messenger;

	/**	@var	Model_Download_File								$modelFile			Database model of files */
	protected Model_Download_File $modelFile;

	/**	@var	Model_Download_Folder							$modelFolder		Database model of folders */
	protected Model_Download_Folder $modelFolder;

	/**	@var	Dictionary										$options			Module configuration object */
	protected Dictionary $options;

	/**	@var	string											$path				Base path to download files */
	protected string $path;

	/**	@var	array											$rights				List of access rights of current user */
	protected array $rights		= [];

	public function deliver( $fileId = NULL ): void
	{
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid download file ID: '.$fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$mimeType	= mime_content_type( $path.$file->title );
		header( 'Content-Type: '.$mimeType );
		header( 'Content-Length: '.filesize( $path.$file->title ) );
		$fp = @fopen( $path.$file->title, "rb" );
		if( !$fp )
			header("HTTP/1.0 500 Internal Server Error");
		fpassthru( $fp );
		exit;
	}

	public function download( string $fileId ): void
	{
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid download file ID: '.$fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$this->modelFile->edit( $fileId, array(
			'nrDownloads'	=> $file->nrDownloads++,
			'downloadedAt'	=> time(),
		) );
		HttpDownload::sendFile( $path.$file->title );
		exit;
	}

	public function index( $folderId = NULL ): void
	{
		$folderId	= (int) $folderId;
		$orders		= ['rank' => 'ASC'];
		if( $folderId ){
			$folder		= $this->modelFolder->get( $folderId );
			if( !$folder ){
				$this->messenger->noteError( sprintf( 'Invalid folder ID: '.$folderId ) );
				$this->restart( NULL, TRUE );
			}
		}
		$folders	= $this->modelFolder->getAll( ['parentId' => $folderId], $orders );
		$files		= $this->modelFile->getAll( ['downloadFolderId' => $folderId], $orders );

		$this->addData( 'files', $files );
		$this->addData( 'folders', $folders );
		$this->addData( 'folderId', $folderId );
		$this->addData( 'pathBase', $this->path );
		$this->addData( 'folderPath', $this->getPathFromFolderId( $folderId ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'steps', $this->getStepsFromFolderId( $folderId ) );
	}

	public function view( $fileId = NULL ): void
	{
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid download file ID: '.$fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$this->addData( 'file', $file );
		$this->addData( 'path', $path );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'filesize', filesize( $path.$file->title ) );
		$this->addData( 'type', pathinfo( $file->title, PATHINFO_EXTENSION ) );
		$this->addData( 'mimeType', mime_content_type( $path.$file->title ) );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		$this->messenger	= $this->env->getMessenger();
		$this->options		= $this->env->getConfig()->getAll( 'module.info_downloads.', TRUE );
		$this->rights		= $this->env->getAcl()->index( 'info/downloads' );
		$this->path			= $this->options->get( 'path' );
		$this->modelFolder	= new Model_Download_Folder( $this->env );
		$this->modelFile	= new Model_Download_File( $this->env );
	}

	protected function checkFolder( $folderId ): bool
	{
		if( (int) $folderId > 0 ){
			$folder		= $this->modelFolder->get( $folderId );
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

	protected function countFolders( $folderId ): int
	{
		return $this->modelFolder->count( ['parentId' => $folderId] );
	}

	protected function getPathFromFolderId( $folderId, bool $withBasePath = FALSE ): string
	{
		$path	= '';
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: '.$folderId );
			$path		= $folder->title.'/'.$path;
			$folderId	= $folder->parentId;
		}
		return $withBasePath ? $this->path.$path : $path;
	}

	protected function countIn( string $path, bool $recursive = FALSE ): array
	{
		$files		= 0;
		$folders	= 0;
		if( $recursive ){
			$index		= RecursiveFolderLister::getMixedList( $this->path.$path );
			foreach( $index as $entry )
//				if( !$entry->isDot() )
				$entry->isDir() ? $folders++ : $files++;
		}
		else{
			die( "no implemented yet" );
		}
		return ['folders' => $folders, 'files' => $files];
	}

	protected function getStepsFromFolderId( $folderId ): array
	{
		$steps		= [];
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: '.$folderId );
			$steps[$folder->downloadFolderId]	= $folder;
			$folderId	= $folder->parentId;
		}
		$steps	= array_reverse( $steps );
		return $steps;
	}

	protected function updateNumber( string $folderId, string $type, int $diff = 1 ): void
	{
		if( !in_array( $type, ['folder', 'file'] ) )
			throw new InvalidArgumentException( 'Type must be folder or file' );
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: '.$folderId );
			switch( $type ){
				case 'folder':
					$data	= ['nrFolders' => $folder->nrFolders + $diff];
					break;
				case 'file':
					$data	= ['nrFiles' => $folder->nrFiles + $diff];
					break;
			}
			$data['modifiedAt']	= time();
			$this->modelFolder->edit( $folderId, $data );
			$folderId	= $folder->parentId;
		}
	}
}
