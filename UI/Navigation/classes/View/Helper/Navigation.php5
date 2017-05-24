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

	static public function ___setupSidebar( $env, $context, $module, $data = array() ){
		$moduleConfig	= $env->getConfig()->getAll( 'module.ui_navigation.', TRUE );
		$desktopRendererClass = $moduleConfig->get( 'render.desktop.class' );
		if( $desktopRendererClass === 'View_Helper_Navigation_Bootstrap_Sidebar' ){
			$pathJs	= $env->getConfig()->get( 'path.scripts' );
			$context->js->addUrl( $pathJs.'module.ui.navigation.sidebar.js' );
			$context->js->addScriptOnReady("ModuleUiNavigation.Sidebar.init();");
		}
	}

	public function getMenu(){
		return $this->menu;
	}

	public function render( $scope = 'main', $class = NULL, $style = NULL ){
		$class		= $class ? $class : $this->moduleConfig->get( 'render.desktop.class' );
		$style		= $style ? $style : $this->moduleConfig->get( 'render.desktop.style' );
		$argments	= array( $this->env, $this->menu );
		if( !class_exists( $class ) )
			throw new RuntimeException( 'Navigation class "'.$class.'" is not existing' );
		$helper	= Alg_Object_Factory::createObject( $class, $argments );
		$helper->setInverse( $this->inverse );
		$helper->setLinksToSkip( $this->linksToSkip );
		$helper->setLogo( $this->logoTitle, $this->logoLink, $this->logoIcon );
		$helper->setScope( $scope );
		$helper->setStyle( $style );
		return $helper->render();
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
