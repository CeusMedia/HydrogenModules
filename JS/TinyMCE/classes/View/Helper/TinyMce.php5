<?php
class View_Helper_TinyMce extends CMF_Hydrogen_View_Helper_Abstract{

	public $list		= array();
	public $listImages	= array();
	public $listLinks	= array();

	static protected $loaded	= FALSE;

	/**	@var	ADT_List_Dictionary		$config		Module configuration */
	protected $config;

	/**	@var 	string					$pathFront	Path to frontend application */
	protected $pathFront;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Web	$env
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Web $env ){
		$this->setEnv( $env );
		$this->config		= $this->env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
		$this->pathFront	= $this->config->get( 'path' );
		$this->cache		= $this->env->getCache();
	}

	/**
	 *	@todo		extract to future View_Helper_TinyMCE
	 */
	static public function ___onPageApplyModules( $env, $context, $module, $data = array() ){
		self::load( $env );
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );

		if( $config->get( 'auto' ) && $config->get( 'auto.selector' ) ){
			$language	= self::getLanguage( $env );

			$baseUrl	= $env->url;
			if( $env->getModules()->has( 'Resource_Frontend' ) )
				$baseUrl	= Logic_Frontend::getInstance( $env )->getUri();

			/* @todo extract to language file after rethinking this solution */
			$labels	= array(
				'de'	=> 'Deutsch',
				'en'	=> 'Englisch',
			);

			/* @todo	WHY? please implement self::getLanguages similar to self::getLanguage */
			$languages	= array();
			$matches	= array();
			foreach( explode( ',', getEnv( 'HTTP_ACCEPT_LANGUAGE' ) ) as $item ){
				preg_match( "/^([a-z]{2})(-([A-Z]{2}))?(;q=([0-9].?[0-9]*))?$/", $item, $matches );
				if( isset( $matches[1] ) && isset( $labels[$matches[1]] ) ){
					$label	= $labels[$matches[1]];
					if( !in_array( $label."=".$matches[1], $languages ) )
						$languages[]	= $label."=".$matches[1];
				}
			}

			$styleFormats	= array(
				array(
					'title'		=> 'Blöcke',
					'items'		=> array(
						array(
							'title'				=> 'Absatz',
							'block'				=> 'p',
						),
						array(
							'title'				=> 'Textblock',
							'block'				=> 'div',
						),
						array(
							'title'				=> 'Zitatblock',
							'block'				=> 'blockquote',
							'wrapper'			=> TRUE,
						),
						array(
							'title'				=> 'vorformatierter Text',
							'block'				=> 'pre',
						),
						array(
							'title'				=> 'Abbildung',
							'block'				=> 'figure',
							'wrapper'			=> TRUE,
						),
						array(
							'title'				=> 'HTML5: Sektion',
							'block'				=> 'section',
							'wrapper'			=> TRUE,
							'merge_siblings'	=> FALSE,
						),
						array(
							'title'				=> 'HTML5: Artikel',
							'block'				=> 'article',
							'wrapper'			=> TRUE,
							'merge_siblings'	=> FALSE,
						),
						array(
							'title'				=> 'HTML5: Marginale',
							'block'				=> 'aside',
							'wrapper'			=> TRUE,
						),
					)
				),
				array(
					'title'		=> 'Bildformatierung',
					'items'		=> array(
						array(
							'title'		=> 'Ausrichtung',
							'items'		=> array(
								array(
									'title'		=> 'links',
									'selector'	=> 'img',
									'styles'	=> array( 'float' => 'left', 'margin' => '0 20px 10px 0px'),
								),
								array(
									'title'		=> 'rechts',
									'selector'	=> 'img',
									'styles'	=> array( 'float' => 'right', 'margin' => '0 0 10px 20px'),
								),
							)
						),
						array(
							'title'		=> 'Dekoration',
							'items'		=> array(
								array(
									'title'		=> 'abgerundet',
									'selector'	=> 'img',
									'classes'	=> 'img-rounded',
								),
								array(
									'title'		=> 'kreisrund',
									'selector'	=> 'img',
									'classes'	=> 'img-circle',
								),
								array(
									'title'		=> 'Polaroid',
									'selector'	=> 'img',
									'classes'	=> 'img-polaroid',
								),
							)
						),
						array(
							'title'				=> 'In Lightbox öffnen',
							'selector'			=> 'a',
							'classes'			=> 'fancybox-auto',
						),
					)
				)
			);

			$options	= array(
				'languages'		=> $languages,
				'envUri'		=> $env->url,
				'frontendUri'	=> $baseUrl,
				'frontendTheme'	=> $env->getConfig()->get( 'layout.theme' ),
				'language'		=> $language,
				'styleFormats'	=> $styleFormats,
			);
			if(0){
				$helper	= new View_Helper_TinyMce( $env );
				$options['listImages']	= json_encode( $helper->getImageList() );
				$options['listLinks']	= json_encode( $helper->getLinkList() );
			}
			$context->js->addScript( 'ModuleJsTinyMce.configAuto('.json_encode( $options ).')' );
			$context->js->addScriptOnReady( 'ModuleJsTinyMce.applyAuto()' );
		}
	}

	static public function load( $env ){
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

	static public function getLanguage( $env ){
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
			$this->listImages	= array();
			$this->cache->remove( $cacheKey );
		}
		if( !( $this->listImages = $this->cache->get( $cacheKey ) ) ){
			$this->list	= array();
			if( ( $modules = $this->env->getModules() ) )											//  get module handler resource if existing
				$modules->callHook( 'TinyMCE', 'getImageList', $this, array( 'hidePrefix' => FALSE ) );								//  call related module event hooks
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
			$this->listLinks	= array();
			$this->cache->remove( $cacheKey );
		}
		if( !( $this->listLinks = $this->cache->get( $cacheKey ) ) ){
			$this->list	= array();
			if( ( $modules = $this->env->getModules() ) )											//  get module handler resource if existing
				$modules->callHook( 'TinyMCE', 'getLinkList', $this );								//  call related module event hooks
			$this->listLinks	= $this->list;
			usort( $this->listLinks, array( $this, "__compare" ) );
			$this->cache->set( $cacheKey, $this->listLinks );
		}
		return $this->listLinks;
	}

	static public function tidyHtml( $html, $options = array() ){
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
