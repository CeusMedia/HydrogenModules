<?php
class View_Helper_Navigation_BootstrapResponsive
{
	protected $env;
	protected $menu;
	protected $linksToSkip	= array();
	protected $scope		= 'main';
	protected $position		= 'static';
	protected $helperAccountMenu;
	protected $logoTitle;
	protected $logoLink;
	protected $logoIcon;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env		= $env;
		$this->menu		= new Model_Menu( $this->env );
	}

	public function render(): string
	{
		$config			= $this->env->getConfig()->getAll( 'module.ui_navigation.', TRUE );
		$useMobile		= $config->get( 'render.mobile' );
		$useDesktop		= $config->get( 'render.desktop' );

		$navbars	= array();

		if( $useDesktop ){
			$configDesktop		= $config->getAll( 'render.desktop.', TRUE );
			$helperNavDesktop	= new View_Helper_Navigation_Bootstrap_Dropdown( $this->env, $this->menu );
			$helperNavDesktop->setScope( $this->scope );
			$helperNavDesktop->setStyle( $configDesktop->get( 'style' ) );
			$helperNavDesktop->setInverse( $configDesktop->get( 'theme' ) === "dark" );
			$helperNavDesktop->setLinksToSkip( $this->linksToSkip );
			$helperNavDesktop->setLogo( $this->logoTitle, $this->logoLink, $this->logoIcon );

			if( $configDesktop->get( 'navbar' ) ){
				if( !$this->env->getModules()->has( 'UI_Navigation_Bootstrap_Navbar' ) )
					throw new RuntimeException( 'Missing module "UI_Navigation_Bootstrap_Navbar"' );
				$helperNavbarDesktop   = new View_Helper_Navigation_Bootstrap_Navbar();
				$helperNavbarDesktop->setEnv( $this->env );
				if( $this->helperAccountMenu )
					$helperNavbarDesktop->setAccountMenuHelper( $this->helperAccountMenu );
				$helperNavbarDesktop->setNavigationHelper( $helperNavDesktop );
				if( $this->position )
					$helperNavbarDesktop->setPosition( $this->position );
				$helperNavbarDesktop->setLinksToSkip( $this->linksToSkip );
				$helperNavbarDesktop->setInverse( $configDesktop->get( 'theme' ) === "dark" );
				$helperNavbarDesktop->setContainer( TRUE );
				$helperNavbarDesktop->hideOnMobileDevice( $useMobile );
				$helperNavDesktop	= $helperNavbarDesktop;
				//$helperNavbarDesktop->isCollapsable( TRUE );
			}
			$navbars[]	= $helperNavDesktop->render();
		}

		if( $useMobile ){
			$configMobile		= $config->getAll( 'render.mobile.', TRUE );

			$helperNavMobile	= new View_Helper_Navigation_Mobile( $this->env, $this->menu );
			$helperNavMobile->setScope( $this->scope );
			$helperNavMobile->setInverse( $configMobile->get( 'theme' ) === "dark" );
			$helperNavMobile->setLinksToSkip( $this->linksToSkip );

			if( $configMobile->get( 'navbar' ) ){
				if( !$this->env->getModules()->has( 'UI_Navigation_Bootstrap_Navbar' ) )
					throw new RuntimeException( 'Missing module "UI_Navigation_Bootstrap_Navbar"' );

				$helperNavMobileTitle	= new View_Helper_Navigation_Bootstrap_NavbarMobileTitle();
				$helperNavMobileTitle->setInverse( $configMobile->get( 'navbar.theme' ) === "dark" );
				$helperNavMobileTitle->setLogo( $this->logoTitle, $this->logoLink, $this->logoIcon );

				$helperNavbarMobile   = new View_Helper_Navigation_Bootstrap_NavbarMobile();
				$helperNavbarMobile->setEnv( $this->env );
				if( $this->helperAccountMenu )
					$helperNavbarMobile->setAccountMenuHelper( $this->helperAccountMenu );
				$helperNavbarMobile->setNavigationHelper( $helperNavMobileTitle );
				$helperNavbarMobile->setPosition( "fixed" );
				$helperNavbarMobile->setInverse( $configMobile->get( 'navbar.theme' ) === "dark" );
				$helperNavbarMobile->setContainer( TRUE );
				$helperNavbarMobile->hideOnDesktop( $useDesktop );
				$navbars[]	= $helperNavbarMobile->render();
				$navbars[]	= $helperNavMobile->render();
			}
			else{
				$navbars[]	= $helperNavMobile->render();
			}
		}
		return join( $navbars );
	}

	public function setAccountMenuHelper( $helper ): self
	{
		$this->helperAccountMenu	= $helper;
		return $this;
	}

	public function setLinksToSkip( array $links ): self
	{
		$this->linksToSkip	= $links;
		return $this;
	}

	public function setLogo( string $title, string $url = NULL, string $icon = NULL ): self
	{
		$this->logoTitle	= $title;
		$this->logoLink		= $url;
		$this->logoIcon		= $icon;
		return $this;
	}

	public function setPosition( string $position ): self
	{
		$this->position	= $position;
		return $this;
	}

	public function setScope( string $scope ): self
	{
		$this->scope	= $scope;
		return $this;
	}
}
