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

	/**	@var	array											$rights				List of access rights of current user */
	protected $rights		= array();

	public function __onInit(){
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
		$folder		= trim( $this->env->getRequest()->get( 'folder' ) );
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
			Folder_Editor::createFolder( $this->path.$path.$folder );
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
			$index		= Folder_RecursiveLister::getMixedList( $this->path.$path );
			foreach( $index as $entry )
//				if( !$entry->isDot() )
					$entry->isDir() ? $folders++ : $files++;
		}
		else{
			die( "no implemented yet" );
		}
		return array( 'folders' => $folders, 'files' => $files );
	}

	public function download( $fileId ){
		$file		= $this->modelFile->get( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: '.$fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$this->modelFile->edit( $fileId, array(
			'nrDownloads'	=> $file->nrDownloads++,
			'downloadedAt'	=> time(),
		) );
		Net_HTTP_Download::sendFile( $path.$file->title );
		exit;
	}

	protected function getPathFromFolderId( $folderId, $withBasePath = FALSE ){
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

	protected function getStepsFromFolderId( $folderId ){
		$steps		= array();
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

	public function index( $folderId = NULL ){
		$folderId	= (int) $folderId;
		$orders		= array( 'rank' => 'ASC' );
		if( $folderId ){
			$folder		= $this->modelFolder->get( $folderId );
			if( !$folder ){
				$this->messenger->noteError( sprintf( 'Invalid folder ID: '.$folderId ) );
				$this->restart( NULL, TRUE );
			}
		}
		$folders	= $this->modelFolder->getAll( array( 'parentId' => $folderId ), $orders );
		$files		= $this->modelFile->getAll( array( 'downloadFolderId' => $folderId ), $orders );

		$this->addData( 'files', $files );
		$this->addData( 'folders', $folders );
		$this->addData( 'folderId', $folderId );
		$this->addData( 'pathBase', $this->path );
		$this->addData( 'folderPath', $this->getPathFromFolderId( $folderId ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'steps', $this->getStepsFromFolderId( $folderId ) );
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
			$this->messenger->noteError( 'Invalid file ID: '.$fileId );
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
				$this->messenger->noteError( sprintf( 'Invalid folder ID: '.$folderId ) );
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
					'title'				=> $entryName
				);
				if( !$this->modelFile->count( $data ) ){
					$data['rank']		= $nrFiles++;
					$data['uploadedAt']	= filemtime( $entry->getPathname() );
					$this->modelFile->add( $data );
					$this->updateNumber( $parentId, 'file' );
					$stats->files[]	= (object) array( 'title' => $entryName, 'path' => $path );
				}
			}
		}
	}

	protected function updateNumber( $folderId, $type, $diff = 1 ){
		if( !in_array( $type, array( 'folder', 'file' ) ) )
			throw new InvalidArgumentException( 'Type must be folder or file' );
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: '.$folderId );
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
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$upload	= (object) $request->get( 'upload' );
			if( $upload->error ){
				$handler    = new Net_HTTP_UploadErrorHandler();
				$handler->setMessages( $this->getWords( 'msgErrorUpload' ) );
				$this->messenger->noteError( $handler->getErrorMessage( $upload->error ) );
			}
			else{
				$targetFile	= $this->getPathFromFolderId( $folderId, TRUE ).$upload->name;
				if( !@move_uploaded_file( $upload->tmp_name, $targetFile ) ){
					$this->messenger->noteFailure( 'Moving uploaded file to documents folder failed' );
					$this->restart( NULL, TRUE );
				}
				$rank	= $this->modelFile->count( array( 'downloadFolderId' => $folderId ) );
				$this->modelFile->add( array(
					'downloadFolderId'	=> $folderId,
					'rank'				=> $rank,
					'title'				=> $upload->name,
					'uploadedAt'		=> time()
				) );
				$this->updateNumber( $folderId, 'file', 1 );
				$this->messenger->noteSuccess( 'Datei "%s" hochgeladen.', $upload->name );
			}
		}
		$this->restart( 'index/'.$folderId, TRUE );
	}
}
?>
