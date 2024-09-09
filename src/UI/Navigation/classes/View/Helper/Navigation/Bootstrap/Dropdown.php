<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Navigation_Bootstrap_Dropdown
{
	protected Environment $env;
	protected ?Model_Menu $menu		= NULL;
	protected bool $inverse			= FALSE;
	protected array $linksToSkip	= [];
	protected string $scope			= 'main';
	protected ?string $logoTitle	= NULL;
	protected ?string $logoLink		= NULL;
	protected ?string $logoIcon		= NULL;
	protected ?string $style		= NULL;

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
		if( NULL === $this->menu )
			throw new RuntimeException( 'No menu model set' );
		$listClass	= 'nav';
		if( 'pills' === strtolower( $this->style ?? '' ) )
			$listClass	.= ' nav-pills';

		$list	= [];
		$pages	= $this->menu->getPages( $this->scope, FALSE );
		foreach( $pages as $page ){
			if( $page->type == 'menu' ){
				$sublist	= [];
				$lastChapter	= '';
				foreach( $page->items as $subpage ){
					$class		= $subpage->active ? 'active' : NULL;
					$href		= './'.$subpage->link;
//					$link		= HtmlTag::create( 'a', $subpage->label, ['href' => $href] );
					$link		= HtmlTag::create( 'a', $this->renderLabelWithIcon( $subpage ), ['href' => $href] );
					if( strlen( trim( $subpage->chapter ) ) > 0 && $subpage->chapter !== $lastChapter ){
						if( count( $sublist ) > 0)
							$sublist[]	= HtmlTag::create( 'li', '', ['class' => 'divider'] );
						$lastChapter = $subpage->chapter;
					}
					$sublist[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
				}
				if( !$sublist )
					continue;
				$class		= $page->active ? 'dropdown active' : 'dropdown';
				$sublist	= HtmlTag::create( 'ul', $sublist, ['class' => 'dropdown-menu'] );
				$title		= $this->renderLabelWithIcon( $page ).' <b class="caret"></b>';
				$link	= HtmlTag::create( 'a', $title, [
					'href'			=> '#',
					'class' 		=> 'dropdown-toggle',
					'data-toggle'	=> 'dropdown'
				] );
				$list[]	= HtmlTag::create( 'li', $link.$sublist, ['class' => $class] );
			}
			else{
				if( in_array( $page->path, $this->linksToSkip ) )
					continue;
				$class	= $page->active ? 'active' : NULL;
				$href	= $page->path == 'index' ? './' : './'.$page->link;
//				$link	= HtmlTag::create( 'a', $page->label, ['href' => $href] );
				$link	= HtmlTag::create( 'a', self::renderLabelWithIcon( $page ), ['href' => $href] );
				$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
			}
		}
		$logo	= $this->renderLogo();
		return $logo.HtmlTag::create( 'ul', $list, ['class' => $listClass] );
	}

	public function renderLogo(): string
	{
		if( !( strlen( trim( $this->logoTitle ?? '' ) ) || strlen( trim( $this->logoIcon ?? '' ) ) ) )
			return '';
		$icon	= '';
		if( $this->logoIcon ){
			$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
			$icon	= HtmlTag::create( 'i', '', ['class' => $icon] );
		}
		$label	= $icon.'&nbsp;'.$this->logoTitle;
		if( !$this->logoLink )
			return HtmlTag::create( 'div', $label, [
//				'id'	=> 'logo',
				'class'	=> 'brand'
			] );
		return HtmlTag::create( 'a', $label, [
			'href'	=> $this->logoLink,
//			'id'	=> 'logo',
			'class'	=> 'brand'
		] );
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

	public function setLogo( ?string $title, ?string $url = NULL, ?string $icon = NULL ): self
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

	protected function renderLabelWithIcon( $entry ): string
	{
		if( empty( $entry->icon ) )
			return $entry->label;
		$class	= $entry->icon;
		if( !str_starts_with( $entry->icon, 'fa' ))
			$class	= 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
		$icon   = HtmlTag::create( 'i', '', ['class' => $class] );
		if( strlen( $entry->label ) )
			return $icon.'&nbsp;'.$entry->label;
		return $icon;
	}
}
