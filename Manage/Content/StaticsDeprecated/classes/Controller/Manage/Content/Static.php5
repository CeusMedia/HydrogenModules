<?php
/**
 *	Static Content Management Controller.
 *	@category		cmFrameworks.Hydrogen.Modules
 *	@package		Controller.Manage.Content.Statics
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Ceus Media
 *	@version		$Id$
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	Static Content Management Controller.
 *	@category		cmFrameworks.Hydrogen.Modules
 *	@package		Controller.Manage.Content.Statics
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Ceus Media
 *	@version		$Id$
 */
class Controller_Manage_Content_Static extends Controller{

	protected $path;

	protected function __onInit() {
		parent::__onInit();
		$config		= $this->env->getConfig();
		$this->path	= $config->get( 'module.manage_content_statics.path' );
		if( !$this->path ){
			$locales	= $config->get( 'path.locales' );
			$language	= $this->env->language->getLanguage();
			$this->path		= $locales.$language.'/html/';
		}
		if( !file_exists( $this->path ) )
			FS_Folder_Editor::createFolder( $this->path );

		$paths	= [];
		$index	= FS_Folder_RecursiveLister::getFolderList( $this->path );
		foreach( $index as $item )
			$paths[]	= substr( $item->getPathname(), strlen( $this->path ) );
		$this->addData( 'pathContent', $this->path );
		$this->addData( 'paths', $paths );
	}

	public function add() {
		$config		= $this->env->getConfig();									//  @todo	kriss: define and use configured rule
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'add' );

		$name		= $request->get( 'file_name' );
		$path		= $request->get( 'file_path' );
		if( $request->get( 'add' ) ){
			if( !trim( $name ) )
				$messenger->noteError( $words->msgNoName );
			else{
				$path		= trim( $path ) ? $path.'/' : '';
				$filePath	= $path.$name;
				$fileHash	= base64_encode( $filePath );
				$fileUri	= $this->path.$filePath;
				if( file_exists( $fileUri ) )
					$messenger->noteError( $words->msgFileExisting, $filePath );
				else{
					try{
						FS_File_Writer::save( $fileUri, '' );
						$messenger->noteSuccess( $words->msgSuccess, $filePath );
						$this->restart( './manage/content/static/edit/'.$fileHash );
					}
					catch( Exception $e ){
						$messenger->noteFailure( $words->msgWriteError, $filePath, $e->getMessage() );
					}
				}
			}
		}
		$this->restart( './manage/content/static' );
	}

	public function addFolder() {
		$config		= $this->env->getConfig();									//  @todo	kriss: define and use configured rule
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'addFolder' );

		$folderName	= $request->get( 'folder_name' );
		$folderPath	= $request->get( 'folder_path' );
		$folderPath	= trim( $folderPath ) ? $folderPath.'/' : '';
		if( !trim( $folderName ) )
			$messenger->noteError( $words->msgNoName );
		else{
			$folderUri	= $this->path.$folderPath.$folderName;
			if( file_exists( $folderUri ) )
				$messenger->noteError( $words->msgFolderExisting, $folderPath.$folderName );
			else{
				try{
					FS_Folder_Editor::createFolder( $folderUri );
					$messenger->noteSuccess( $words->msgSuccess, $folderPath.$folderName );
				}
				catch( Exception $e ){
					$messenger->noteError( $words->msgWriteError, $folderPath.$folderName, $e->getMessage() );
				}
			}
		}
		$this->restart( './manage/content/static' );
	}

	protected function convertLeadingTabsToSpaces( $content ){
		$lines	= explode( "\n", $content );
		foreach( $lines as $nr => $line )
			while( preg_match( "/^ *\t/", $lines[$nr] ) )
				$lines[$nr]	= preg_replace( "/^( *)\t/", "\\1 ", $lines[$nr] );
		return implode( "\n", $lines );
	}

	protected function convertLeadingSpacesToTabs( $content ){
		$lines	= explode( "\n", $content );
		foreach( $lines as $nr => $line )
			while( preg_match( "/^\t* /", $lines[$nr] ) )
				$lines[$nr]	= preg_replace( "/^(\t*) /", "\\1\t", $lines[$nr] );
		return implode( "\n", $lines );
	}

	public function edit( $fileHash = NULL ) {
		$config		= $this->env->getConfig();									//  @todo	kriss: define and use configured rule
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'edit' );

		$filePath	= base64_decode( $fileHash );
		if( !$filePath ){
			$messenger->noteError( $words->msgInvalidFileHash );
			$this->restart( './manage/content/static' );
		}
		$fileUri	= $this->path.$filePath;
		if( !file_exists( $fileUri ) ){
			$messenger->noteError( $words->msgInvalidFileUri );
			$this->restart( './manage/content/static' );
		}

		$content	= FS_File_Reader::load( $fileUri );

		$newName	= $request->get( 'name' );
		$newPath	= $request->get( 'path' );
		$newContent	= $this->convertLeadingSpacesToTabs( $request->get( 'content' ) );

		if( $request->get( 'do' ) == 'save' ){
			if( !trim( $newName ) )
				$messenger->noteError( $words->msgNoName );
//			if( !trim( $newPath ) )
//				$messenger->noteError( $words->msgNoPath );
			$newPath	= trim( $newPath ) ? $newPath.'/' : '';
			$newFileUri	= $this->path.$newPath.$newName;
			if( !$messenger->gotError() ){
				$editor	= new FS_File_Editor( $fileUri );
				if( $content != $newContent ){
					try{
						$editor->writeString( $newContent);						//  @todo	kriss: security !!!
						$content	= $newContent;
						$messenger->noteSuccess( $words->msgSuccess, $newName, $newPath );
					}
					catch( Exception $e ){
						$messenger->noteFailure( $words->msgWriteError, $newName, $newPath );
					}
				}
				if( $fileUri != $newFileUri ){
					if( file_exists( $newFileUri ) )
						$messenger->noteError( $words->msgFileExisting, $newName, $newPath );
					else{
						try{
							$editor->rename( $newFileUri );
							$filePath	= $newPath.$newName;
							$fileHash	= base64_encode( $filePath );
							$fileUri	= $this->path.$filePath;
#							$messenger->noteFailure( $words->msgSuccessRenamed, $name, $path, $e->getMessage() );
							$this->restart( './manage/content/static/edit/'.$fileHash );
						}
						catch( Exception $e ){
							$messenger->noteFailure( $words->msgRenameError, $name, $path, $e->getMessage() );
						}
					}
				}

			}
#			$this->restart( './manage/content/static' );
		}
		$this->addData( 'fileHash', $fileHash );
		$this->addData( 'fileUri', $fileUri );
		$this->addData( 'filePath', $filePath );
		$this->addData( 'fileName', basename( $filePath ) );
		$this->addData( 'pathName', dirname( $filePath ) );
		$this->addData( 'content', $this->convertLeadingTabsToSpaces( $content ) );
		$this->loadFileTree();
	}

	public function index() {
#		$request	= $this->env->getRequest();
#		$this->addData( 'filename', $request->get( 'key' ) );
		$this->loadFileTree();
	}

	protected function loadFileTree(){
		$files	= new FS_File_RecursiveRegexFilter( $this->path, '/\.html$/' );
		$this->addData( 'files', $files );
	}

	public function remove( $fileHash ){
		$config		= $this->env->getConfig();									//  @todo	kriss: define and use configured rule
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'remove' );

		$filePath	= base64_decode( $fileHash );
		if( !$filePath ){
			$messenger->noteError( $words->msgInvalidFileHash );
			$this->restart( './manage/content/static' );
		}
		$fileUri	= $this->path.$filePath;
		if( !file_exists( $fileUri ) ){
			$messenger->noteError( $words->msgInvalidFileUri );
			$this->restart( './manage/content/static' );
		}
		if( @unlink( $fileUri ) ){
			$messenger->noteSuccess( $words->msgSuccess, $filePath );
			$this->restart( './manage/content' );
		}
		$messenger->noteSuccess( $words->msgWriteError, $filePath );
		$this->restart( './manage/content/static/edit/'.$fileHash );
	}
}
?>
