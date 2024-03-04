<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_Download extends Controller
{
	/**	@var	Messenger										$messenger	*/
	protected Messenger $messenger;

	protected Logic_Frontend $frontend;

	/**	@var	Model_Download_File								$modelFile			Database model of files */
	protected Model_Download_File $modelFile;

	/**	@var	Model_Download_Folder							$modelFolder		Database model of folders */
	protected Model_Download_Folder $modelFolder;

	/**	@var	Dictionary										$options			Module configuration object */
	protected Dictionary $options;

	/**	@var	string											$path				Base path to download files */
	protected string $pathBase;

	/**	@var	array											$rights				List of access rights of current user */
	protected array $rights		= [];

	protected object $messages;

	public function addFolder( $folderId = NULL )
	{
		$path		= $this->getPathFromFolderId( $folderId );
		$folder		= trim( $this->env->getRequest()->get( 'folder' ) );
		if( preg_match( "/[\/\?:]/", $folder) ){
			$this->messenger->noteError( 'Folgende Zeichen sind in Ordnernamen nicht erlaubt: / : ?' );
			$url	= ( $folderId ?: NULL ).'?input_folder='.rawurlencode( $folder );
			$this->restart( $url, TRUE );
		}
		else if( file_exists( $this->pathBase.$path.$folder ) ){
			$this->messenger->noteError( sprintf( 'Ein Eintrag <small>(ein Ordner oder eine Datei)</small> mit dem Namen "%s" existiert in diesem Ordner bereits.', $folder ) );
			$this->restart( $folderId.'?input_folder='.rawurlencode( $folder ), TRUE );
		}
		else{
			FolderEditor::createFolder( $this->pathBase.$path.$folder );
			$this->messenger->noteSuccess( 'Ordner <b>"%s"</b> hinzugefügt.', $folder );
			$newId	= $this->modelFolder->add( [
				'parentId'	=> (int) $folderId,
				'rank'		=> $this->countFolders( $folderId ),
				'title'		=> $folder,
				'createdAt'	=> time(),
			] );
			$this->updateNumber( $folderId, 'folder', 1 );
			$this->restart( 'index/'.$folderId, TRUE );
		}
	}

	public function ajaxRenameFolder()
	{
		$folderId	= $this->env->getRequest()->get( 'folderId' );
		$title		= $this->env->getRequest()->get( 'name' );

		$pathOld	= $this->getPathFromFolderId( $folderId, TRUE );
		$pathNew	= dirname( $pathOld ).'/'.$title;
		if( @rename( $pathOld, $pathNew ) ){
			$this->modelFolder->edit( $folderId, array(
				'title'			=> $title,
				'modifiedAt'	=> time()
			) );
		}
		print( json_encode( $this->modelFolder->get( $folderId ) ) );
		exit;
	}

	public function deliver( $fileId = NULL )
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

	public function download( $fileId )
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

	public function index( $folderId = NULL )
	{
		$folderId	= (int) $folderId;
		$orders		= ['rank' => 'ASC'];
		if( $folderId ){
			$folder		= $this->modelFolder->get( $folderId );
			if( !$folder ){
				$this->messenger->noteError( sprintf( 'Invalid folder ID: %s', $folderId ) );
				$this->restart( NULL, TRUE );
			}
		}
		$folders	= $this->modelFolder->getAll( ['parentId' => $folderId], $orders );
		$files		= $this->modelFile->getAll( ['downloadFolderId' => $folderId], $orders );

		$this->addData( 'files', $files );
		$this->addData( 'folders', $folders );
		$this->addData( 'folderId', $folderId );
		$this->addData( 'pathBase', $this->pathBase );
		$this->addData( 'folderPath', $this->getPathFromFolderId( $folderId ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'steps', $this->getStepsFromFolderId( $folderId ) );
	}

	public function rankFolder( $folderId, $downwards = NULL )
	{
		$words		= (object) $this->getWords( 'msg' );
		$direction	= (bool) $downwards ? +1 : -1;
		if( !( $folder = $this->modelFolder->get( (int) $folderId ) ) )
			$this->messenger->noteError( $words->errorInvalidFolderId, $folderId );
		else{
			$rank		= $folder->rank + $direction;
			$conditions	= ['rank' => $rank, 'parentId' => $folder->parentId];
			if( ( $next = $this->modelFolder->getByIndices( $conditions ) ) ){
				$this->modelFolder->edit( (int) $folderId, array( 'rank' => $rank, 'modifiedAt' => time() ) );
				$this->modelFolder->edit( $next->downloadFolderId, array( 'rank' => $folder->rank, 'modifiedAt' => time() ) );
			}
		}
		$this->restart( 'index/'.$folder->parentId, TRUE );
	}

	public function remove( $fileId )
	{
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid download file ID: '.$fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->pathBase;
		if( $file->downloadFolderId ){
			$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		}
		@unlink( $path.$file->title );
		$this->modelFile->remove( $fileId );
		$this->updateNumber( $file->downloadFolderId, 'file', -1 );
		$this->messenger->noteSuccess( 'Datei <b>"%s"</b> entfernt.', $file->title );
		$this->restart( 'index/'.$file->downloadFolderId, TRUE );
	}

	public function removeFolder( $folderId )
	{
		if( $folderId ){
			$folder		= $this->modelFolder->get( $folderId );
			if( !$folder ){
				$this->messenger->noteError( sprintf( 'Invalid download folder ID: %s', $folderId ) );
			}
			else{
				$hasSubfolders	= $this->modelFile->count( ['downloadFolderId' => $folderId] );
				$hasSubfiles	= $this->modelFolder->count( ['parentId' => $folderId] );
				if( $hasSubfolders && $hasSubfiles ){
					$this->messenger->noteError( 'Der Ordner <b>"%s"</b> ist nicht leer und kann daher nicht entfernt werden.', $folder->title );
				}
				else{
					rmdir( $this->getPathFromFolderId( $folderId, TRUE ) );
					$this->modelFolder->remove( $folderId );
					$this->updateNumber( $folder->parentId, 'folder', -1 );
				}
				$this->restart( $folder->parentId ? 'index/'.$folder->parentId : '', TRUE );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function scan()
	{
		$statsImport	= (object) ['folders' => [], 'files' => []];
		$statsClean		= (object) ['folders' => [], 'files' => []];
		$this->scanRecursive( 0, '', $statsImport );
		$this->cleanRecursive( 0, '', $statsClean );

		$addedSomething		= count( $statsImport->folders ) + count( $statsImport->folders ) > 0;
		$removedSomething	= count( $statsClean->folders ) + count( $statsClean->folders ) > 0;
		if( $addedSomething || $removedSomething ){
			if( $addedSomething ){
				$list	= [];
				foreach( $statsImport->files as $file ){
					$path	= HtmlTag::create( 'small', $file->pathBase, ['class' => "muted"] );
					$list[]	= HtmlTag::create( 'li', $path.$file->title );
				}
				$list	= HtmlTag::create( 'ul', $list );
				$this->messenger->noteNotice( $this->messages->infoScanFoundSomething, count( $statsImport->folders ), count( $statsImport->files ), $list );
			}
			if( $removedSomething ){
				$list	= [];
				foreach( $statsClean->files as $file ){
					$path	= HtmlTag::create( 'small', $file->pathBase, ['class' => "muted"] );
					$list[]	= HtmlTag::create( 'li', $path.$file->title );
				}
				$list	= HtmlTag::create( 'ul', $list );
				$this->messenger->noteNotice( $this->messages->infoScanRemovedSomething, count( $statsClean->folders ), count( $statsClean->files ), $list );
			}
		}
		else
			$this->messenger->noteNotice( $this->messages->infoScanNoChanges );
		$this->restart( NULL, TRUE );
	}

	public function upload( $folderId = NULL )
	{
		if( !in_array( 'upload', $this->rights ) )
			$this->restart( NULL, TRUE );
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$upload	= (object) $request->get( 'upload' );
			$logicUpload	= new Logic_Upload( $this->env );
			try{
				$logicUpload->setUpload( $upload );
				$logicUpload->checkSize( Logic_Upload::getMaxUploadSize(), TRUE );
//				$logicUpload->checkVirus( TRUE );
				$targetFile	= $this->getPathFromFolderId( $folderId, TRUE ).$upload->name;
				$logicUpload->saveTo( $targetFile );
				$rank	= $this->modelFile->count( ['downloadFolderId' => $folderId] );
				$this->modelFile->add( array(
					'downloadFolderId'	=> $folderId,
					'rank'				=> $rank,
					'size'				=> $logicUpload->getFileSize(),
					'title'				=> $logicUpload->getFileName(),
					'description'		=> (string) $request->get( 'description' ),
					'uploadedAt'		=> time()
				) );
				$this->updateNumber( $folderId, 'file', 1 );
				$this->messenger->noteSuccess( 'Datei "%s" hochgeladen.', $upload->name );
			}
			catch( Exception $e ){
				$helperError	= new View_Helper_UploadError( $this->env );
				$helperError->setUpload( $logicUpload );
				$message	= $helperError->render();
				$this->messenger->noteError( $message ?: $e->getMessage() );
			}
		}
		$this->restart( 'index/'.$folderId, TRUE );
	}

	public function view( $fileId = NULL )
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->messenger	= $this->env->getMessenger();
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->rights		= $this->env->getAcl()->index( 'manage/downloads' );
		$this->pathBase		= $this->frontend->getModuleConfigValue( 'info_downloads', 'path' );
		$this->modelFolder	= new Model_Download_Folder( $this->env );
		$this->modelFile	= new Model_Download_File( $this->env );
		$this->messages		= (object) $this->getWords( 'msg' );
	}

	protected function checkFolder( string $folderId )
	{
		if( (int) $folderId > 0 ){
			$folder		= $this->modelFolder->get( $folderId );
			if( $folder && file_exists( $this->pathBase.$folder->title ) )
				return TRUE;
			if( !$folder )
				$this->messenger->noteError( 'Invalid folder with ID '.$folderId );
			else if( file_exists( $this->pathBase.$folder->title ) )
				$this->messenger->noteError( 'Folder %s is not existing', $folder->title );
		}
		else{
			if( file_exists( $this->pathBase ) )
				return TRUE;
			$this->messenger->noteError( 'Base folder %s is not existing', $this->pathBase );
		}
		return FALSE;
	}

	protected function cleanRecursive( string $parentId, $path, $stats ){
		$path		= $this->getPathFromFolderId( $parentId, FALSE );
		$folders	= $this->modelFolder->getAll( ['parentId' => $parentId] );
		$files		= $this->modelFile->getAll( ['downloadFolderId' => $parentId] );
		foreach( $folders as $folder ){
			$this->cleanRecursive( $folder->downloadFolderId, $path.$folder->title.'/', $stats );
			if( !file_exists( $this->pathBase.$path.$folder->title ) ){
				$this->modelFolder->remove( $folder->downloadFolderId );
				$stats->folders[]	= (object) ['title' => $folder->title, 'path' => $path];
			}
		}
		foreach( $files as $file ){
			if( !file_exists( $this->pathBase.$path.$file->title ) ){
				$this->modelFile->remove( $file->downloadFileId );
				$stats->files[]	= (object) ['title' => $file->title, 'path' => $path];
			}
		}
	}

	protected function countFolders( string $folderId ): int
	{
		return $this->modelFolder->count( ['parentId' => $folderId] );
	}

	protected function countIn( string $path, bool $recursive = FALSE ): array
	{
		$files		= 0;
		$folders	= 0;
		if( $recursive ){
			$index		= RecursiveFolderLister::getMixedList( $this->pathBase.$path );
			foreach( $index as $entry )
//				if( !$entry->isDot() )
					$entry->isDir() ? $folders++ : $files++;
		}
		else{
			die( "no implemented yet" );
		}
		return ['folders' => $folders, 'files' => $files];
	}

	protected function getPathFromFolderId( int|string $folderId, bool $withBasePath = FALSE ): string
	{
		$path	= '';
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: '.$folderId );
			$path		= $folder->title.'/'.$path;
			$folderId	= $folder->parentId;
		}
		return $withBasePath ? $this->pathBase.$path : $path;
	}

	protected function getStepsFromFolderId( int|string $folderId ): array
	{
		$steps		= [];
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: '.$folderId );
			$steps[$folder->downloadFolderId]	= $folder;
			$folderId	= $folder->parentId;
		}
		return array_reverse( $steps );
	}

	protected function scanRecursive( $parentId, $path, $stats )
	{
		$index	= new DirectoryIterator( $this->pathBase.$path );
		foreach( $index as $entry ){
			if( $entry->isDot() || substr( $entry->getFilename(), 0, 1 ) === '.' )
				continue;
			$nrFolders	= $this->modelFolder->count( ['parentId' => $parentId] );
			$nrFiles	= $this->modelFile->count( ['downloadFolderId' => $parentId] );
			$entryName	= $entry->getFilename();
			if( $entry->isDir() ){
				$data	= [
					'parentId'	=> $parentId,
					'title'		=> $entryName
				];
				$folder	= $this->modelFolder->getByIndices( $data );
				if( $folder )
					$folderId	= $folder->downloadFolderId;
				else{
					$data['rank']		= $nrFolders++;
					$data['createdAt']	= filemtime( $entry->getPathname() );
					$folderId			= $this->modelFolder->add( $data );
					$this->updateNumber( $parentId, 'folder' );
					$stats->folders[]	= (object) ['title' => $entryName, 'path' => $path];
				}
				$this->scanRecursive( $folderId, $path.$entryName.'/',  $stats );
			}
			else if( $entry->isFile() ){
				$data		= [
					'downloadFolderId'	=> $parentId,
					'title'				=> $entryName
				];
				if( !$this->modelFile->count( $data ) ){
					$data['rank']		= $nrFiles++;
					$data['uploadedAt']	= filemtime( $entry->getPathname() );
					$this->modelFile->add( $data );
					$this->updateNumber( $parentId, 'file' );
					$stats->files[]	= (object) ['title' => $entryName, 'path' => $path];
				}
			}
		}
	}

	protected function updateNumber( string $folderId, string $type, $diff = 1 ): void
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
