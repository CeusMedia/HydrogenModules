<?php
class Controller_Info_Manual extends CMF_Hydrogen_Controller{

	protected $path;
	protected $request;
	protected $messenger;
	protected $config;
	protected $files		= array();

	/** @var	ADT_List_Dictionary	$order */
	protected $order;
	protected $ext			= ".md";
	
	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->config		= $this->env->getConfig()->getAll( 'module.info_manual.', TRUE );
		$this->path			= $this->config->get( 'path' );
		$this->order		= new ADT_List_Dictionary();
		$this->isEditable	= $this->config->get( 'editor' );
		
		if( !file_exists( $this->path ) )
			throw new RuntimeException( 'Path "'.$this->path.'" is not existing' );

		$this->scanFiles();
		$orderFile	= $this->path.'order.list';
		if( file_exists( $this->path.'order.list' ) ){
			$order			= trim( File_Reader::load( $orderFile ) ); 
			$this->order	= new ADT_List_Dictionary( explode( "\n", $order ) );
		}
		else{
			$this->order	= new ADT_List_Dictionary( array_values( $this->files ) );
			$this->saveOrder();
		}

		$this->addData( 'path', $this->path );
		$this->addData( 'moduleConfig', $this->config );
		$this->addData( 'files', $this->files );
		$this->addData( 'order', $this->order );
	}
	
	public function add(){
		if( !$this->isEditable )
			$this->restart( NULL, TRUE );
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'add' );
			$fileName	= trim( $this->request->get( 'filename' ) );
			if( !$fileName )
				$this->messenger->noteError( $words->msgErrorFilenameMissing );
			else{
				$filePath	= $this->path.$fileName.$this->ext;
				if( file_exists( $filePath ) )
					$this->messenger->noteError( $words->msgErrorFileExisting, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
				else{
					Folder_Editor::createFolder( dirname( $filePath ) );
					File_Writer::save( $filePath, $this->request->get( 'content' ) );
					$this->order[]	= $fileName.$this->ext;
					$this->saveOrder();
					$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
					$this->restart( 'view/'.$this->urlencode( $fileName ), TRUE );
				}
			}
		}
		$this->addData( 'filename', $this->request->get( 'filename' ) );
		$this->addData( 'content', $this->request->get( 'content' ) );
	}
	
	public function edit( $fileHash, $version = NULL ){
		$fileName	= base64_decode( $fileHash );

		if( !$this->isEditable )
			$this->restart( 'view/'.$this->urlencode( $fileName ), TRUE );

		$filePath	= $this->path.$fileName.$this->ext;
		$resource	= new File_Editor( $filePath );
		$content	= $resource->readString();

		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'edit' );
			if( !is_writable( $filePath ) ){
				$this->messenger->noteFailure( $words->msgErrorNotWritable );
			}
			else{
				$backup		= new File_Backup( $filePath );
				if( $content === $this->request->get( 'content' ) )
					$this->messenger->noteNotice( $words->msgNoChanges );
				else{
					if( $this->request->has( 'backup' ) )
						$backup->store();
					$resource->writeString( $this->request->get( 'content' ) );
					$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
				}
				if( $this->request->get( 'filename' ) !== $fileName ){
					$newName	= $this->request->get( 'filename' );
					$targetFile	= $this->path.$newName.$this->ext;
					if( file_exists( $targetFile ) ){
						$this->messenger->noteError( $words->msgErrorFileExists, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
						$this->restart( 'edit/'.$fileHash, TRUE );
					}
					else{
						try{
							Folder_Editor::createFolder( dirname( $targetFile ) );
							$resource->rename( $targetFile );
							$index		= $this->order->getKeyOf( $fileName.$this->ext );
							$this->order->set( $index, $newName.$this->ext );
							$this->saveOrder();
							$this->relink( $fileName, $newName );
							$this->messenger->noteSuccess( $words->msgSuccessRenamed, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ), htmlentities( $newName, ENT_QUOTES, 'UTF-8' ) );
							$fileName	= $newName;
						}
						catch( Exception $e ){
							$this->messenger->noteFailure( $words->msgErrorRename, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
							$this->restart( 'edit/'.$fileHash, TRUE );
						}
					}
				}
			}
			$this->restart( 'view/'.$this->urlencode( $fileName ), TRUE );
		}
		$this->addData( 'file', $fileName );
		$this->addData( 'content', $content );
	}
	
	public function index(){
		if( $this->files && $this->order ){
			$file	= preg_replace( "/\.md/", "", $this->order[0] );
			$this->restart( 'view/'.$this->urlencode( $file ), TRUE );
		}
		$this->messenger->noteNotice( 'Keine Seiten vorhanden.' );
		$this->addData( 'files', $this->files );
	}

	public function moveDown( $fileHash ){
		$words		= (object) $this->getWords( 'move' );
		$fileName	= base64_decode( $fileHash );
		if( !$this->isEditable )
			$this->restart( 'view/'.$this->urlencode( $fileName ), TRUE );
		if( !in_array( $fileName.$this->ext, $this->files ) ){
			$this->messenger->noteError( $words->msgErrorFileNotFound, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
			$this->restart( NULL, TRUE );
		}
		$index	= $this->order->getKeyOf( $fileName.$this->ext );		
		if( is_int( $index ) && $index >= 0 && $index < $this->order->count() - 1 ){
			$temp	= $this->order->get( $index + 1 );
			$this->order->set( $index + 1, $fileName.$this->ext );
			$this->order->set( $index, $temp );
			$this->saveOrder();
		}
		$this->restart( 'edit/'.$fileHash, TRUE );
	}

	public function moveUp( $fileHash ){
		$words		= (object) $this->getWords( 'move' );
		$fileName	= base64_decode( $fileHash );
		if( !$this->isEditable )
			$this->restart( 'view/'.$this->urlencode( $fileName ), TRUE );
		if( !in_array( $fileName.$this->ext, $this->files ) ){
			$this->messenger->noteError( $words->msgErrorFileNotFound, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
			$this->restart( NULL, TRUE );
		}
		$index	= $this->order->getKeyOf( $fileName.$this->ext );
		if( is_int( $index ) && $index > 0 ){
			$temp	= $this->order->get( $index - 1 );
			$this->order->set( $index - 1, $fileName.$this->ext );
			$this->order->set( $index, $temp );
			$this->saveOrder();
		}
		$this->restart( 'edit/'.$fileHash, TRUE );
	}

	protected function relink( $oldName, $newName ){
		$this->scanFiles();
		foreach( $this->files as $entry ){
			$filePath	= $this->path.$entry;
			$content	= File_Reader::load( $filePath );
			$relinked	= str_replace( "](".$oldName.")", "](".$newName.")", $content );
			$relinked	= str_replace( "]: ".$oldName."\r\n", "]: ".$newName."\r\n", $relinked );
			if( $relinked !== $content )
				File_Writer::save( $filePath, $relinked );
		}
	}

	public function reload(){		
		$orderFile	= $this->path.'order.list';
		$new		= array_diff( $this->files, $this->order->getAll() );
		$outdated	= array_diff( $this->order->getAll(), $this->files );
		foreach( $new as $entry )
			$this->order[]	= $entry;
		foreach( $outdated as $entry )
			$this->order->remove( $this->order->getKeyOf( $entry ) );
		$this->saveOrder();
		$this->restart( getEnv( 'HTTP_REFERER' ) );
	}

	public function remove( $fileHash ){
		$words		= (object) $this->getWords( 'remove' );
		$fileName	= base64_decode( $fileHash );
		if( !$this->isEditable )
			$this->restart( 'view/'.$this->urlencode( $fileName ), TRUE );
		$filePath	= $this->path.$fileName.$this->ext;

		if( !file_exists( $filePath ) )
			$this->messenger->noteError( $words->msgErrorFileMissing, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
		else{
			if( !File_Editor::delete( $filePath ) )
				$this->messenger->noteFailure( $words->msgErrorDelete, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
			else{
				$this->order->remove( $this->order->getKeyOf( $fileName.$this->ext ) );
				$this->saveOrder();
				$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
			}
		}
		$this->restart( NULL, TRUE );
	}

	protected function saveOrder(){
		$orderFile	= $this->path.'order.list';
		File_Writer::save( $orderFile, implode( "\n", $this->order->getAll() ) );
	}

	public function scanFiles(){
		$this->files	= array();
		$index	= new File_RecursiveRegexFilter( $this->path, "/\\".$this->ext."$/" );
		foreach( $index as $entry ){
			$pathName	= substr( $entry->getPathname(), strlen( $this->path ) );
			$this->files[]	= $pathName;
			natcasesort( $this->files );
		}
	}

	protected function urlencode( $name ){
		return str_replace( "%2F", "/", rawurldecode( $name ) );
	}

	public function view( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL ){
		$words		= (object) $this->getWords( 'index' );
		$fileName	= join( "/", func_get_args() );

		if( !strlen( trim( $fileName ) ) )
			$this->restart( NULL, TRUE );

		if( !in_array( $fileName.$this->ext, $this->files ) ){
			$this->messenger->noteNotice( $words->msgErrorFileNotFound, htmlentities( $fileName, ENT_QUOTES, 'UTF-8' ) );
			$this->restart( NULL, TRUE );
		}
		
		$content	= File_Reader::load( $this->path.$fileName.$this->ext );
		foreach( $this->files as $entry ){
			$entry	= preg_replace( "/\.md$/", "", $entry );
			$content	= str_replace( "](".$entry.")", "](./info/manual/view/".$this->urlencode( $entry ).")", $content );
			$content	= str_replace( "]: ".$entry."\r\n", "]: ./info/manual/view/".$this->urlencode( $entry )."\r\n", $content );
		}
		$this->addData( 'file', $fileName );
		$this->addData( 'files', $this->files );
		$this->addData( 'content', $content );
	}
}
?>
