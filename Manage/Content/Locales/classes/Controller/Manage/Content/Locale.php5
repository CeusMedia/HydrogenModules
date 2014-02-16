<?php
/**
 *	Content Controller.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	Content Controller.
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2014 Ceus Media
 *	@version		$Id$
 */
class Controller_Manage_Content_Locale extends CMF_Hydrogen_Controller {

	protected $languages	= array();

	protected $types	= array(
		'language'	=> array(
			'folder'		=> '',
			'extensions'	=> 'ini'
		),
		'html'		=> array(
			'folder'		=> 'html/',
			'extensions'	=> 'html'
		),
		'mail'		=> array(
			'folder'		=> 'mail/',
			'extensions'	=> 'html,txt'
		),
	);

	public function	__onInit(){
		$this->config		= $this->env->getConfig()->getAll( 'module.manage_content_locales.', TRUE );
		$this->basePath		= $this->config->get( 'basePath' );
		$this->languages	= $this->getLanguages();
	}

	public function edit( $language, $type, $fileId) {
		$request	= $this->env->getRequest();
		$filePath	= base64_decode( $fileId );
		$pathName	= $this->basePath.'locales/'.$language.'/'.$filePath;
		$words		= (object) $this->getWords( 'msg' );

		switch( $request->get( 'do' ) ){
			case 'save':
				try{
					$content	= $request->get( 'content' );
					$editor		= new File_Editor( $pathName );
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

	protected function getDefaultLanguage(){
		$language	= $this->config->get( 'language.default' );
		if( $language === "auto" )
			$language	= $this->env->getLanguage()->getLanguage()
		if( !trim( $language ) )
			$language	= array_shift( $this->getLanguages() );
		return $language;
	 }

	protected function getLanguages(){
		if( $this->languages )
			return $this->languages;
		$list	= array();
		$index	= new Folder_Lister( $this->basePath.'locales/' );
		foreach( $index->getList() as $folder )
			$list[]	= $folder->getFilename();
		natcasesort( $list );
		return $list;
	}

	public function index( $language = NULL, $type = NULL, $fileId = NULL) {
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'msg' );

		if( $request->getMethod() == "POST" ){
			$url	= $request->get( 'language' ).'/'.$request->get( 'type' ).'/'.$request->get( 'fileId' );
			$this->restart( $url, TRUE );
		}
		if( is_null( $language ) ){
			$language	= $this->getDefaultLanguage();
			$this->restart( $language.'/', TRUE );
		}
		if( is_null( $type ) ){
			$type	= array_shift( array_keys( $this->types ) );
			$this->restart( $language.'/'.$type.'/', TRUE );
		}
		if( $fileId ){
			$filePath	= base64_decode( $fileId );
			$fileUri	= $this->basePath.'locales/'.$language.'/'.$filePath;
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
		}

		$folder		= $this->types[$type]['folder'];
		$extensions	= $this->types[$type]['extensions'] ? explode( ',', $this->types[$type]['extensions'] ) : array();
		$list		= array();
		$path		= $this->basePath.'locales/'.$language.'/';
		if( file_exists( $path.$folder ) ){
			$index	= Folder_RecursiveLister::getFileList( $path.$folder );
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
		$this->addData( 'filePath', $filePath );
		$this->addData( 'fileName', basename( $filePath ) );
	}
}
?>
