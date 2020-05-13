<?php
class Controller_Info_File extends CMF_Hydrogen_Controller{

	/**	@var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger	*/
	protected $messenger;

	/**	@var	Model_Download_File								$modelFile			Database model of files */
	protected $modelFile;

	/**	@var	Model_Download_Folder							$modelFolder		Database model of folders */
	protected $modelFolder;

	/**	@var	ADT_List_Dictionary								$options			Module configuration object */
	protected $options;

	/**	@var	string											$path				Base path to files */
	protected $path;

	/**	@var	ADT_List_Dictionary								$request			Object to map request parameters */
	protected $request;

	/**	@var	array											$rights				List of access rights of current user */
	protected $rights		= array();

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->options		= $this->env->getConfig()->getAll( 'module.info_files.', TRUE );
		$this->rights		= $this->env->getAcl()->index( 'info/file' );
		$this->path			= $this->options->get( 'path' );
		$this->modelFolder	= new Model_Download_Folder( $this->env );
		$this->modelFile	= new Model_Download_File( $this->env );
		$this->messages		= (object) $this->getWords( 'msg' );
	}

	public function addFolder( $folderId = NULL ){
		$path		= $this->getPathFromFolderId( $folderId );
		$folder		= trim( $this->request->get( 'folder' ) );
		if( preg_match( "/[\/\?:]/", $folder) ){
			$this->messenger->noteError( 'Folgende Zeichen sind in Ordnernamen nicht erlaubt: / : ?' );
			$url	= ( $folderId ? $folderId : NULL).'?input_folder='.rawurlencode( $folder );
			$this->restart( $url, TRUE );
		}
		else if( file_exists( $this->path.$path.$folder ) ){
			$this->messenger->noteError( sprintf( 'Ein Eintrag <small>(ein Ordner oder eine Datei)</small> mit dem Namen "%s" existiert in diesem Ordner bereits.', $folder ) );
			$this->restart( $folderId.'?input_folder='.rawurlencode( $folder ), TRUE );
		}
		else{
			FS_Folder_Editor::createFolder( $this->path.$path.$folder );
			$this->messenger->noteSuccess( 'Ordner <b>"%s"</b> hinzugefügt.', $folder );
			$newId	= $this->modelFolder->add( array(
				'parentId'	=> (int) $folderId,
				'rank'		=> $this->countFolders( $folderId ),
				'title'		=> $folder,
				'createdAt'	=> time(),
			) );
			$this->updateNumber( $folderId, 'folder', 1 );
			$this->restart( 'index/'.$folderId, TRUE );
		}
	}

	public function ajaxRenameFolder(){
		$folderId	= $this->request->get( 'folderId' );
		$title		= $this->request->get( 'name' );

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

	protected function checkFolder( $folderId ){
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

	protected function cleanRecursive( $parentId, $path, $stats ){
		$path		= $this->getPathFromFolderId( $parentId, FALSE );
		$folders	= $this->modelFolder->getAll( array( 'parentId' => $parentId ) );
		$files		= $this->modelFile->getAll( array( 'downloadFolderId' => $parentId ) );
		foreach( $folders as $folder ){
			$this->cleanRecursive( $folder->downloadFolderId, $path.$folder->title.'/', $stats );
			if( !file_exists( $this->path.$path.$folder->title ) ){
				$this->modelFolder->remove( $folder->downloadFolderId );
				$stats->folders[]	= (object) array( 'title' => $folder->title, 'path' => $path );
			}
		}
		foreach( $files as $file ){
			if( !file_exists( $this->path.$path.$file->title ) ){
				$this->modelFile->remove( $file->downloadFileId );
				$stats->files[]	= (object) array( 'title' => $file->title, 'path' => $path );
			}
		}
	}

	protected function countFolders( $folderId ){
		return $this->modelFolder->count( array( 'parentId' => $folderId ) );
	}

	protected function countIn( $path, $recursive = FALSE ){
		$files		= 0;
		$folders	= 0;
		if( $recursive ){
			$index		= FS_Folder_RecursiveLister::getMixedList( $this->path.$path );
			foreach( $index as $entry )
				$entry->isDir() ? $folders++ : $files++;
		}
		else{
			$index		= FS_Folder_Lister::getMixedList( $this->path.$path );
			foreach( $index as $entry )
				$entry->isDir() ? $folders++ : $files++;
		}
		return (object) array( 'folders' => $folders, 'files' => $files );
	}

	public function deliver( $fileId = NULL ){
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		if( !file_exists( $path.$file->title ) ){
			$this->messenger->noteError( 'Die Datei wurde nicht am Speicherort gefunden. Bitte informieren Sie den Administator!' );
			$this->restart( 'index/'.$file->downloadFolderId, TRUE );
		}
		$mimeType	= mime_content_type( $path.$file->title );
		header( 'Content-Type: '.$mimeType );
		header( 'Content-Length: '.filesize( $path.$file->title ) );
		$fp = @fopen( $path.$file->title, "rb" );
		if( !$fp )
			header("HTTP/1.0 500 Internal Server Error");
		fpassthru( $fp );
		exit;
	}

	public function download( $fileId ){
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		if( !file_exists( $path.$file->title ) ){
			$this->messenger->noteError( 'Die Datei wurde nicht am Speicherort gefunden. Bitte informieren Sie den Administator!' );
			$this->restart( 'index/'.$file->downloadFolderId, TRUE );
		}
		$this->modelFile->edit( $fileId, array(
			'nrDownloads'	=> $file->nrDownloads + 1,
			'downloadedAt'	=> time(),
		) );
		Net_HTTP_Download::sendFile( $path.$file->title );
		exit;
	}

	public function editFile( $fileId ){
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$data		= array();
			$path		= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
			$title		= $this->request->get( 'title' );
			$folderId	= $this->request->get( 'folderId' );
			if( $title != $file->title ){
				$editor	= new FS_File_Editor( $path.$file->title );
				$editor->rename( $path.$title );
				$data['title']	= $title;
			}
			if( $folderId != $file->downloadFolderId ){
				$pathTarget	= $this->getPathFromFolderId( $folderId, TRUE );
				if( !file_exists( $pathTarget ) ){
					$this->messenger->noteError( 'Target folder is not existing' );
					$this->restart( 'editFile/'.$fileId, TRUE );
				}
				$editor		= new FS_File_Editor( $path.$file->title );
				$editor->rename( $pathTarget.$file->title );
				$this->updateNumbers( $file->downloadFolderId );
				$this->updateNumbers( $folderId );
				$data['downloadFolderId']	= $folderId;
			}
			if( $data )
				$this->modelFile->edit( $fileId, $data );
			$this->restart( 'index/'.$file->downloadFolderId, TRUE );
		}
		$this->addData( 'file', $file );
		$this->addData( 'folderId', -1 );//(int) $file->downloadFolderId );
		$this->addData( 'folderPath', $this->getPathFromFolderId( $file->downloadFolderId ) );
		$this->addData( 'folders', $this->listFolderNested() );
		$this->addData( 'rights', $this->rights );
	}

	public function editFolder( $folderId ){
		$folder		= $this->modelFolder->get( $folderId );
		if( !$folder ){
			$this->messenger->noteError( 'Invalid folder ID: %s', $folderId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$data		= array();
			$path		= $this->getPathFromFolderId( $folder->parentId, TRUE );
			$title		= $this->request->get( 'title' );
			$parentId	= $this->request->get( 'parentId' );
			if( $title != $folder->title ){
				$editor	= new FS_Folder_Editor( $path.$folder->title );
				$editor->rename( $path.$title );
				$data['title']	= $title;
			}
			if( $parentId != $folder->parentId ){
				$pathTarget	= $this->getPathFromFolderId( $parentId, TRUE );
				if( !file_exists( $pathTarget ) ){
					$this->messenger->noteError( 'Target folder is not existing' );
					$this->restart( 'editFolder/'.$folderId, TRUE );
				}
				$editor		= new FS_Folder_Editor( $path.$folder->title );
				$editor->move( $pathTarget );
				$this->updateNumbers( $folder->parentId );
				$this->updateNumbers( $parentId );
				$data['parentId']	= $parentId;
			}
			if( $data )
				$this->modelFolder->edit( $folderId, $data );
			$this->restart( 'index/'.$folder->parentId, TRUE );
		}
		$files	= $this->modelFile->getAll( array( 'downloadFolderId' => $folderId ), array( 'title' => 'ASC' ) );

		$this->addData( 'folder', $folder );
		$this->addData( 'folderPath', $this->getPathFromFolderId( $folder->parentId ) );
		$this->addData( 'folders', $this->listFolderNested( 0, $folderId ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'files', $files );
	}

	protected function listFolderNested( $parentId = 0, $excludeFolderId = 0, $level = 0 ){
		$list		= array();
		$orders		= array( 'title' => 'ASC' );
		$folders	= $this->modelFolder->getAll( array( 'parentId' => $parentId ), $orders );
		$icon		= '<i class="fa fa-fw fa-folder-open"></i>';
		foreach( $folders as $folder ){
			if( $folder->downloadFolderId == $excludeFolderId )
				continue;
			$list[$folder->downloadFolderId]	= str_repeat( '- ', $level ).$folder->title;
			$children	= $this->listFolderNested( $folder->downloadFolderId, $excludeFolderId, $level + 1 );
			foreach( $children as $childId => $childLabel )
				$list[$childId]	= $childLabel;
		}
		return $list;
	}

	protected function getPathFromFolderId( $folderId, $withBasePath = FALSE ){
		$path	= '';
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: %s', $folderId );
			$path		= $folder->title.'/'.$path;
			$folderId	= $folder->parentId;
		}
		return $withBasePath ? $this->path.$path : $path;
	}

	protected function getStepsFromFolderId( $folderId ){
		$steps		= array();
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: %s', $folderId );
			$steps[$folder->downloadFolderId]	= $folder;
			$folderId	= $folder->parentId;
		}
		$steps	= array_reverse( $steps );
		return $steps;
	}

	protected function getNestedFolderIds( $parentId ){
		$list		= array();
		$folders	= $this->modelFolder->getAllByIndex( 'parentId', $parentId );
		foreach( $folders as $folder ){
			$list[]	= $folder->downloadFolderId;
			foreach( $this->getNestedFolderIds( $folder->downloadFolderId ) as $id )
				$list[]	= $id;
		}
		return $list;
	}


	public function index( $folderId = NULL ){
		$search		= trim( $this->request->get( 'search' ) );
		$folderId	= (int) $folderId;

		$folders	= array();
		if( $search ){
			$conditions	= array( 'title' => '%'.$search.'%' );
			if( $folderId ){
				$folderIds	= $this->getNestedFolderIds( $folderId );
				array_unshift( $folderIds, $folderId );
				$conditions['downloadFolderId']	= $folderIds;
			}
			$orders		= array( 'title' => 'ASC' );
			$limits		= array();
			$files		= $this->modelFile->getAll( $conditions, $orders, $limits );
		}
		else{
			$orders		= array( 'rank' => 'ASC' );
			$orders		= array( 'title' => 'ASC' );
			if( $folderId ){
				$folder		= $this->modelFolder->get( $folderId );
				if( !$folder ){
					$this->messenger->noteError( sprintf( 'Invalid folder ID: %s', $folderId ) );
					$this->restart( NULL, TRUE );
				}
				$this->addData( 'folder', $folder );
			}
			$files		= $this->modelFile->getAll( array( 'downloadFolderId' => $folderId ), $orders );
			$orders		= array( 'title' => 'ASC' );
			$folders	= $this->modelFolder->getAll( array( 'parentId' => $folderId ), $orders );
		}

		$this->addData( 'files', $files );
		$this->addData( 'folders', $folders );
		$this->addData( 'folderId', $folderId );
		$this->addData( 'pathBase', $this->path );
		$this->addData( 'folderPath', $this->getPathFromFolderId( $folderId ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'search', $this->request->get( 'search' ) );
	}

	public function rankFolder( $folderId, $downwards = NULL ){
		$words		= (object) $this->getWords( 'msg' );
		$direction	= (boolean) $downwards ? +1 : -1;
		if( !( $folder = $this->modelFolder->get( (int) $folderId ) ) )
			$this->messenger->noteError( $words->errorInvalidFolderId, $folderId );
		else{
			$rank		= $folder->rank + $direction;
			$conditions	= array( 'rank' => $rank, 'parentId' => $folder->parentId );
			if( ( $next = $this->modelFolder->getByIndices( $conditions ) ) ){
				$this->modelFolder->edit( (int) $folderId, array( 'rank' => $rank, 'modifiedAt' => time() ) );
				$this->modelFolder->edit( $next->downloadFolderId, array( 'rank' => $folder->rank, 'modifiedAt' => time() ) );
			}
		}
		$this->restart( 'index/'.$folder->parentId, TRUE );
	}

	public function remove( $fileId ){
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->path;
		if( $file->downloadFolderId ){
			$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		}
		@unlink( $path.$file->title );
		$this->modelFile->remove( $fileId );
		$this->updateNumber( $file->downloadFolderId, 'file', -1 );
		$this->messenger->noteSuccess( 'Datei <b>"%s"</b> entfernt.', $file->title );
		$this->restart( 'index/'.$file->downloadFolderId, TRUE );
	}

	public function removeFolder( $folderId ){
		if( $folderId ){
			$folder		= $this->modelFolder->get( $folderId );
			if( !$folder ){
				$this->messenger->noteError( sprintf( 'Invalid folder ID: %s', $folderId ) );
			}
			else{
				$hasSubfolders	= $this->modelFile->count( array( 'downloadFolderId' => $folderId ) );
				$hasSubfiles	= $this->modelFolder->count( array( 'parentId' => $folderId ) );
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

	public function scan(){
		$statsImport	= (object) array( 'folders' => array(), 'files' => array() );
		$statsClean		= (object) array( 'folders' => array(), 'files' => array() );
		$this->scanRecursive( 0, '', $statsImport );
		$this->cleanRecursive( 0, '', $statsClean );

		$addedSomething		= count( $statsImport->folders ) + count( $statsImport->folders ) > 0;
		$removedSomething	= count( $statsClean->folders ) + count( $statsClean->folders ) > 0;
		if( $addedSomething || $removedSomething ){
			if( $addedSomething ){
				$list	= array();
				foreach( $statsImport->files as $file ){
					$path	= UI_HTML_Tag::create( 'small', $file->path, array( 'class' => "muted" ) );
					$list[]	= UI_HTML_Tag::create( 'li', $path.$file->title );
				}
				$list	= UI_HTML_Tag::create( 'ul', $list );
				$this->messenger->noteNotice( $this->messages->infoScanFoundSomething, count( $statsImport->folders ), count( $statsImport->files ), $list );
			}
			if( $removedSomething ){
				$list	= array();
				foreach( $statsClean->files as $file ){
					$path	= UI_HTML_Tag::create( 'small', $file->path, array( 'class' => "muted" ) );
					$list[]	= UI_HTML_Tag::create( 'li', $path.$file->title );
				}
				$list	= UI_HTML_Tag::create( 'ul', $list );
				$this->messenger->noteNotice( $this->messages->infoScanRemovedSomething, count( $statsClean->folders ), count( $statsClean->files ), $list );
			}
		}
		else
			$this->messenger->noteNotice( $this->messages->infoScanNoChanges );
		$this->restart( NULL, TRUE );
	}

	protected function scanRecursive( $parentId, $path, $stats ){
		$index	= new DirectoryIterator( $this->path.$path );
		foreach( $index as $entry ){
			if( $entry->isDot() || substr( $entry->getFilename(), 0, 1 ) === '.' )
				continue;
			$nrFolders	= $this->modelFolder->count( array( 'parentId' => $parentId ) );
			$nrFiles	= $this->modelFile->count( array( 'downloadFolderId' => $parentId ) );
			$entryName	= $entry->getFilename();
			if( $entry->isDir() ){
				$data	= array(
					'parentId'	=> $parentId,
					'title'		=> $entryName
				);
				$folder	= $this->modelFolder->getByIndices( $data );
				if( $folder )
					$folderId	= $folder->downloadFolderId;
				else{
					$data['rank']		= $nrFolders++;
					$data['createdAt']	= filemtime( $entry->getPathname() );
					$folderId			= $this->modelFolder->add( $data );
					$this->updateNumber( $parentId, 'folder' );
					$stats->folders[]	= (object) array( 'title' => $entryName, 'path' => $path );
				}
				$this->scanRecursive( $folderId, $path.$entryName.'/',  $stats );
			}
			else if( $entry->isFile() ){
				$data		= array(
					'downloadFolderId'	=> $parentId,
					'title'				=> $entryName,
				);
				if( !$this->modelFile->count( $data ) ){
					$data['rank']		= $nrFiles++;
					$data['size']		= filesize( $entry->getPathname() );
					$data['uploadedAt']	= filemtime( $entry->getPathname() );
					$this->modelFile->add( $data );
					$this->updateNumber( $parentId, 'file' );
					$stats->files[]	= (object) array( 'title' => $entryName, 'path' => $path );
				}
			}
		}
	}

	protected function updateNumbers( $folderId ){
		if( $folderId ){
			$path		= $this->getPathFromFolderId( $folderId, FALSE );
			$counts		= $this->countIn( $path, TRUE );
			$this->modelFolder->edit( $folderId, array(
				'nrFolders'	=> $counts->folders,
				'nrFiles'	=> $counts->files,
			) );
			$folder	= $this->modelFolder->get( $folderId );
			if( $folder->parentId )
				$this->updateNumbers( $folder->parentId );
		}
	}

	protected function updateNumber( $folderId, $type, $diff = 1 ){
		if( !in_array( $type, array( 'folder', 'file' ) ) )
			throw new InvalidArgumentException( 'Type must be folder or file' );
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: %s', $folderId );
			switch( $type ){
				case 'folder':
					$data	= array( 'nrFolders' => $folder->nrFolders + $diff );
					break;
				case 'file':
					$data	= array( 'nrFiles' => $folder->nrFiles + $diff );
					break;
			}
			$data['modifiedAt']	= time();
			$this->modelFolder->edit( $folderId, $data );
			$folderId	= $folder->parentId;
		}
	}

	public function upload( $folderId = NULL ){
		if( !in_array( 'upload', $this->rights ) )
			$this->restart( NULL, TRUE );
		if( $this->request->has( 'save' ) ){
			$upload	= (object) $this->request->get( 'upload' );
			$logicUpload	= new Logic_Upload( $this->env );
			try{
				$logicUpload->setUpload( $upload );
				$logicUpload->checkSize( Logic_Upload::getMaxUploadSize(), TRUE );
//				$logicUpload->checkVirus( TRUE );
				$targetFile	= $this->getPathFromFolderId( $folderId, TRUE ).$upload->name;
				$logicUpload->saveTo( $targetFile );
				$rank	= $this->modelFile->count( array( 'downloadFolderId' => $folderId ) );
				$this->modelFile->add( array(
					'downloadFolderId'	=> $folderId,
					'rank'				=> $rank,
					'size'				=> $logicUpload->getFileSize(),
					'title'				=> $logicUpload->getFileName(),
					'description'		=> (string) $this->request->get( 'description' ),
					'uploadedAt'		=> time()
				) );
				$this->updateNumber( $folderId, 'file', 1 );
				$this->messenger->noteSuccess( 'Datei "%s" hochgeladen.', $upload->name );
			}
			catch( Exception $e ){
				$helperError	= new View_Helper_UploadError( $this->env );
				$helperError->setUpload( $logicUpload );
				$message	= $helperError->render();
				$this->messenger->noteError( $message ? $message : $e->getMessage() );
			}
		}
		$this->restart( 'index/'.$folderId, TRUE );
	}

	public function view( $fileId = NULL ){
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
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
}
?>
