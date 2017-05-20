<?php
class Controller_Info_Download extends CMF_Hydrogen_Controller{

	/**	@var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger	*/
	protected $messenger;

	/**	@var	Model_Download_File								$modelFile			Database model of files */
	protected $modelFile;

	/**	@var	Model_Download_Folder							$modelFolder		Database model of folders */
	protected $modelFolder;

	/**	@var	ADT_List_Dictionary								$options			Module configuration object */
	protected $options;

	/**	@var	string											$path				Base path to download files */
	protected $path;

	/**	@var	array											$rights				List of access rights of current user */
	protected $rights		= array();

	public function __onInit(){
		$this->messenger	= $this->env->getMessenger();
		$this->options		= $this->env->getConfig()->getAll( 'module.info_downloads.', TRUE );
		$this->rights		= $this->env->getAcl()->index( 'info/downloads' );
		$this->path			= $this->options->get( 'path' );
		$this->modelFolder	= new Model_Download_Folder( $this->env );
		$this->modelFile	= new Model_Download_File( $this->env );
		$this->messages		= (object) $this->getWords( 'msg' );
	}

	static public function ___onCollectNovelties( $env, $context, $module, $data = array() ){
		$model		= new Model_Download_File( $env );
		$conditions	= array( 'uploadedAt' => '>'.( time() - 270 * 24 * 60 * 60 ) );
		$files		= $model->getAll( $conditions, array( 'uploadedAt' => 'DESC' ) );
		foreach( $files as $file ){
			$context->add( (object) array(
				'module'	=> 'Info_Downloads',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFolderId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/download/download/'.$file->downloadFolderId,
			) );
		}
	}

	static public function ___onPageCollectNews( $env, $context, $module, $data = array() ){
		$model		= new Model_Download_File( $env );
		$conditions	= array( 'uploadedAt' => '>'.( time() - 270 * 24 * 60 * 60 ) );
		$files		= $model->getAll( $conditions, array( 'uploadedAt' => 'DESC' ) );
		foreach( $files as $file ){
			$context->add( (object) array(
				'module'	=> 'Info_Downloads',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFolderId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/download/download/'.$file->downloadFolderId,
			) );
		}
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

	protected function countFolders( $folderId ){
		return $this->modelFolder->count( array( 'parentId' => $folderId ) );
	}

	protected function countIn( $path, $recursive = FALSE ){
		$files		= 0;
		$folders	= 0;
		if( $recursive ){
			$index		= FS_Folder_RecursiveLister::getMixedList( $this->path.$path );
			foreach( $index as $entry )
//				if( !$entry->isDot() )
					$entry->isDir() ? $folders++ : $files++;
		}
		else{
			die( "no implemented yet" );
		}
		return array( 'folders' => $folders, 'files' => $files );
	}

	public function deliver( $fileId = NULL ){
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

	public function download( $fileId ){
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

	public function view( $fileId = NULL ){
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
}
?>
