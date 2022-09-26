<?php
/**
 *	Content Controller.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2014 Ceus Media
 */

use CeusMedia\Common\FS\File\Editor as FileEditor;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\HydrogenFramework\Controller;

/**
 *	Content Controller.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2014 Ceus Media
 */
class Controller_Manage_Content_Locale extends Controller
{
	protected $frontend;
	protected $languages	= [];
	protected $types		= array(
		'language'	=> array(
			'folder'		=> '',
			'extensions'	=> 'ini'
		),
		'html'		=> array(
			'folder'		=> 'html/',
			'extensions'	=> 'html,md'
		),
		'mail'		=> array(
			'folder'		=> 'mail/',
			'extensions'	=> 'html,txt'
		),
	);

	public function ajaxSaveContent()
	{
		$request	= $this->env->getRequest();
		$language	= $request->get( 'language' );
		$fileId		= base64_decode( $request->get( 'fileId' ) );
		if( $language && $fileId ){
			$pathName	= $this->basePath.$language.'/'.$fileId;
			try{
				$content	= $request->get( 'content' );
				$editor		= new FileEditor( $pathName );
				$editor->writeString( $content );
				$this->handleJsonResponse( 'data', TRUE );
			}
			catch( Exception $e ){
				$this->handleJsonResponse( 'error', $e->getMessage() );
			}
		}
		exit;
	}

	public function edit( $language, $type, $fileId )
	{
		$request	= $this->env->getRequest();
		$filePath	= base64_decode( $fileId );
		$pathName	= $this->basePath.$language.'/'.$filePath;
		$words		= (object) $this->getWords( 'msg' );

		switch( $request->get( 'do' ) ){
			case 'save':
				try{
					$content	= $request->get( 'content' );
					$editor		= new FileEditor( $pathName );
					$editor->writeString( $content );
					$this->env->getMessenger()->noteSuccess( $words->successFileSaved, $fileName );
				}
				catch( Exception $e ){
					$this->env->getMessenger()->noteError( $words->errorException, $e->getMessage() );
				}
				break;
		}
		$this->restart( './manage/content/locale/'.$language.'/'.$type.'/'.$fileId );
	}

	public function index( $language = NULL, $type = NULL, $fileId = NULL )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'msg' );

		if( $request->getMethod()->isPost() ){
			$url	= $request->get( 'language' ).'/'.$request->get( 'type' ).'/'.$request->get( 'fileId' );
			$this->restart( $url, TRUE );
		}
		if( is_null( $language ) ){
			$language	= $this->getDefaultLanguage();
			$this->restart( $language.'/', TRUE );
		}
		if( is_null( $type ) ){
			$types	= array_keys( $this->types );
			$type	= array_shift( $types );
			$this->restart( $language.'/'.$type.'/', TRUE );
		}
		if( $fileId ){
			$filePath	= base64_decode( $fileId );
			$fileUri	= $this->basePath.$language.'/'.$filePath;
			if( !file_exists( $fileUri ) ){
				$messenger->noteNotice( $words->errorFileNotExisting );
				$this->restart( $language.'/'.$type.'/', TRUE );
			}
			else{
				if( !is_writeable( $fileUri ) )
					$messenger->noteNotice( $words->errorFileNotWritable );
				else
					$this->addData( 'content', file_get_contents( $fileUri ) );
			}
			$this->addData( 'filePath', $filePath );
			$this->addData( 'fileName', basename( $filePath ) );
		}

		$folder		= $this->types[$type]['folder'];
		$extensions	= $this->types[$type]['extensions'] ? explode( ',', $this->types[$type]['extensions'] ) : array();
		$list		= [];
		$path		= $this->basePath.$language.'/';
		if( file_exists( $path.$folder ) ){
			$index	= RecursiveFolderLister::getFileList( $path.$folder );
			foreach( $index as $item ){
				$extension	= pathinfo( $item->getFilename(), PATHINFO_EXTENSION );
				if( $extensions && !in_array( $extension, $extensions ) )
					continue;
				if( substr( $item->getFilename(), -1 ) === "~" )
					continue;
				$list[substr( $item->getPathname(), strlen( $path ) )]	= $item->getFilename();
			}
		}
		ksort( $list );
		$this->addData( 'languages', $this->languages );
		$this->addData( 'types', $this->types );
		$this->addData( 'files', $list );
		$this->addData( 'language', $language );
		$this->addData( 'type', $type );
		$this->addData( 'fileId', $fileId );
	}

	protected function	__onInit()
	{
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_content_locales.', TRUE );
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->basePath		= $this->frontend->getPath( 'locales' );
		$this->languages	= $this->frontend->getLanguages();
	}

	protected function getDefaultLanguage()
	{
		$locales	= $this->frontend->getLanguages();
		return array_shift( $locales );
	}
}
