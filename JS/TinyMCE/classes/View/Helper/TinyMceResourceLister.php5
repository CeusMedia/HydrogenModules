<?php
class View_Helper_TinyMceResourceLister extends CMF_Hydrogen_View_Helper_Abstract{

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
	}

	/**
	 *	@todo		extract to future View_Helper_TinyMCE
	 */
	static public function load( $env ){
		if( self::$loaded )
			return;

		$page		= $env->getPage();
		$language	= $env->getLanguage()->getLanguage();
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
		$pathLocal	= $env->getConfig()->get( 'path.scripts' );

		$sourceUri	= $pathLocal.'tinymce/';
		if( $config->get( 'cdn' ) )
			$sourceUri	= rtrim( $config->get( 'cdn' ), '/' ).'/';

		$page->js->addUrl( $sourceUri.'tinymce.min.js' );
		if( $language !== "en" )
			$page->js->addUrl( $sourceUri.'langs/'.$language.'.js' );
		$page->js->addUrl( $pathLocal.'TinyMCE.Config.js' );
		self::$loaded	= TRUE
	}

	/**
	 *	@todo		extract to future View_Helper_TinyMCE
	 */
	static public function ___onPageApplyModules( $env, $context, $module, $data = array() ){
		self::load( $env );
		$language	= $env->getLanguage()->getLanguage();
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );

		$baseUrl	= $env->url;
		if( $env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $env )->getUri();

		/* @todo extract to language file after rethinking this solution */
		$labels	= array(
			'de'	=> 'Deutsch',
			'en'	=> 'Englisch',
		);

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

		if( $config->get( 'auto' ) && $config->get( 'auto.selector' ) ){
			$helper	= new View_Helper_TinyMceResourceLister( $env );
			$script	= '
tinymce.Config.languages = "'.join( ',', $languages ).'";
tinymce.Config.envUri = "'.$env->url.'";
tinymce.Config.frontendUri = "'.$baseUrl.'";
tinymce.Config.language = "'.$language.'";
tinymce.Config.listImages = '.json_encode( $helper->getImageList() ).';
tinymce.Config.listLinks = '.json_encode( $helper->getLinkList() ).';
	if($(settings.JS_TinyMCE.auto_selector).size()){
		if(settings.JS_TinyMCE.auto_selector){
			var options = {};
			if(settings.JS_TinyMCE.auto_tools)
				options.tools = settings.JS_TinyMCE.auto_tools;
			tinymce.init(tinymce.Config.apply(options));
		}
	}';
			$context->js->addScriptOnReady( $script );
		}
	}

	protected function __compare( $a, $b ){
		return strcmp( strtolower( $a->title ), strtolower( $b->title ) );
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		array		List of images
	 */
	public function getImageList(){
		if( !$this->listImages ){
			$this->list	= array();
			if( ( $modules = $this->env->getModules() ) )											//  get module handler resource if existing
				$modules->callHook( 'TinyMCE', 'getImageList', $this, array( 'hidePrefix' => FALSE ) );								//  call related module event hooks
			$this->listImages	= $this->list;
		}
		usort( $this->listImages, array( $this, "__compare" ) );
		return $this->listImages;
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		array		List of links
	 */
	public function getLinkList(){
		if( !$this->listLinks ){
			$this->list	= array();
			if( ( $modules = $this->env->getModules() ) )											//  get module handler resource if existing
				$modules->callHook( 'TinyMCE', 'getLinkList', $this );								//  call related module event hooks
			$this->listLinks	= $this->list;
		}
		$list	= array();
		foreach( $this->listLinks as $key => $value )
			$list[$value->title.'_'.$key]	= $value;
		ksort( $list );
		return $this->listLinks = $list;
	}
}
?>
