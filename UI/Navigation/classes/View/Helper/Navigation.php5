<?php
class View_Helper_Navigation
{
	protected $env;
	protected $menu;
	protected $moduleConfig;
	protected $inverse			= FALSE;
	protected $linksToSkip		= array();
	protected $logoTitle;
	protected $logoLink;
	protected $logoIcon;
	protected $helperAccount;

	public function __construct( $env )
	{
		$this->env			= $env;
		$this->menu			= new Model_Menu( $env );
		$this->moduleConfig	= $env->getConfig()->getAll( "module.ui_navigation.", TRUE );
	}

	public function getMenu(): Model_Menu
	{
		return $this->menu;
	}

	public function render( $scope = 'main', $class = NULL, $style = NULL ): string
	{
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
		if( $this->helperAccount )
			$helper->setAccountMenuHelper( $this->helperAccount );
		return $helper->render();
	}

	public function setAccountMenuHelper( $helperAccount ): self
	{
		$this->helperAccount	= $helperAccount;
		return $this;
	}

	public function setInverse( $boolean = NULL ): self
	{
		$this->inverse	= (boolean) $boolean;
		return $this;
	}

	public function setLinksToSkip( $links ): self
	{
		$this->linksToSkip	= $links;
		return $this;
	}
	public function setLogo( $title, $url = NULL, $icon = NULL ): self
	{
		$this->logoTitle	= $title;
		$this->logoLink		= $url;
		$this->logoIcon		= $icon;
		return $this;
	}
}
?>
