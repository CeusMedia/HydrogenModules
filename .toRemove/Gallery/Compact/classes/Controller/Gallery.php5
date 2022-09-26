<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\INI\Reader as IniFileReader;
use CeusMedia\Common\FS\Folder\Lister as FolderLister;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Gallery extends Controller
{
	protected $path;

	public function download( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL )
	{
		$args	= func_get_args();
		$source	= $arg1 ? join( '/', $args ) : $this->env->getRequest()->get( 'source' );
		if( !$source )
			throw new InvalidArgumentException( 'No file name given.' );
		$uri	= $this->path.$source;
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'File is not existing.' );
		Net_HTTP_Download::sendFile( $uri );
	}

	/**
	 *	Generates RSS Feed and returns it directly to the requesting client.
	 *	@access		public
	 *	@param		integer		$limit
	 *	@param		boolean		$debug
	 *	@return		void
	 */
	public function feed( $limit = 10, $debug = NULL )
	{
		$limit		= ( (int) $limit > 0 ) ? (int) $limit : 10;

		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
		$pattern	= $config->get( 'module.gallery_compact.latest.regex' );
		$index		= RecursiveFolderLister::getFolderList( $path, $pattern );
		foreach( $index as $folder ){
			$timestamp	= filemtime( $folder->getPathname() );
			$data		= array(
				'label'		=> $folder->getFilename(),
				'pathname'	=> substr( $folder->getPathname(), strlen( $this->path ) ),
				'timestamp'	=> $timestamp,
				'content'	=> NULL,
			);
			$fileInfo	= $folder->getPathname().'/info.ini';
			if( file_exists( $fileInfo ) ){
				$info	= IniFileReader::load( $fileInfo );
				if( isset( $info['title'] ) )
					$data['label']	= $info['title'];
				if( isset( $info['description'] ) )
					$data['content']	= $info['description'];
			}
			$list[$timestamp.'_'. uniqid()]	= (object) $data;
		}
		ksort( $list );
		$galleries	= array_reverse( array_slice( $list, -$limit ) );
		$this->addData( 'galleries', $galleries );
		$this->addData( 'path', $this->path );
		$this->addData( 'debug', (bool) $debug );
	}

	public function image()
	{
		$this->addData( 'path', $this->path );
		$this->addData( 'source', $this->env->getRequest()->get( 'source' ) );
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL )
	{
		$source	= urldecode( implode( '/', $this->env->getRequest()->get( '__arguments' ) ) );
		$source	= stripslashes( $source );
		$info	= $this->readGalleryInfo( $source );
		$path	= $this->path.$source;
		$this->setData(
			array(
				'path'			=> $this->path,
				'source'		=> $source ? $source.'/' : '',
				'info'			=> $info,
				'folders'		=> FolderLister::getFolderList( $path ),
				'files'			=> FolderLister::getFileList( $path, '/\.(jpg|jpeg|jpe|png|gif)$/i' ),
				'textBottom'	=> '',
			)
		);
	}

	public function info( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL )
	{
		$source	= join( '/', func_get_args() );
		$uri	= $this->path.$source;
		if( !$source || !file_exists( $uri ) ){
			$this->env->getMessenger()->noteNotice( 'Fehlerhafte Bildadresse. Weiterleitung zur Übersicht.' );
			$this->restart( './gallery' );
		}
		$exif	= new UI_Image_Exif( $uri );
		$info	= $this->readGalleryInfo( dirname( $source ) );
		$key	= pathinfo( $source, PATHINFO_FILENAME );
		$title	= isset( $info[$key] ) ? $info[$key] : NULL;

		$this->setData(
			array(
				'path'		=> $this->path,
				'source'	=> $source,
				'title'		=> $title,
				'files'		=> FolderLister::getFileList( dirname( $uri ), '/\.(jpg|jpeg|jpe|png|gif)$/i' ),
				'exif'		=> new Dictionary( $exif->getAll() ),
			)
		);
	}

	public function thumb()
	{
		$this->addData( 'path', $this->path );
		$this->addData( 'source', urldecode( $this->env->getRequest()->get( 'source' ) ) );
	}

	protected function __onInit()
	{
		$config		= $this->env->getConfig();
		$this->path	= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
	}

	protected function readGalleryInfo( $source )
	{
		$uri	= $this->path.$source.'/info.ini';
		if( !file_exists( $uri ) )
			return array();
		$reader	= new IniFileReader( $uri, TRUE );
		return $reader->toArray( TRUE );
	}
}
