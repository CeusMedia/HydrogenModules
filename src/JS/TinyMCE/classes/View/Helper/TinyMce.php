<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class View_Helper_TinyMce extends Abstraction
{
	public array $list				= [];
	public array $listImages		= [];
	public array $listLinks			= [];

	protected static bool $loaded	= FALSE;

	/**	@var	Dictionary		$config			Module configuration */
	protected Dictionary $config;

//	/**	@var 	string			$pathFront		Path to frontend application */
//	protected string $pathFront;

	protected SimpleCacheInterface $cache;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment	$env
	 *	@return		void
	 */
	public function __construct( WebEnvironment $env ){
		$this->setEnv( $env );
		$this->config		= $this->env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
//		$this->pathFront	= $this->config->get( 'path' );
		$this->cache		= $this->env->getCache();
	}

	/**
	 *	@param		WebEnvironment		$env
	 *	@return		void
	 */
	public static function load( WebEnvironment $env ): void
	{
		if( self::$loaded )
			return;

		$page		= $env->getPage();
		$language	= self::getLanguage( $env );
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
		$pathLocal	= $env->getConfig()->get( 'path.scripts' );

		$sourceUri	= $pathLocal.'tinymce/';
		if( $config->get( 'CDN' ) )
			$sourceUri	= rtrim( $config->get( 'CDN.URI' ), '/' ).'/';

		$page->js->addUrl( $sourceUri.'tinymce.min.js' );
		$page->js->addUrl( $pathLocal.'module.js.tinymce.js' );
		$page->js->addUrl( $pathLocal.'TinyMCE.Config.js' );
		$page->js->addUrl( $pathLocal.'TinyMCE.FileBrowser.js' );

		if( !$config->get( 'CDN' ) && 'en' !== $language )
			$page->js->addUrl( $sourceUri.'langs/'.$language.'.js' );

		self::$loaded	= TRUE;
	}

	/**
	 *	@param		WebEnvironment		$env
	 *	@return		string
	 */
	public static function getLanguage( WebEnvironment $env ): string
	{
		$language	= $env->getLanguage()->getLanguage();
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
		$languages	= explode( ",", $config->get( 'languages' ) );
		if( $config->get( 'CDN' ) )
			$languages	= explode( ",", $config->get( 'CDN.languages' ) );
		if( 'en' !== $language && !in_array( $language, $languages ) )
			$language = 'en';
		return $language;
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		array		List of images
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getImageList( bool $refresh = FALSE ): array
	{
		$cacheKey	= 'tinymce.images';
		if( $refresh ){
			$this->listImages	= [];
			$this->cache->delete( $cacheKey );
		}
		if( $this->cache->has( $cacheKey ) )
			$this->listImages	= $this->cache->get( $cacheKey );
		else{
			$this->list	= [];
			$modules	= $this->env->getModules();
			$payload	= ['hidePrefix' => FALSE];
			$modules->callHookWithPayload( 'TinyMCE', 'getImageList', $this, $payload );	//  call related module event hooks
			$this->listImages	= $this->list;
			usort( $this->listImages, [$this, "__compare"] );
			$this->cache->set( $cacheKey, $this->listImages );
		}
		return $this->listImages;
	}

	/**
	 *	@access		public
	 *	@return		array		List of links
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getLinkList( bool $refresh = FALSE ): array
	{
		$cacheKey	= 'tinymce.links';
		if( $refresh || 1 ){
			$this->listLinks	= [];
			$this->cache->delete( $cacheKey );
		}
		if( $this->cache->has( $cacheKey ) )
			$this->listLinks	= $this->cache->get( $cacheKey );
		else{
			$this->list	= [];
			if( ( $modules = $this->env->getModules() ) )											//  get module handler resource if existing
				$modules->callHook( 'TinyMCE', 'getLinkList', $this );								//  call related module event hooks
			$this->listLinks	= $this->list;
			usort( $this->listLinks, [$this, "__compare"] );
			$this->cache->set( $cacheKey, $this->listLinks );
		}
		return $this->listLinks;
	}

	/**
	 *	@param		string		$html
	 *	@param		array		$options
	 *	@return		string
	 */
	public static function tidyHtml( string $html, array $options = [] ): string
	{
		if( function_exists( 'tidy_repair_string' ) ){
			$html	= tidy_repair_string( $html, array_merge( [
				'clean'				=> FALSE,
				'doctype'			=> 'omit',
				'show-body-only'	=> TRUE,
				'output-xhtml'		=> TRUE,
				'indent'			=> TRUE,
				'indent-spaces'		=> 4,
				'wrap'				=> 0,
			], $options ) );
			$html	= str_replace( "    ", "\t", $html );
		}
		return $html;
	}

	/**
	 *	@param		object		$a
	 *	@param		object		$b
	 *	@return		int
	 */
	protected function __compare( object $a, object $b ): int
	{
		return strcmp( strtolower( $a->title ), strtolower( $b->title ) );
	}
}
