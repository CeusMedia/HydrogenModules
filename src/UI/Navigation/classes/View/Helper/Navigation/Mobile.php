<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Navigation_Mobile
{
	protected Environment $env;
	protected ?Model_Menu $menu		= NULL;
	protected bool $inverse			= FALSE;
	protected array $linksToSkip	= [];
	protected string $scope			= 'main';

	public function __construct( Environment $env, ?Model_Menu $menu = NULL )
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
		foreach( $this->menu->getPages( $this->scope, FALSE ) as $page ){
			if( $page->type == 'menu' ){
				$sublist	= [];
				foreach( $page->items as $subpage ){
					$class		= $subpage->active ? 'Selected' : NULL;
					$href		= './'.$subpage->link;
					$link		= HtmlTag::create( 'a', $this->renderLabelWithIcon( $subpage ), ['href' => $href] );
					$sublist[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
				}
				$class		= $page->active ? 'Selected' : NULL;
				$sublist	= HtmlTag::create( 'ul', $sublist, ['class' => ''] );
				$link		= HtmlTag::create( 'span', $this->renderLabelWithIcon( $page )/*, ['href' => '#']*/ );
				$list[]	= HtmlTag::create( 'li', $link.$sublist, ['class' => $class] );
			}
			else{
				if( in_array( $page->path, $this->linksToSkip ) )
					continue;
				$class	= $page->active ? 'Selected' : NULL;
				$href	= $page->path == 'index' ? './' : './'.$page->link;
				$link	= HtmlTag::create( 'a', self::renderLabelWithIcon( $page ), ['href' => $href] );
				$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
			}
		}
		$list	= HtmlTag::create( 'ul', $list, ['class' => 'mm-listview'] );
		return HtmlTag::create( 'div', $list, ['id' => 'menu', 'class' => 'mm-hidden'] );
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

	public function setMenuModel( Model_Menu $menu ): self
	{
		$this->menu	= $menu;
		return $this;
	}

	public function setScope( string $scope ): self
	{
		$this->scope	= $scope;
		return $this;
	}

	protected function renderLabelWithIcon( $entry ): string
	{
		if( empty( $entry->icon ) || !strlen( trim( $entry->icon ) ) )
			return $entry->label;
		$class	= $entry->icon;
		if( !str_starts_with( trim( $entry->icon ), 'fa'))
			$class	= 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
		$icon   = HtmlTag::create( 'i', '', ['class' => $class] );
		return $icon.'&nbsp;'.$entry->label;
	}
}
