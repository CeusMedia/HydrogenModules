<?php
class Controller_Gallery extends CMF_Hydrogen_Controller{
	
	protected $path;
	
	public function __onInit(){
		$config		= $this->env->getConfig();
		$this->path	= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
	}

	public function download( $source = NULL ){
		$args	= func_get_args();
		$source	= $source ? join( '/', $args ) : $this->env->getRequest()->get( 'source' );
		if( !$source )
			throw new InvalidArgumentException( 'No file name given.' );
		$uri	= $this->path.$source;
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'File is not existing.' );
		Net_HTTP_Download::sendFile( $uri );
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

	public function info(){
		$this->addData( 'path', $this->path );
		$this->addData( 'source', $this->env->getRequest()->get( 'source' ) );
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
