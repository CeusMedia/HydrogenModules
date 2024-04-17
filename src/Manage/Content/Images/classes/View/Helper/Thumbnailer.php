<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\Image\Processing as ImageProcessing;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;

class View_Helper_Thumbnailer
{
	protected Environment $env;
	protected Dictionary $config;
	protected Model_Image_Thumbnail $model;
	protected int $maxWidth;
	protected int $maxHeight;

	public function __construct( Environment $env, $maxWidth = 120, $maxHeight = 80 )
	{
		$this->env			= $env;
		$this->config		= $this->env->getConfig();
		$this->model		= new Model_Image_Thumbnail( $this->env );
		$this->maxWidth		= $maxWidth;
		$this->maxHeight	= $maxHeight;
		if( !( $env instanceof RemoteEnvironment ) ){
			if( $this->env->getRequest()->has( 'flushCache' ) ){
				$number	= $this->flushCache();
				$this->env->getMessenger()->noteNotice( 'Thumbnail cache cleared (%s entries).', $number );
			}
		}
	}

	public function get( $imagePath, ?int $maxWidth = NULL, ?int $maxHeight = NULL )
	{
		$extension	= pathinfo( $imagePath, PATHINFO_EXTENSION );
		if( strtolower( $extension ) === "svg" ){
			$mime		= 'image/svg+xml';
			$content	= file_get_contents( $imagePath );
			return 'data:'.$mime.';base64,'.base64_encode( $content );
		}
		$maxWidth	= $maxWidth ?? $this->maxWidth;
		$maxHeight	= $maxHeight ?? $this->maxHeight;
		$indices	= [
			'imageId'	=> $imagePath,
			'maxWidth'	=> $maxWidth,
			'maxHeight'	=> $maxHeight,
		];
		if( !file_exists( $imagePath ) ){
			$this->uncacheFile( $imagePath );
			throw new RuntimeException( 'Image "'.$imagePath.'" is not existing' );
		}
		if( ( $thumb = $this->model->getByIndices( $indices ) ) )
			return $thumb->data;
		$mime		= image_type_to_mime_type( exif_imagetype( $imagePath ) );
//		print_m( $mime );die;
		$tmpName	= tempnam( sys_get_temp_dir(), 'img_' );
		$image		= new Image( $imagePath );
		$processor	= new ImageProcessing( $image );
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

	public function optimize( $pathImages )
	{
		return;
		$ids	= $this->cache->index();
		foreach( RecursiveFolderLister::getFileList( $pathImages ) as $entry ){
			foreach( $ids as $nr => $id ){
				if( strpos( $id, (string) $entry->getPathname() ) !== FALSE )
					unset( $ids[$nr] );
			}

		}
		foreach( $ids as $id )
			$this->cache->delete( $id );
	}

	public function uncacheFolder( $folderPath ): int
	{
		return $this->model->removeByIndex( 'imageId', $folderPath.'%' );
	}

	public function uncacheFile( $imagePath ): int
	{
		$indices	= [
			'imageId'	=> $imagePath,
			'maxWidth'	=> $this->maxWidth,
			'maxHeight'	=> $this->maxHeight,
		];
		return $this->model->removeByIndices( $indices );
	}

	public function flushCache(): int
	{
//		return $this->model->truncate();
		return $this->model->removeByIndex( 'imageThumbnailId', '> 0' );
	}
}
