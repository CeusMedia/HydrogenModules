<?php
class Controller_Gallery extends CMF_Hydrogen_Controller{
	
	protected $path;
	
	public function __onInit(){
		$config		= $this->env->getConfig();
		$this->path	= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
	}
	
	public function download( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL ){
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
	public function feed( $limit = 10, $debug = NULL ){
		$limit		= ( (int) $limit > 0 ) ? (int) $limit : 10;

		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
		$index		= Folder_RecursiveLister::getFolderList( $path, '/^[0-9]{4}-[0-9]{2}-[0-9]{2} /' );
		foreach( $index as $folder ){
			$timestamp	= filemtime( $folder->getPathname() );
			$list[$timestamp.'_'. uniqid()]	= (object) array(
				'label'		=> $folder->getFilename(),
				'pathname'	=> substr( $folder->getPathname(), strlen( $path ) ),
				'timestamp'	=> $timestamp,
			);
		}
		ksort( $list );
		$galleries	= array_reverse( array_slice( $list, -$limit ) );
		$this->addData( 'galleries', $galleries );
		$this->addData( 'path', $path );
		$this->addData( 'debug', (bool) $debug );
	}

	public function image(){
		$this->addData( 'path', $this->path );
		$this->addData( 'source', $this->env->getRequest()->get( 'source' ) );
	}

	public function index(){
		$source	= urldecode( implode( '/', $this->env->getRequest()->get( 'arguments' ) ) );
		$source	= stripslashes( $source );
		$info	= $this->readGalleryInfo( $source );
		$path	= $this->path.$source;
		$this->setData(
			array(
				'path'			=> $this->path,
				'source'		=> $source ? $source.'/' : '',
				'info'			=> $info,
				'folders'		=> Folder_Lister::getFolderList( $path ),
				'files'			=> Folder_Lister::getFileList( $path, '/\.(jpg|jpeg|jpe|png|gif)$/i' ),
				'textBottom'	=> '',
			)
		);
	}
	
	public function info( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL ){
		$args	= func_get_args();
		$source	= $arg1 ? join( '/', $args ) : $this->env->getRequest()->get( 'source' );
		if( !$source )
			throw new InvalidArgumentException( 'No file name given.' );
		$uri	= $this->path.$source;
		$exif	= new UI_Image_Exif( $uri );
		$info	= $this->readGalleryInfo( dirname( $source ) );
		$key	= pathinfo( $source, PATHINFO_FILENAME );
		$title	= isset( $info[$key] ) ? $info[$key] : NULL;
		
		$this->addData( 'path', $this->path );
		$this->addData( 'source', $source );
		$this->addData( 'title', $title );
		$this->addData( 'exif', new ADT_List_Dictionary( $exif->getAll() ) );
	}

	protected function readGalleryInfo( $source ){
		$uri	= $this->path.$source.'/info.ini';
		if( !file_exists( $uri ) )
			return array();
		$reader	= new File_INI_Reader( $uri, TRUE );
		return $reader->toArray( TRUE );
	}

	public function thumb(){
		$this->addData( 'path', $this->path );
		$this->addData( 'source', urldecode( $this->env->getRequest()->get( 'source' ) ) );
	}
}
?>
