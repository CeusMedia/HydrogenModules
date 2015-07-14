<?php
class View_Helper_TinyMceResourceLister extends CMF_Hydrogen_View_Helper_Abstract{

	public $list		= array();
	public $listImages	= array();
	public $listLinks	= array();

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


	static public function ___onPageApplyModules( $env, $context, $module, $data = array() ){
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$pathLib	= $env->getConfig()->get( 'path.scripts.lib' );
		$language	= $env->getLanguage()->getLanguage();
		$version	= $config->get( 'version' );

		$context->js->addUrl( $pathLib.'tinymce/'.$version.'/tinymce.min.js' );
		$context->js->addUrl( $pathLib.'tinymce/'.$version.'/langs/'.$language.'.js' );
		$context->js->addUrl( $pathJs.'TinyMCE.Config.js' );

		$frontend	= Logic_Frontend::getInstance( $env );

/*
$languages	= array();
$pattern	= "/^([a-z]{2})(-([A-Z]{2}))?(;q=([0-9].?[0-9]*))?$/";
$items		= explode( ',', getEnv( 'HTTP_ACCEPT_LANGUAGE' ) );
foreach( $items as $item ){
	$matches	= array();
	preg_match( $pattern, $item, $matches );
	$languages[]	= (object) array(
		'language'	=> $matches[1],
		'dialect'	=> $matches[3],
		'quality'	=> isset( $matches[5] ) ? $matches[5] : 1,
    );
}
*/
		$labels	= array(
			'de'	=> 'Deutsch',
			'en'	=> 'Englisch',
		);


		$languages	= array();
		$matches	= array();
		foreach( explode( ',', getEnv( 'HTTP_ACCEPT_LANGUAGE' ) ) as $item ){
			preg_match( "/^([a-z]{2})(-([A-Z]{2}))?(;q=([0-9].?[0-9]*))?$/", $item, $matches );
			$label	= $labels[$matches[1]];
			if( !in_array( $label."=".$matches[1], $languages ) )
				$languages[]	= $label."=".$matches[1];
		}



		if( $config->get( 'auto' ) && $config->get( 'auto.selector' ) ){
			$helper	= new View_Helper_TinyMceResourceLister( $env );
			$script	= '
tinymce.Config.languages = "'.join( ',', $languages ).'";
tinymce.Config.envUri = "'.$env->url.'";
tinymce.Config.frontendUri = "'.$frontend->getUri().'";
tinymce.Config.language = "'.$language.'";
tinymce.Config.listImages = '.json_encode( $helper->getImageList() ).';
tinymce.Config.listLinks = '.json_encode( $helper->getLinkList() ).';
	if($("settings.Module_JS_TinyMCE.auto_selector").size()){
		if(settings.Module_JS_TinyMCE.auto_selector){
			var options = {};
			if(settings.Module_JS_TinyMCE.auto_tools)
				options.tools = settings.Module_JS_TinyMCE.auto_tools;
			tinymce.init(tinymce.Config.apply(options));
console.log(options);
		}
	}';
			$context->js->addScriptOnReady( $script );
		}
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
