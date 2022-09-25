<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_TinyMce extends CMF_Hydrogen_View_Helper_Abstract{

	public $list		= [];
	public $listImages	= [];
	public $listLinks	= [];

	static protected $loaded	= FALSE;

	/**	@var	Dictionary		$config		Module configuration */
	protected $config;

	/**	@var 	string			$pathFront	Path to frontend application */
	protected $pathFront;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment	$env
	 *	@return		void
	 */
	public function __construct( WebEnvironment $env ){
		$this->setEnv( $env );
		$this->config		= $this->env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
		$this->pathFront	= $this->config->get( 'path' );
		$this->cache		= $this->env->getCache();
	}

	static public function load( Environment $env ){
		if( self::$loaded )
			return;

		$page		= $env->getPage();
		$language	= $env->getLanguage()->getLanguage();
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
		$pathLocal	= $env->getConfig()->get( 'path.scripts' );

		$sourceUri	= $pathLocal.'tinymce/';
		if( $config->get( 'CDN' ) )
			$sourceUri	= rtrim( $config->get( 'CDN.URI' ), '/' ).'/';

		$page->js->addUrl( $sourceUri.'tinymce.min.js' );
		$page->js->addUrl( $pathLocal.'module.js.tinymce.js' );
		$page->js->addUrl( $pathLocal.'TinyMCE.Config.js' );
		$page->js->addUrl( $pathLocal.'TinyMCE.FileBrowser.js' );

		$languages	= self::getLanguage( $env );
		if( !$config->get( 'CDN' ) && $language !== "en" )
			$page->js->addUrl( $sourceUri.'langs/'.$language.'.js' );

		self::$loaded	= TRUE;
	}

	static public function getLanguage( Environment $env ){
		$language	= $env->getLanguage()->getLanguage();
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
		$languages	= explode( ",", $config->get( 'languages' ) );
		if( $config->get( 'CDN' ) )
			$languages	= explode( ",", $config->get( 'CDN.languages' ) );
		if( $language !== "en" && !in_array( $language, $languages ) )
			$language = "en";
		return $language;
	}

	protected function __compare( $a, $b ){
		return strcmp( strtolower( $a->title ), strtolower( $b->title ) );
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		array		List of images
	 */
	public function getImageList( $refresh = FALSE ){
		$cacheKey	= 'tinymce.images';
		if( $refresh ){
			$this->listImages	= [];
			$this->cache->remove( $cacheKey );
		}
		if( !( $this->listImages = $this->cache->get( $cacheKey ) ) ){
			$this->list	= [];
			if( ( $modules = $this->env->getModules() ) ){	 										//  get module handler resource if existing
				$payload	= array( 'hidePrefix' => FALSE );
				$modules->callHook( 'TinyMCE', 'getImageList', $this, $payload );								//  call related module event hooks
			}
			$this->listImages	= $this->list;
			usort( $this->listImages, array( $this, "__compare" ) );
			$this->cache->set( $cacheKey, $this->listImages );
		}
		return $this->listImages;
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		array		List of links
	 */
	public function getLinkList( $refresh = FALSE ){
		$cacheKey	= 'tinymce.links';
		if( $refresh || 1 ){
			$this->listLinks	= [];
			$this->cache->remove( $cacheKey );
		}
		if( !( $this->listLinks = $this->cache->get( $cacheKey ) ) ){
			$this->list	= [];
			if( ( $modules = $this->env->getModules() ) )											//  get module handler resource if existing
				$modules->callHook( 'TinyMCE', 'getLinkList', $this );								//  call related module event hooks
			$this->listLinks	= $this->list;
			usort( $this->listLinks, array( $this, "__compare" ) );
			$this->cache->set( $cacheKey, $this->listLinks );
		}
		return $this->listLinks;
	}

	static public function tidyHtml( $html, $options = [] ){
		if( function_exists( 'tidy_repair_string' ) ){
			$html	= tidy_repair_string( $html, array_merge( array(
				'clean'				=> FALSE,
				'doctype'			=> 'omit',
				'show-body-only'	=> TRUE,
				'output-xhtml'		=> TRUE,
				'indent'			=> TRUE,
				'indent-spaces'		=> 4,
				'wrap'				=> 0,
			), $options ) );
			$html	= str_replace( "    ", "\t", $html );
		}
		return $html;
	}
}
?>
