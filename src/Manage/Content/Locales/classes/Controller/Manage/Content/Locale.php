<?php
/**
 *	Locale Content Management Controller.
 *	@category		cmApps
 *	@package		Chat.Admin.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\Editor as FileEditor;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\Folder\Lister as FolderLister;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

/**
 *	Locale Content Management Controller.
 *	@category		cmApps
 *	@package		Chat.Admin.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Manage_Content_Locale extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected array $languages;
	protected string $language;
	protected string $basePath;
	protected string $folderPathFull;
	protected Model_Provision_Product_License $modelLicense;
	protected ?string $folder		= NULL;
	protected ?string $file			= NULL;

	public static array $folders	= [
		'locale'		=> '',
		'html'			=> 'html/',
		'mail'			=> 'mail/',
	];

	public static string $filterPrefix	= 'filter_manage_content_locale_';

	public function edit( string $folder, string $language, string $file ): void
	{
		$this->setFolder( $folder );
		$this->setLanguage( $language );
		$this->setFile( base64_decode( $file ) );

		$words		= (object) $this->getWords( 'msg' );
		$folderPath	= static::$folders[$this->folder];
		$pathName	= $this->basePath.$this->language.'/'.$folderPath.$this->file;
		switch( $this->request->get( 'do' ) ){
			case 'save':
				try{
					$content	= $this->request->get( 'content' );
#					$content	= $this->convertLeadingSpacesToTabs( $content );
					$editor		= new FileEditor( $pathName );
					$editor->writeString( $content );
					$this->env->getMessenger()->noteSuccess( sprintf( $words->successSaved, $this->file ) );
				}
				catch( Exception $e ){
					$this->env->getMessenger()->noteError( sprintf( $words->errorError, $e->getMessage() ) );
				}
				break;
		}

		if( $this->file ){
			$folderPath	= static::$folders[$this->folder];
			$filePath	= $this->basePath.$this->language.'/'.$folderPath.$this->file;
			if( !file_exists( $filePath ) ){
				$this->messenger->noteNotice( $words->noticeNotInThisLanguage );
				$this->session->remove( static::$filterPrefix.'file' );
				$this->restart( NULL, TRUE );
			}
			if( !is_writeable( $filePath ) ){
				$this->messenger->noteNotice( $words->noticeNotWritable );
			}
			$this->addData( 'filePath', $filePath );
			$this->addData( 'content', FileReader::load( $filePath ) );
			$this->addData( 'readonly', !is_writeable( $filePath ) );

			$editors	= [];
			if( $this->env->getModules()->has( 'JS_Ace' ) )
				$editors[]	= 'Ace';
			if( $this->env->getModules()->has( 'JS_CodeMirror' ) )
				$editors[]	= 'CodeMirror';
			if( $this->env->getModules()->has( 'JS_TinyMCE' ) && $this->folder !== 'locale' )
				$editors[]	= 'TinyMCE';
			$this->addData( 'editors', $editors );

			$ext	= strtolower( pathinfo( $this->file, PATHINFO_EXTENSION ) );
			$editor	= $this->session->get( static::$filterPrefix.'editor_'.$ext );
			$editor	= $editor ?: $this->session->get( static::$filterPrefix.'editor' );
			$editor	= $editor ?: array_shift( $editors );
			$editor	= $editor ?: 'Plain';
			$this->addData( 'editor', $editor );
			$this->addData( 'editorByExt', $this->session->get( static::$filterPrefix.'editor_'.$ext ) );
			$this->env->getCaptain()->disableHook( 'View', 'onRenderContent' );
		}
	}

	public function filter( $reset = NULL ): void
	{
		if( $reset ){
			$this->session->remove( static::$filterPrefix.'folder' );
			$this->session->remove( static::$filterPrefix.'language' );
			$this->session->remove( static::$filterPrefix.'file' );
			$this->session->remove( static::$filterPrefix.'empty' );
		}
		if( $this->request->get( 'folder' ) )
			$this->setFolder( $this->request->get( 'folder' ) );
		if( $this->request->get( 'language' ) )
			$this->setLanguage( $this->request->get( 'language' ) );

		$setEmpty	= (bool) $this->request->get( 'empty' );
		$hasEmpty	= (bool) $this->session->get( static::$filterPrefix.'empty' );
		if( $setEmpty !== $hasEmpty )
			$this->session->set( static::$filterPrefix.'empty', $setEmpty );

		if( $this->file )
			$this->restart( vsprintf( 'edit/%s/%s/%s', array(
				$this->folder,
				$this->language,
				base64_encode( $this->file ),
			) ), TRUE );

		$this->restart( NULL, TRUE );
	}

	public function index( ?string $folder = NULL, ?string $language = NULL ): void
	{
		if( NULL !== $folder ){
			$this->setFolder( $folder );
			if( NULL !== $language )
				$this->setLanguage( $language );
			$this->restart( NULL, TRUE );
		}
	}

	public function setEditor(): void
	{
		$editor		= $this->request->get( 'editor' );
		$ext		= $this->request->get( 'ext' );
		$default	= $this->request->get( 'default' );
		$from		= $this->request->get( 'from' );

		if( $editor ){
			$this->session->set( static::$filterPrefix.'editor', $editor );
			if( $default )
				$this->session->set( static::$filterPrefix.'editor_'.$ext, $editor );
			if( !$default )
				$this->session->remove( static::$filterPrefix.'editor_'.$ext );
		}
		$this->restart( vsprintf( 'edit/%s/%s/%s', array(
			$this->folder,
			$this->language,
			base64_encode( $this->file ),
		) ), TRUE );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_content_locales.', TRUE );
		$this->basePath		= $this->env->getConfig()->get( 'path.locales' );
		$this->language		= $this->env->getConfig()->get( 'locale.default' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend		= Logic_Frontend::getInstance( $this->env );
			$this->basePath	= $frontend->getPath( 'locales' );
			$this->language	= $frontend->getConfigValue( 'locale.default' );
		}
		if( $this->session->get( static::$filterPrefix.'folder' ) )
			$this->folder	= $this->session->get( static::$filterPrefix.'folder' );

		$this->languages	= $this->getLanguages();
		if( $this->session->get( static::$filterPrefix.'language' ) )
			$this->language	= $this->session->get( static::$filterPrefix.'language' );

		$this->basePath		= preg_replace( '/^\.\//', '', $this->basePath );
		$this->file			= $this->session->get( static::$filterPrefix.'file' );
		$this->folder		= $this->session->get( static::$filterPrefix.'folder' );
		if( !$this->folder )
			$this->setFolder( 'html' );
//		$this->addData( 'basePath', $this->basePath );
//		$this->addData( 'moduleConfig', $this->moduleConfig );
		$folderPath		= static::$folders[$this->folder];
		$this->folderPathFull	= $this->basePath.$this->language.'/'.$folderPath;
		$this->addData( 'filterEmpty', $this->session->get( static::$filterPrefix.'empty' ) );
		$this->addData( 'folders', static::$folders );
		$this->addData( 'folder', $this->folder );
		$this->addData( 'folderPath', $folderPath );
		$this->addData( 'folderPathFull', $this->folderPathFull );
		$this->addData( 'file', $this->file );
		$this->addData( 'language', $this->language );
		$this->addData( 'languages', $this->languages );
		$this->indexFiles();
	}

/*	protected function convertLeadingTabsToSpaces( $content )
	{
		$lines	= explode( "\n", $content );
		foreach( $lines as $nr => $line )
			while( preg_match( "/^ *\t/", $lines[$nr] ) )
				$lines[$nr]	= preg_replace( "/^( *)\t/", "\\1 ", $lines[$nr] );
		return implode( "\n", $lines );
	}

	protected function convertLeadingSpacesToTabs( $content )
	{
		$lines	= explode( "\n", $content );
		foreach( $lines as $nr => $line )
			while( preg_match( "/^\t* /", $lines[$nr] ) )
				$lines[$nr]	= preg_replace( "/^(\t*) /", "\\1\t", $lines[$nr] );
		return implode( "\n", $lines );
	}*/

	protected function getLanguages(): array
	{
		$index	= new FolderLister( $this->basePath );
		$list	= [];
		foreach( $index->getList() as $folder )
			$list[]	= $folder->getFilename();
		natcasesort( $list );
		return $list;
	}

	protected function indexFiles(): void
	{
		$list		= [];
		foreach( static::$folders as $folderKey => $folderPath ){
			if( $folderKey !== $this->folder )
				continue;
			$path	= $this->basePath.$this->language.'/';
			if( file_exists( $path.$folderPath ) ){
				$index	= RecursiveFolderLister::getFileList( $path.$folderPath );
				foreach( $index as $item ){
					if( !str_ends_with( $item->getFilename(), '~' ) ){
						$pathName	= substr( $item->getPathname(), strlen( $path ) );
						if( $this->folder === 'locale' ){
							if( str_starts_with( $pathName, 'html/' ) )
								continue;
							if( str_starts_with( $pathName, 'mail/' ) )
								continue;
						}
						$content	= FileReader::load( $item->getPathname() );
						$content	= preg_replace( "/<!--(.|\s)*?-->/", "", $content );			//  @todo better: ungreedy
						$pathName	= substr( $pathName, strlen( $folderPath ) );
						$root		= preg_match( '/\//', $pathName ) ? 1 : 0;
						$list[$root.'_'.$pathName]	= (object) [
							'pathName'	=> $pathName,
							'fileName'	=> $item->getFilename(),
							'baseName'	=> pathinfo( $item->getFilename(), PATHINFO_FILENAME ),
							'extension'	=> pathinfo( $item->getFilename(), PATHINFO_EXTENSION ),
							'size'		=> strlen( trim( $content ) ),
						];
					}
				}
			}
		}
		ksort( $list );
		$this->addData( 'files', $list );
	}

	protected function setFile( ?string $file ): ?bool
	{
		if( $this->file === $file )
			return NULL;
		if( $file == NULL || !strlen( trim( $file ) ) ){
			$this->session->remove( static::$filterPrefix.'file' );
			$this->file	= NULL;
			$this->addData( 'file', NULL );
			return TRUE;
		}
		$folderPath		= static::$folders[$this->folder];
		$folderPathFull	= $this->basePath.$this->language.'/'.$folderPath;
		if( !file_exists( $folderPathFull.$file ) )
			throw new RuntimeException( 'File "'.$file.'" is not existing in '.$this->folder.' folder' );
		$this->session->set( static::$filterPrefix.'file', $this->file = $file );
		$this->addData( 'file', $this->file );
		return TRUE;
	}

	protected function setFolder( string $folder ): ?bool
	{
		if( $this->folder === $folder )
			return NULL;
		if( !array_key_exists( $folder, static::$folders ) )
			throw new RuntimeException( 'Invalid folder' );
		$this->session->set( static::$filterPrefix.'folder', $this->folder = $folder );
		$this->addData( 'folder', $this->folder );
		$this->setFile( '' );
		return TRUE;
	}

	protected function setLanguage( string $language ): ?bool
	{
		if( $this->language === $language )
			return NULL;
		if( !in_array( $language, $this->languages ) )
			throw new RuntimeException( 'Invalid language' );
		$this->session->set( static::$filterPrefix.'language', $this->language = $language );
		$this->addData( 'language', $this->language );
		return TRUE;
	}
}
