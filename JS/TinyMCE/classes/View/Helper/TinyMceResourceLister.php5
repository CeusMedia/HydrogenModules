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
		return $this->listLinks;
	}
}
?>
