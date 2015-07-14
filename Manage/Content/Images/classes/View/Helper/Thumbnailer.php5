<?php
class View_Helper_Thumbnailer{

	public function __construct( CMF_Hydrogen_Environment_Abstract $env, $maxWidth = 120, $maxHeight = 80, $cachePath = NULL, $cacheFile = "thumbs.sqlite" ){
		$this->env			= $env;
		$this->config		= $this->env->getconfig();
		$this->cache		= new Resource_SqliteCache( $cachePath.$cacheFile );
//		$this->cache		= new Resource_SqliteCache( ".thumbs.db" );
		$this->maxWidth		= $maxWidth;
		$this->maxHeight	= $maxHeight;

//$this->env->getMessenger()->noteNotice( print_m( $this->cache->index(), NULL, NULL, TRUE ) );

	}

	public function get( $imagePath, $maxWidth = NULL, $maxHeight = NULL ){
		$maxWidth	= is_null( $maxWidth ) ? $this->maxWidth : (int) $maxWidth;
		$maxHeight	= is_null( $maxHeight ) ? $this->maxHeight : (int) $maxHeight;
		$prefix		= 'image('.$maxWidth.':'.$maxHeight.'):';
		if( ( $thumb = $this->cache->get( $prefix.$imagePath ) ) )
			return $thumb;
		if( !file_exists( $imagePath ) )
			throw new RuntimeException( 'Image "'.$imagePath.'" is not existing' );
		$mime		= image_type_to_mime_type( exif_imagetype( $imagePath ) );
		$tmpName	= tempnam( sys_get_temp_dir(), 'img_' );
		$scaler		= new UI_Image_ThumbnailCreator( $imagePath, $tmpName );
		$scaler->thumbizeByLimit( $maxWidth, $maxHeight );
		$data		= 'data:'.$mime.';base64,'.base64_encode( file_get_contents( $tmpName ) );
		unlink( $tmpName );
		$this->cache->set( $prefix.$imagePath, $data );
		return $data;
	}

	public function optimize( $pathImages ){
		$ids	= $this->cache->index();
		foreach( Folder_RecursiveLister::getFileList( $pathImages ) as $entry ){
			foreach( $ids as $nr => $id ){
				if( strpos( $id, $entry->getPathname() ) !== FALSE )
					unset( $ids[$nr] );
			}

		}
		foreach( $ids as $id )
			$this->cache->remove( $id );
	}

	public function uncacheFile( $imagePath ){
		$this->cache->remove( 'image('.$this->maxWidth.':'.$this->maxHeight.'):'.$imagePath );
	}

	public function uncacheFolder( $folderPath ){
		$this->cache->flush( 'image('.$this->maxWidth.':'.$this->maxHeight.'):'.$folderPath );
	}

	public function flushCache(){
		$this->cache->flush();
	}
}
?>
