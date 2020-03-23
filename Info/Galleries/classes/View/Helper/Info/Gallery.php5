<?php
class View_Helper_Info_Gallery extends CMF_Hydrogen_View_Helper_Abstract{

	const SCOPE_GALLERY		= 0;
	const SCOPE_IMAGE		= 1;

	public function __construct( $env ){
		$this->env			= $env;
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.info_galleries.', TRUE );
		$pathImages			= $env->getConfig()->get( 'path.images' );
		$pathImages			= $pathImages ? $pathImages : 'images/';
		$this->baseFilePath	= $this->env->url.$pathImages.$this->moduleConfig->get( 'path' );
		$this->modelGallery		= new Model_Gallery( $this->env );
		$this->modelImage			= new Model_Gallery_Image( $this->env );
	}

	protected function getBasePath(){
		return Controller_Info_Gallery::$defaultPath;
	}

	protected function getGalleries(){
		$conditions		= array( 'status' => '> 0' );
		$configSort		= $this->moduleConfig->getAll( 'index.order.', TRUE );
		$order			= array( $configSort->get( 'by' ) => $configSort->get( 'direction' ) );
		$galleries		= $this->modelGallery->getAll( $conditions, $order );
		return $galleries;
	}

	protected function getGalleryImages( $galleryId ){
		$configSort		= $this->moduleConfig->getAll( 'gallery.order.', TRUE );
		$order			= array( $configSort->get( 'by' ) => $configSort->get( 'direction' ) );
		$images			= $this->modelImage->getAllByIndex( 'galleryId', $galleryId, $order );
		return $images;
	}

	static public function getGalleryUrl( $gallery, $basePath ){
		return sprintf(
			'./%s/%s-%s',
			$basePath,
			$gallery->galleryId,
			self::urlencodeTitle( $gallery->title )
		);
	}

	protected function getThumbnailLinkClass( $scope = 0 ){
		$lightbox	= $this->moduleConfig->get( 'index.lightbox' );
		if( $scope === self::SCOPE_IMAGE )
			$lightbox	= $this->moduleConfig->get( 'gallery.lightbox' );

		$classLink		= 'thumbnail';
		$modulesConfig	= $this->env->getConfig()->getAll( 'module.', TRUE );
		switch( strtolower( $lightbox ) ){
			case 'darkbox':
				$classLink	= 'darkbox';
				if( $this->env->getModules()->has( 'UI_JS_Darkbox' ) )
					$classLink	= $modulesConfig->get( 'ui_js_darkbox.auto.class' );
				break;
			case 'fancybox':
				$classLink	= 'fancybox';
				if( $this->env->getModules()->has( 'UI_JS_fancyBox' ) )
					$classLink	= $modulesConfig->get( 'ui_js_fancybox.auto.class' );
				break;
		}
		return $classLink;
	}

	static public function renderGalleryDescription( CMF_Hydrogen_Environment $env, $view, $gallery ){
		$content	= "";
		if( trim( $gallery->description ) ){
			$content	= trim( $gallery->description );
			$content	= View_Info_Gallery::renderContentStatic( $env, $view, $content );
			$content	= UI_HTML_Tag::create( 'p', $content );
		}
		return $content;
	}

	static public function urlencodeTitle( $label, $delimiter = "_" ){
		$label  = str_replace( array( 'ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß' ), array( 'ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss' ), $label );
		$label  = preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label  = preg_replace( "/ +/", $delimiter, $label );
		return $label;
	}
}
?>
