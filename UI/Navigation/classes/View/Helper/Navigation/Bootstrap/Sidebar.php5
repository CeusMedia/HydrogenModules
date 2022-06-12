<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Navigation_Bootstrap_Sidebar
{
	protected $env;
	protected $menu;
	protected $inverse			= FALSE;
	protected $linksToSkip		= [];
	protected $logoTitle;
	protected $logoLink;
	protected $logoIcon;
	protected $scope			= 'main';
	protected $style;
	protected $helperAccountMenu;

	public function __construct( Environment $env, Model_Menu $menu = NULL )
	{
		$this->env		= $env;
		if( NULL !== $menu )
			$this->setMenuModel( $menu );
	}

	/**
	 *	@todo 		kriss: remove after abstract interface and abstract of Hydrogen view helper are updated
	 */
	public function __toString()
	{
		return $this->render();
	}

	public function render(): string
	{
		$list	= [];
		$pages	= $this->menu->getPages( $this->scope, FALSE );
		foreach( $pages as $page ){
			if( $page->type == 'menu' ){
				$sublist	= [];
				if( !$page->items )
					continue;
				$title		= $this->renderLabelWithIcon( $page );
				$list[]		= UI_HTML_Tag::create( 'li', $title, array( 'class' => 'bs4-nav-link nav-header'	) );

				foreach( $page->items as $subpage ){
					$class		= 'bs4-nav-item nav-list-sub-item '.( $subpage->active ? 'active' : NULL );
					$href		= './'.$subpage->link;
//					$link		= UI_HTML_Tag::create( 'a', $subpage->label, array( 'href' => $href ) );
					$link		= UI_HTML_Tag::create( 'a', $this->renderLabelWithIcon( $subpage ), array( 'href' => $href, 'class' => 'bs4-nav-link' ) );
					$list[]		= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
				}
			}
			else{
				if( in_array( $page->path, $this->linksToSkip ) )
					continue;
				$class	= 'bs4-nav-item '.( $page->active ? 'active' : NULL );
				$href	= $page->path == "index" ? './' : './'.$page->link;
//				$link	= UI_HTML_Tag::create( 'a', $page->label, array( 'href' => $href ) );
				$link	= UI_HTML_Tag::create( 'a', self::renderLabelWithIcon( $page ), array( 'href' => $href, 'class' => 'bs4-nav-link' ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
			}
		}
		$logo	= $this->renderLogo();
		$this->env->getPage()->addBodyClass( 'nav-sidebar' );

		$account	= '';
		if( $this->helperAccountMenu ){
			$account	= $this->helperAccountMenu->render();
		}

		$list	= UI_HTML_Tag::create( 'ul', $list, array( "class" => 'nav nav-list bs4-nav-pills bs4-flex-column' ) );
		$list	= UI_HTML_Tag::create( 'div', $list, array( 'id' => 'nav-sidebar-list' ) );
		$this->env->getPage()->js->addScriptOnReady('jQuery(".dropdown-toggle").dropdown();');
		return $logo.$account.$list;
	}

	public function renderLogo(): string
	{
		if( !( strlen( trim( $this->logoTitle ) ) || strlen( trim( $this->logoIcon ) ) ) )
			return '';
		$icon	= "";
		$label	= $this->logoTitle;
		if( $this->logoIcon ){
			$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $icon ) );
			$label	= $icon.'&nbsp;'.$this->logoTitle;
		}
		if( !$this->logoLink )
			return UI_HTML_Tag::create( 'div', $label, array(
//				'id'	=> "logo",
				'class'	=> 'brand'
			) );
		$link	= UI_HTML_Tag::create( 'a', $label, array(
			'href'	=> $this->logoLink,
//			'class'	=> 'brand'
		) );
		return UI_HTML_Tag::create( 'div', $link, array( 'class' => 'brand' ) );
	}

	public function setInverse( bool $boolean = NULL ): self
	{
		$this->inverse	= (boolean) $boolean;
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

	public function setMenuModel( Model_Menu $menu ): self
	{
		$this->menu		= $menu;
		return $this;
	}

	public function setScope( string $scope ): self
	{
		$this->scope	= $scope;
		return $this;
	}

	public function setStyle( string $style ): self
	{
		$this->style	= $style;
		return $this;
	}

	public function setAccountMenuHelper( $helper ): self
	{
		$this->helperAccountMenu	= $helper;
		return $this;
	}

	protected function renderLabelWithIcon( $entry ): string
	{
		if( empty( $entry->icon ) || !strlen( trim( $entry->icon ) )  )
			return $entry->label;
		$class	= $entry->icon;
		if( !preg_match( "/^fa/", trim( $entry->icon ) ) )
			$class	= 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
		$icon   = UI_HTML_Tag::create( 'i', '', array( 'class' => $class ) );
		if( strlen( $entry->label ) )
			return $icon.'&nbsp;'.$entry->label;
		return $icon;
	}
}
