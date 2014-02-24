<?php
class Controller_Info_File extends CMF_Hydrogen_Controller{

	/**	@var	string		$fileIndex	*/
	protected $fileIndex;
	/**	@var	array		$index		*/
	protected $index		= array();
	/**	@var	array		$rights		*/
	protected $rights		= array();
	/**	@var	array		$folders	*/
	protected $folders		= array();

	public function __onInit(){
		$this->messenger	= $this->env->getMessenger();
		$this->options	= $this->env->getConfig()->getAll( 'module.info_files.' );
		$this->rights	= $this->env->getAcl()->index( 'info/file' );
		$this->path		= $this->options['path'];
		$this->fileIndex	= $this->path.'.index';
		$this->index		= array( 'folders' => array(), 'files' => array() );
		if( !file_exists( $this->fileIndex ) )
			$this->saveIndex();
		$this->index			= File_JSON_Reader::load( $this->fileIndex );
		$this->index->folders	= (array) $this->index->folders;
		$this->index->files		= (array) $this->index->files;

		foreach( Folder_RecursiveLister::getFolderList( $this->path ) as $folder ){
			$pathName	= substr( $folder->getPathname(), strlen( $this->path ) );
			$this->folders[$pathName]	= (object) array(
				'name'	=> $pathName,
				'files'	=> 0,
			);
		}
	}

	public function addFolder( $pathId = NULL ){
		$path	= is_null( $pathId ) ? '' : rtrim( base64_decode( $pathId ), '/' ).'/';
		$folder	= trim( $this->env->getRequest()->get( 'folder' ) );
		if( preg_match( "/[\/\?:]/", $folder) ){
			$this->messenger->noteError( 'Folgende Zeichen sind in Ordnernamen nicht erlaubt: / : ?' );
			$this->restart( 'index/'.base64_encode( $path ).'?input_folder='.rawurlencode( $folder ), TRUE );
		}
		else if( file_exists( $this->path.$path.$folder ) ){
			$this->messenger->noteError( sprintf( 'Ein Eintrag <small>(ein Ordner oder eine Datei)</small> mit dem Namen "%s" existiert in diesem Ordner bereits.', $folder ) );
			$this->restart( 'index/'.base64_encode( $path ).'?input_folder='.rawurlencode( $folder ), TRUE );
		}
		else{
			Folder_Editor::createFolder( $this->path.$path.$folder );
			$this->messenger->noteSuccess( 'Ordner <b>"%s"</b> hinzugefügt.', $folder );
			$pathCopy	= rtrim( $path, '/' );
			$this->index->folders[$pathCopy]->folders++;
			while( ( $pathCopy = dirname( $pathCopy ) ) !== '.' )
				$this->index->folders[$pathCopy]->folders++;
			$this->saveIndex();
			$this->restart( 'index/'.base64_encode( $path ), TRUE );
		}
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
		$pathName	= base64_decode( $fileId );
		if( !file_exists( $this->path.$pathName ) ){
			$this->messenger->noteError( 'Invalid file: '.$pathName );
			$this->restart( NULL, TRUE );
		}
		$this->index->files[strtolower( $pathName )]->downloads++;
		$this->index->files[strtolower( $pathName )]->downloaded	= time();
		$this->saveIndex();
		Net_HTTP_Download::sendFile( $this->path.$pathName );
		exit;
	}

	public function index( $pathId = NULL ){
		$path	= is_null( $pathId ) ? '' : rtrim( base64_decode( $pathId ), '/' ).'/';
		if( !file_exists( $this->path.$path ) ){
			$this->messenger->noteError( sprintf( 'Der Ordner "%s" existiert nicht.', $path ) );
			$this->restart( NULL, TRUE );
		}

#		$index	= Folder_RecursiveLister::getFolderList( $this->path );
#		foreach( $index as $folder ){
#			
#		}

		$folders	= array();
		foreach( Folder_Lister::getFolderList( $this->path.$path ) as $folder ){
			$pathName	= substr( $folder->getPathname(), strlen( $this->path ) );
			if( !isset( $this->index->folders[$pathName] ) ){
				$count	= $this->countIn( $pathName, TRUE );
				$this->index->folders[$pathName] = (object) array(
					'folders'	=> $count['folders'],
					'files'		=> $count['files'],
				);
				$this->saveIndex();
			}
			$folders[$pathName]	= (object) array(
				'pathName'		=> $pathName,
				'folderName'	=> basename( $pathName ),
				'files'			=> 0,
				'totalFolders'	=> $this->index->folders[$pathName]->folders,
				'totalFiles'	=> $this->index->folders[$pathName]->files,
			);
		}

		$index	= Folder_Lister::getFileList( $this->path.$path );
		$files	= array();
		foreach( $index as $entry ){
			$pathName	= substr( $entry->getPathname(), strlen( $this->path ) );
			$key		= strtolower( $pathName );
			if( !isset( $this->index->files[$key] ) ){
				$this->index->files[$key]	= (object) array(
					'downloaded'	=> NULL,
					'downloads'		=> 0,
					'timestamp'		=> filemtime( $entry->getPathname() ),
				);
				$this->saveIndex();
			}
			$files[$pathName]	= (object) array(
				'fileName'		=> $entry->getFilename(),
				'pathName'		=> $pathName,
				'downloads'		=> $this->index->files[$key]->downloads,
				'timestamp'		=> $this->index->files[$key]->timestamp,
				'downloaded'	=> $this->index->files[$key]->downloaded,
			);
//			if( preg_match( '/\//', $pathName ) )
//				$folders[dirname( $pathName )]->files++;
		}
#		remark( "Files:" );
#		print_m( $files );
#		remark( "Folders:" );
#		print_m( $folders );
#		die;
		$this->addData( 'files', $files );
		$this->addData( 'folders', $folders );
		$this->addData( 'path', $path );
		$this->addData( 'pathBase', $this->path );
		$this->addData( 'pathId', base64_encode( $path ) );
		$this->addData( 'rights', $this->rights );
	}

	public function remove( $fileId ){
		$pathName	= base64_decode( $fileId );
		$path		= dirname( $pathName ) === '.' ? '' : dirname( $pathName ).'/';
		if( !file_exists( $this->path.$pathName ) ){
			$this->messenger->noteError( 'Invalid file: '.$pathName );
			$this->restart( 'index/'.base64_encode( $path ), TRUE );
		}
		unset( $this->index->files[strtolower( $pathName )] );
		$pathCopy   = rtrim( $pathName, '/' );
		$this->index->folders[pathCopy]->files--;
		while( ( $pathCopy = dirname( $pathCopy ) ) != '.' )
			$this->index->folders[$pathCopy]->files--;
		$this->saveIndex();
		@unlink( $this->path.$pathName );

		$this->messenger->noteSuccess( 'Datei <b>"%s"</b> entfernt.', $pathName );
		$this->restart( 'index/'.base64_encode( $path ), TRUE );
	}

	public function removeFolder( $pathId ){
		$pathName	= base64_decode( $pathId );
		$path		= dirname( $pathName ) === '.' ? '' : dirname( $pathName ).'/';
		if( !file_exists( $this->path.$pathName ) )
			$this->messenger->noteError( 'Invalid path: '.$pathName );
		else{
			$count	= 0;
			foreach( Folder_Lister::getMixedList( $this->path.$pathName ) as $entry )
				$count++;
			if( $count )
				$this->messenger->noteError( 'Der Ordner <b>"%s"</b> ist nicht leer und kann daher nicht entfernt werden.', $pathName );
			else{
				Folder_Editor::removeFolder( $this->path.$pathName );
				$this->messenger->noteSuccess( 'Der Ordner <b>"%s"</b> wurde entfernt.', $pathName );
				$pathCopy	= rtrim( $path, '/' );
				unset( $this->index->folders[$pathCopy] );
				while( ( $pathCopy = dirname( $pathCopy ) ) != '.' )
					$this->index->folders[$pathCopy]->folders--;
				$this->saveIndex();
			}
		}
		$this->restart( 'index/'.base64_encode( $path ), TRUE );
	}

	protected function saveIndex(){
		return File_JSON_Writer::save( $this->fileIndex, $this->index, TRUE );
	}

	public function upload( $pathId = NULL ){
		$path	= is_null( $pathId ) ? '' : rtrim( base64_decode( $pathId ), '/' ).'/';
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
				$targetFile	= $this->path.$path.$upload->name;
				if( !@move_uploaded_file( $upload->tmp_name, $targetFile ) ){
					$this->messenger->noteFailure( 'Moving uploaded file to documents folder failed' );
					$this->restart( NULL, TRUE );
				}
				$this->messenger->noteSuccess( 'Datei "%s" hochgeladen.', $upload->name );
				$this->index->files[strtolower( $targetFile )]	= array(
					'downloaded'	=> NULL,
					'downloads'		=> 0,
					'timestamp'		=> filemtime( $targetFile ),
				);
				$this->index->folders[$path]->files++;
				$this->saveIndex();
			}
		}
		$this->restart( 'index/'.base64_encode( $path ), TRUE );
	}
}
?>
