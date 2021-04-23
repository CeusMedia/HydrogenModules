<?php
class View_Helper_Navigation_Bootstrap_Dropdown
{
	protected $env;
	protected $menu;
	protected $inverse			= FALSE;
	protected $linksToSkip		= array();
	protected $logoTitle;
	protected $logoLink;
	protected $logoIcon;
	protected $scope			= 'main';
	protected $style;

	public function __construct( CMF_Hydrogen_Environment $env, Model_Menu $menu = NULL )
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
		if( NULL === $this->menu )
			throw new RuntimeException( 'No menu model set' );
		$listClass	= 'nav';
		if( strtolower( $this->style ) == "pills" )
			$listClass	.= ' nav-pills';

		$list	= array();
		$pages	= $this->menu->getPages( $this->scope, FALSE );
		foreach( $pages as $page ){
			if( $page->type == 'menu' ){
				$sublist	= array();
				foreach( $page->items as $subpage ){
					$class		= $subpage->active ? 'active' : NULL;
					$href		= './'.$subpage->link;
//					$link		= UI_HTML_Tag::create( 'a', $subpage->label, array( 'href' => $href ) );
					$link		= UI_HTML_Tag::create( 'a', $this->renderLabelWithIcon( $subpage ), array( 'href' => $href ) );
					$sublist[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
				}
				if( !$sublist )
					continue;
				$class		= $page->active ? 'dropdown active' : 'dropdown';
				$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => 'dropdown-menu' ) );
				$title		= $this->renderLabelWithIcon( $page ).' <b class="caret"></b>';
				$link	= UI_HTML_Tag::create( 'a', $title, array(
					'href'			=> '#',
					'class' 		=> 'dropdown-toggle',
					'data-toggle'	=> 'dropdown'
				) );
				$list[]	= UI_HTML_Tag::create( 'li', $link.$sublist, array( 'class' => $class ) );
			}
			else{
				if( in_array( $page->path, $this->linksToSkip ) )
					continue;
				$class	= $page->active ? 'active' : NULL;
				$href	= $page->path == "index" ? './' : './'.$page->link;
//				$link	= UI_HTML_Tag::create( 'a', $page->label, array( 'href' => $href ) );
				$link	= UI_HTML_Tag::create( 'a', self::renderLabelWithIcon( $page ), array( 'href' => $href ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
			}
		}
		$logo	= $this->renderLogo();
		return $logo.UI_HTML_Tag::create( 'ul', $list, array( "class" => $listClass ) );
	}

	public function renderLogo(): string
	{
		if( !( strlen( trim( $this->logoTitle ) ) || strlen( trim( $this->logoIcon ) ) ) )
			return '';
		$icon	= "";
		if( $this->logoIcon ){
			$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $icon ) );
		}
		$label	= $icon.'&nbsp;'.$this->logoTitle;
		if( !$this->logoLink )
			return UI_HTML_Tag::create( 'div', $label, array(
//				'id'	=> "logo",
				'class'	=> 'brand'
			) );
		return UI_HTML_Tag::create( 'a', $label, array(
			'href'	=> $this->logoLink,
//			'id'	=> "logo",
			'class'	=> 'brand'
		) );
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
		if( !preg_match( "/^fa/", $entry->icon ) )
			$class	= 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
		$icon   = UI_HTML_Tag::create( 'i', '', array( 'class' => $class ) );
		if( strlen( $entry->label ) )
			return $icon.'&nbsp;'.$entry->label;
		return $icon;
	}
}
