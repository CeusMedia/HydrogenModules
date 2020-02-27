<?php
class Controller_Info_Gallery extends CMF_Hydrogen_Controller{

	protected $moduleConfig;
	protected $baseFilePath;

	public function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_galleries.', TRUE );
		$config			= $this->env->getConfig();
		$pathImages		= $config->get( 'path.images' ) ? $config->get( 'path.images' ) : 'images/';
		$pathGalleries	= $this->moduleConfig->get( 'folder' );
		$this->addData( 'baseUriPath', $this->path );
		$this->addData( 'baseFilePath', $this->baseFilePath = $pathImages.$pathGalleries );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'indexMode', $this->moduleConfig->get( 'index.mode' ) );
	}

	public function index( $galleryId = NULL ){
		$this->env->getSession()->set( 'gallery_referer', new ADT_URL( getEnv( 'HTTP_REFERER' ) ) );
		if( $galleryId )
			$this->restart( 'view/'.$galleryId, TRUE );
	}

	public function view( $galleryId = NULL ){
		$modelGallery	= new Model_Gallery( $this->env );

		if( !$galleryId )
			$this->restart( NULL, TRUE );
		if( !( $gallery = $modelGallery->get( $galleryId ) ) ){
			$this->env->getMessenger()->noteError( 'Invalid gallery ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'gallery', $gallery );

		$indices		= array( 'status' => 1 );
		$configSort		= $this->moduleConfig->getAll( 'index.order.', TRUE );
		$order			= array( $configSort->get( 'by' ) => $configSort->get( 'direction' ) );
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
}
?>