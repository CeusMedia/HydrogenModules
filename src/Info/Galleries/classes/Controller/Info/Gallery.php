<?php

use CeusMedia\Common\ADT\URL as Url;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_Gallery extends Controller
{
	protected $moduleConfig;
	protected $baseFilePath;

	public function index( $galleryId = NULL )
	{
		$this->env->getSession()->set( 'gallery_referer', new Url( getEnv( 'HTTP_REFERER' ) ) );
		if( $galleryId )
			$this->restart( 'view/'.$galleryId, TRUE );
	}

	public function view( $galleryId = NULL )
	{
		$modelGallery	= new Model_Gallery( $this->env );

		if( !$galleryId )
			$this->restart( NULL, TRUE );
		if( !( $gallery = $modelGallery->get( $galleryId ) ) ){
			$this->env->getMessenger()->noteError( 'Invalid gallery ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'gallery', $gallery );

		$indices		= ['status' => 1];
		$configSort		= $this->moduleConfig->getAll( 'index.order.', TRUE );
		$order			= [$configSort->get( 'by' ) => $configSort->get( 'direction' )];
		$galleries		= $modelGallery->getAllByIndices( $indices, $order );

		$nextGallery	= NULL;
		$prevGallery	= NULL;
		$lastGallery	= NULL;
		foreach( $galleries as $nr => $gallery ){
			if( $gallery->galleryId == $galleryId ){
				$this->addData( 'prevGallery', $lastGallery );
				$prevGallery	= $lastGallery;
				if( isset( $galleries[$nr + 1] ) )
					$nextGallery	= $galleries[$nr + 1];
				break;
			}
			$lastGallery	= $gallery;
		}
		$this->addData( 'prevGallery', $lastGallery );
		$this->addData( 'nextGallery', $nextGallery );
		$this->addData( 'referer', $this->env->getSession()->get( 'gallery_referer' ) );
		$this->env->getSession()->remove( 'gallery_referer' );
	}

	protected function __onInit(): void
	{
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_galleries.', TRUE );
		$config			= $this->env->getConfig();
		$pathImages		= $config->get( 'path.images' ) ? $config->get( 'path.images' ) : 'images/';
		$pathGalleries	= $this->moduleConfig->get( 'folder' );
		$this->addData( 'baseUriPath', $this->path );
		$this->addData( 'baseFilePath', $this->baseFilePath = $pathImages.$pathGalleries );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'indexMode', $this->moduleConfig->get( 'index.mode' ) );
	}
}
