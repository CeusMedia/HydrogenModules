<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Info_Gallery extends Abstraction
{
	const SCOPE_GALLERY		= 0;
	const SCOPE_IMAGE		= 1;

	protected Dictionary $moduleConfig;
	protected string $baseFilePath;
	protected Model_Gallery $modelGallery;
	protected Model_Gallery_Image $modelImage;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.info_galleries.', TRUE );
		$pathImages			= $env->getConfig()->get( 'path.images' );
		$pathImages			= $pathImages ?: 'images/';
		$this->baseFilePath	= $this->env->url.$pathImages.$this->moduleConfig->get( 'path' );
		$this->modelGallery	= new Model_Gallery( $this->env );
		$this->modelImage	= new Model_Gallery_Image( $this->env );
	}

	public static function getGalleryUrl( $gallery, string $basePath ): string
	{
		return sprintf(
			'./%s/%s-%s',
			$basePath,
			$gallery->galleryId,
			self::urlencodeTitle( $gallery->title )
		);
	}

	public static function renderGalleryDescription( Environment $env, $view, $gallery ): string
	{
		$content	= "";
		if( trim( $gallery->description ) ){
			$content	= trim( $gallery->description );
			$content	= View_Info_Gallery::renderContentStatic( $env, $view, $content );
			$content	= HtmlTag::create( 'p', $content );
		}
		return $content;
	}

	public static function urlencodeTitle( string $label, string $delimiter = "_" ): string
	{
		$label  = str_replace( ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'], $label );
		$label  = preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label  = preg_replace( "/ +/", $delimiter, $label );
		return $label;
	}

	protected function getBasePath(): string
	{
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_galleries.', TRUE );
		$config			= $this->env->getConfig();
		$pathImages		= $config->get( 'path.images' ) ? $config->get( 'path.images' ) : 'images/';
		$pathGalleries	= $this->moduleConfig->get( 'folder' );
		return $pathImages.$pathGalleries;
	}

	protected function getGalleries(): array
	{
		$conditions		= ['status' => '> 0'];
		$configSort		= $this->moduleConfig->getAll( 'index.order.', TRUE );
		$order			= [$configSort->get( 'by' ) => $configSort->get( 'direction' )];
		$galleries		= $this->modelGallery->getAll( $conditions, $order );
		return $galleries;
	}

	protected function getGalleryImages( int|string $galleryId ): array
	{
		$configSort		= $this->moduleConfig->getAll( 'gallery.order.', TRUE );
		$order			= [$configSort->get( 'by' ) => $configSort->get( 'direction' )];
		$images			= $this->modelImage->getAllByIndex( 'galleryId', $galleryId, $order );
		return $images;
	}

	protected function getThumbnailLinkClass( $scope = 0 ): string
	{
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
}
