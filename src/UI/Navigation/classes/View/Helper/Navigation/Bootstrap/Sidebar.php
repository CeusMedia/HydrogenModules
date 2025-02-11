<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as Environment;

class View_Helper_Navigation_Bootstrap_Sidebar
{
	protected Environment $env;
	protected Model_Menu $menu;
	protected bool $inverse			= FALSE;
	protected array $linksToSkip	= [];
	protected string $scope			= 'main';
	protected ?string $logoTitle	= NULL;
	protected ?string $logoLink		= NULL;
	protected ?string $logoIcon		= NULL;
	protected ?string $style		= NULL;
	protected ?object $helperAccountMenu	= NULL;

	public function __construct( Environment $env, Model_Menu $menu = NULL )
	{
		$this->env		= $env;
		if( NULL !== $menu )
			$this->setMenuModel( $menu );
	}

	/**
	 *	@todo 		 remove after abstract interface and abstract of Hydrogen view helper are updated
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
				if( !$page->items )
					continue;
				$title		= $this->renderLabelWithIcon( $page );
				$list[]		= HtmlTag::create( 'li', $title, ['class' => 'bs4-nav-link nav-header'] );

				foreach( $page->items as $subpage ){
					$class		= 'bs4-nav-item nav-list-sub-item '.( $subpage->active ? 'active' : NULL );
					$href		= './'.$subpage->link;
//					$link		= HtmlTag::create( 'a', $subpage->label, ['href' => $href] );
					$link		= HtmlTag::create( 'a', $this->renderLabelWithIcon( $subpage ), ['href' => $href, 'class' => 'bs4-nav-link'] );
					$list[]		= HtmlTag::create( 'li', $link, ['class' => $class] );
				}
			}
			else{
				if( in_array( $page->path, $this->linksToSkip ) )
					continue;
				$class	= 'bs4-nav-item '.( $page->active ? 'active' : NULL );
				$href	= $page->path == 'index' ? './' : './'.$page->link;
//				$link	= HtmlTag::create( 'a', $page->label, ['href' => $href] );
				$link	= HtmlTag::create( 'a', self::renderLabelWithIcon( $page ), ['href' => $href, 'class' => 'bs4-nav-link'] );
				$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
			}
		}
		$logo	= $this->renderLogo();
		$this->env->getPage()->addBodyClass( 'nav-sidebar' );

		$account	= '';
		if( $this->helperAccountMenu ){
			$account	= $this->helperAccountMenu->render();
		}

		$list	= HtmlTag::create( 'ul', $list, ['class' => 'nav nav-list bs4-nav-pills bs4-flex-column'] );
		$list	= HtmlTag::create( 'div', $list, ['id' => 'nav-sidebar-list'] );
		$this->env->getPage()->js->addScriptOnReady('jQuery(".dropdown-toggle").dropdown();');
		return $logo.$account.$list;
	}

	public function renderLogo(): string
	{
		$logoTitle	= trim( $this->logoTitle ?? '' );
		$logoIcon	= trim( $this->logoIcon ?? '' );
		if( '' === $logoTitle && '' === $logoIcon )
			return '';
		$label	= $this->logoTitle;
		if( $this->logoIcon ){
			$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
			$icon	= HtmlTag::create( 'i', '', ['class' => $icon] );
			$label	= $icon.'&nbsp;'.$this->logoTitle;
		}
		if( !$this->logoLink )
			return HtmlTag::create( 'div', $label, [
//				'id'	=> 'logo',
				'class'	=> 'brand'
			] );
		$link	= HtmlTag::create( 'a', $label, [
			'href'	=> $this->logoLink,
//			'class'	=> 'brand'
		] );
		return HtmlTag::create( 'div', $link, ['class' => 'brand'] );
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
		if( !str_starts_with( trim( $entry->icon ), 'fa' ) )
			$class	= 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
		$icon   = HtmlTag::create( 'i', '', ['class' => $class] );
		if( strlen( $entry->label ) )
			return $icon.'&nbsp;'.$entry->label;
		return $icon;
	}
}
