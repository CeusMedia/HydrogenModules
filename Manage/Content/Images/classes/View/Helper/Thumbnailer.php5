<?php
class View_Helper_Thumbnailer{

	public function __construct( CMF_Hydrogen_Environment_Abstract $env, $maxWidth = 120, $maxHeight = 80 ){
		$this->env			= $env;
		$this->config		= $this->env->getConfig();
		$this->model		= new Model_Image_Thumbnail( $this->env );
		$this->maxWidth		= $maxWidth;
		$this->maxHeight	= $maxHeight;
		if( $this->env->getRequest()->has( 'flushCache' ) ){
			$number	= $this->flushCache();
			$this->env->getMessenger()->noteNotice( 'Thumbnail cache cleared (%s entries).', $number );
		}
	}

	public function get( $imagePath, $maxWidth = NULL, $maxHeight = NULL ){
		$maxWidth	= is_null( $maxWidth ) ? $this->maxWidth : (int) $maxWidth;
		$maxHeight	= is_null( $maxHeight ) ? $this->maxHeight : (int) $maxHeight;
		$indices	= array(
			'imageId'	=> $imagePath,
			'maxWidth'	=> $maxWidth,
			'maxHeight'	=> $maxHeight,
		);
		if( !file_exists( $imagePath ) ){
			$this->uncacheFile( $imagePath, $maxWidth, $maxHeight );
			throw new RuntimeException( 'Image "'.$imagePath.'" is not existing' );
		}
		if( ( $thumb = $this->model->getByIndices( $indices ) ) )
			return $thumb->data;
		$mime		= image_type_to_mime_type( exif_imagetype( $imagePath ) );
		$tmpName	= tempnam( sys_get_temp_dir(), 'img_' );
		$image		= new UI_Image( $imagePath );
		$processor	= new UI_Image_Processing( $image );
		$processor->scaleDownToLimit( (int) $maxWidth, (int) $maxHeight );
		$image->save( $tmpName );
		$content	= 'data:'.$mime.';base64,'.base64_encode( file_get_contents( $tmpName ) );
		unlink( $tmpName );
		$this->model->add( array_merge( $indices, array(
			'realWidth'		=> $image->getWidth(),
			'realHeight'	=> $image->getHeight(),
			'data'			=> $content,
			'timestamp'		=> time(),
		) ) );
		return $content;
	}

	public function optimize( $pathImages ){
		return;
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

	public function uncacheFolder( $folderPath ){
		return (int) $this->model->removeByIndex( 'imageId', $folderPath.'%' );
	}

	public function uncacheFile( $imagePath ){
		$indices	= array(
			'imageId'	=> $imagePath,
			'maxWidth'	=> $this->maxWidth,
			'maxHeight'	=> $this->maxHeight,
		);
		return $this->model->removeByIndices( $indices );
	}

	public function flushCache(){
//		return $this->model->truncate();
		return $this->model->removeByIndex( 'imageThumbnailId', '>0' );
	}
}
?>
