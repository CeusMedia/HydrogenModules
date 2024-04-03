<?php
/**
 *	Static Content Management Controller.
 *	@category		cmFrameworks.Hydrogen.Modules
 *	@package		Controller.Manage.Content.Statics
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\FS\File\Editor as FileEditor;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\HydrogenFramework\Controller;

/**
 *	Static Content Management Controller.
 *	@category		cmFrameworks.Hydrogen.Modules
 *	@package		Controller.Manage.Content.Statics
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Manage_Content_Static extends Controller
{
	protected string $path;

	public function add(): void
	{
		$config		= $this->env->getConfig();									//  @todo	 define and use configured rule
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
						FileWriter::save( $fileUri, '' );
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

	public function addFolder(): void
	{
		$config		= $this->env->getConfig();									//  @todo	 define and use configured rule
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
					FolderEditor::createFolder( $folderUri );
					$messenger->noteSuccess( $words->msgSuccess, $folderPath.$folderName );
				}
				catch( Exception $e ){
					$messenger->noteError( $words->msgWriteError, $folderPath.$folderName, $e->getMessage() );
				}
			}
		}
		$this->restart( './manage/content/static' );
	}

	public function edit( $fileHash = NULL ): void
	{
		$config		= $this->env->getConfig();									//  @todo	 define and use configured rule
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

		$content	= FileReader::load( $fileUri );

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
				$editor	= new FileEditor( $fileUri );
				if( $content != $newContent ){
					try{
						$editor->writeString( $newContent);						//  @todo	 security !!!
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
						$editor->rename( $newFileUri );
						$filePath	= $newPath.$newName;
						$fileHash	= base64_encode( $filePath );
						$fileUri	= $this->path.$filePath;
#							$messenger->noteFailure( $words->msgSuccessRenamed, $name, $path, $e->getMessage() );
						$this->restart( './manage/content/static/edit/'.$fileHash );
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

	public function index(): void
	{
#		$request	= $this->env->getRequest();
#		$this->addData( 'filename', $request->get( 'key' ) );
		$this->loadFileTree();
	}

	public function remove( string $fileHash ): void
	{
		$config		= $this->env->getConfig();									//  @todo	 define and use configured rule
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

	protected function __onInit(): void
	{
		parent::__onInit();
		$config		= $this->env->getConfig();
		$this->path	= $config->get( 'module.manage_content_statics.path' );
		if( !$this->path ){
			$locales	= $config->get( 'path.locales' );
			$language	= $this->env->language->getLanguage();
			$this->path		= $locales.$language.'/html/';
		}
		if( !file_exists( $this->path ) )
			FolderEditor::createFolder( $this->path );

		$paths	= [];
		$index	= RecursiveFolderLister::getFolderList( $this->path );
		foreach( $index as $item )
			$paths[]	= substr( $item->getPathname(), strlen( $this->path ) );
		$this->addData( 'pathContent', $this->path );
		$this->addData( 'paths', $paths );
	}

	protected function convertLeadingTabsToSpaces( string $content ): string
	{
		$lines	= explode( "\n", $content );
		foreach( $lines as $nr => $line )
			while( preg_match( "/^ *\t/", $lines[$nr] ) )
				$lines[$nr]	= preg_replace( "/^( *)\t/", "\\1 ", $lines[$nr] );
		return implode( "\n", $lines );
	}

	protected function convertLeadingSpacesToTabs( string $content ): string
	{
		$lines	= explode( "\n", $content );
		foreach( $lines as $nr => $line )
			while( preg_match( "/^\t* /", $lines[$nr] ) )
				$lines[$nr]	= preg_replace( "/^(\t*) /", "\\1\t", $lines[$nr] );
		return implode( "\n", $lines );
	}

	protected function loadFileTree(): void
	{
		$files	= new RecursiveRegexFileIndex( $this->path, '/\.html$/' );
		$this->addData( 'files', $files );
	}
}
