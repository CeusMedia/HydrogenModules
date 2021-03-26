<?php
class View_Helper_Navigation_Bootstrap_Navbar extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $container			= FALSE;
	protected $inverse				= FALSE;
	protected $logoTitle;
	protected $logoLink;
	protected $logoIcon;
	protected $position				= "static";
	protected $helperAccountMenu;
	protected $helperNavigation;
	protected $linksToSkip			= array();
	protected $hideOnMobileDevice	= FALSE;

	/**
	 *	@todo 		kriss: remove after abstract interface and abstract of Hydrogen view helper are updated
	 */
	public function __toString()
	{
		return $this->render();
	}

	public function hideOnMobileDevice( bool $hide ): self
	{
		$this->hideOnMobileDevice	= $hide;
		return $this;
	}

	public function render(): string
	{
		$this->env->getPage()->addBodyClass( "navbar-".$this->position );

		if( $this->helperNavigation ){
			$this->helperNavigation->setInverse( $this->inverse );
			$this->helperNavigation->setLinksToSkip( $this->linksToSkip );
			$links	= $this->helperNavigation->render();

			if( $this->container )
				$links	= UI_HTML_Tag::create( 'div', $links, array( 'class' => 'container' ) );

			$inner	= UI_HTML_Tag::create( 'div', $links, array( 'class' => 'navbar-inner' ) );
			$class	= "navbar navbar-".$this->position."-top";
			if( $this->inverse )
				$class	.= ' navbar-inverse';
			$links	= UI_HTML_Tag::create( 'div', $inner, array( 'class' => $class ) );
		}
		else{
			$helperNavbar	= new CMF_Hydrogen_View_Helper_Navigation_SingleAutoTabs( $this->env );
			$helperNavbar->classContainer	= "navbar navbar-".$this->position."-top";
			$helperNavbar->classWidget		= "navbar-inner";
			$helperNavbar->classHelper		= "nav";
			$helperNavbar->classTab			= "";
			$helperNavbar->classTabActive	= "active";
			$helperNavbar->setContainer( $this->container );
			foreach( $this->linksToSkip as $path )
				$helperNavbar->skipLink( $path );
			if( $this->inverse )
				$helperNavbar->classContainer	.= " navbar-inverse";
			$links			= $helperNavbar->render();
		}

		$inverse		= $this->inverse ? 'inverse' : '';

		$accountMenu	= "";
		if( $this->helperAccountMenu )
			$accountMenu	= $this->helperAccountMenu->render( $inverse );
		$content		= $this->renderLogo().$links.$accountMenu;
		$classes		= array();
		if( $this->inverse )
			$classes[]	= 'inverse';
		if( $this->hideOnMobileDevice )
			$classes[]	= 'visible-desktop';
		if( $classes )
			$content	= UI_HTML_Tag::create( 'div', $content, array( 'class' => $classes) );
		return $content;
	}

	public function renderLogo(): string
	{
		if( strlen( trim( $this->logoTitle ) ) || strlen( trim( $this->logoIcon ) ) ){
			$icon	= "";
			if( $this->logoIcon ){
				$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $icon ) );
			}
			$label	= $icon.$this->logoTitle;
			if( $this->logoLink )
				$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $this->logoLink ) );
			return UI_HTML_Tag::create( 'div', $label, array( 'id' => "navbar-logo" ) );
		}
		return '';
	}

	public function setAccountMenuHelper( $helper ): self
	{
		$this->helperAccountMenu	= $helper;
		return $this;
	}

	public function setNavigationHelper( $helper ): self
	{
		$this->helperNavigation	= $helper;
		return $this;
	}

	public function setContainer( bool $boolean = NULL ): self
	{
		$this->container	= $boolean;
		return $this;
	}

	public function setInverse( bool $boolean = NULL ): self
	{
		$this->inverse	= $boolean;
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

	public function setPosition( string $mode ): self
	{
		$this->position	= $mode;
		return $this;
	}
}
