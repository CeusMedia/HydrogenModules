<?php
class View_Helper_Navigation{

	protected $env;
	protected $menu;
	protected $moduleConfig;
	protected $inverse			= FALSE;
	protected $linksToSkip		= array();
	protected $logoTitle;
	protected $logoLink;
	protected $logoIcon;

	public function __construct( $env ){
		$this->env			= $env;
		$this->menu			= new Model_Menu( $env );
		$this->moduleConfig	= $env->getConfig()->getAll( "module.ui_navigation.", TRUE );
	}

	public function getMenu(){
		return $this->menu;
	}

	public function render( $scope = 'main', $class = NULL, $style = NULL ){
		$class		= $class ? $class : $this->moduleConfig->get( 'nav.class' );
		$style		= $style ? $style : $this->moduleConfig->get( 'nav.style' );
		$argments	= array( $this->env, $this->menu );
		if( !class_exists( $class ) )
			throw new RuntimeException( 'Navigation class "'.$class.'" is not existing' );
		$helper	= Alg_Object_Factory::createObject( $class, $argments );
		$helper->setInverse( $this->inverse );
		$helper->setLinksToSkip( $this->linksToSkip );
		$helper->setLogo( $this->logoTitle, $this->logoLink, $this->logoIcon );
		return $helper->render( $scope, $style );
	}

	public function setInverse( $boolean = NULL ){
		$this->inverse	= (boolean) $boolean;
	}

	public function setLinksToSkip( $links ){
		$this->linksToSkip	= $links;
	}
	public function setLogo( $title, $url = NULL, $icon = NULL ){
		$this->logoTitle	= $title;
		$this->logoLink		= $url;
		$this->logoIcon		= $icon;
	}
}
?>
