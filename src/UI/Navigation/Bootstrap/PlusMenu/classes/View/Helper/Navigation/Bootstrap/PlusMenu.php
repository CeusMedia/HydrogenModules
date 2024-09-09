<?php

use CeusMedia\Bootstrap\Dropdown\Menu as DropdownMenu;
use CeusMedia\Bootstrap\Button\Group as Dropdown;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Navigation_Bootstrap_PlusMenu extends Abstraction
{
	protected string $class			= 'plus-menu';
	protected string $buttonClass	= '';
	protected string $buttonIcon	= 'fa fa-fw fa-plus';
	protected array $links			= [];
	protected bool $alignRight		= FALSE;
	protected bool $alignBottom		= FALSE;

	protected string $moduleKey	= 'ui_navigation_bootstrap_plusmenu';
	protected Dictionary $moduleConfig;

	protected $menu;
	protected $scope;

	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.'.$this->moduleKey.'.', TRUE );
		if( $this->moduleConfig->get( 'button.class' ) )
			$this->setButtonClass( $this->moduleConfig->get( 'button.class' ) );
		if( $this->moduleConfig->get( 'button.icon' ) )
			$this->setButtonIcon( $this->moduleConfig->get( 'button.icon' ) );
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function addLink( string $url, string $label, string $icon = NULL ): self
	{
		$this->links[]	= (object) [
			'url'		=> $url,
			'label'		=> $label,
			'icon'		=> $icon,
		];
		return $this;
	}

	public function render(): string
	{
		if( !$this->menu )
			return '';
		$pages	= $this->menu->getPages( $this->scope );
		if( 0 === count( $pages ) && !$this->links )
			return '';
		$dropdown	= new DropdownMenu();
		$dropdown->setAlign( !$this->alignRight );
		if( $this->menu )
			foreach( $pages as $page )
				$dropdown->add( $page->link, $page->label, NULL, $page->icon );
		else if( $this->links )
			foreach( $this->links as $link )
				$dropdown->add( $link->url, $link->label, NULL, $link->icon );

		$buttonClasses	= ['btn', 'dropdown-toggle'];
		if( $this->buttonClass )
			$buttonClasses[]	= $this->buttonClass;
		$iconPlus	= HtmlTag::create( 'i', '', ['class' => $this->buttonIcon] );
		$button		= HtmlTag::create( 'button', $iconPlus, array(
			'class'			=> join( ' ', $buttonClasses ),
			'data-toggle'	=> 'dropdown',
		) );
		$group		= (new Dropdown())->add( $button )->add( $dropdown );
		if( $this->alignBottom )
			$group->addClass( 'dropup' );
		return HtmlTag::create( 'div', $group, ['class' => $this->class] );
	}

	public function setAlignRight( bool $alignRight ): self
	{
		$this->alignRight	= $alignRight;
		return $this;
	}

	public function setAlignBottom( bool $alignBottom ): self
	{
		$this->alignBottom	= $alignBottom;
		return $this;
	}

	public function setButtonClass( string $buttonClass ): self
	{
		$this->buttonClass	= $buttonClass;
		return $this;
	}

	public function setButtonIcon( string $buttonIcon ): self
	{
		$this->buttonIcon	= $buttonIcon;
		return $this;
	}

	public function setMenuPages( $menu, $scope ): self
	{
		$this->menu		= $menu;
		$this->scope	= $scope;
		return $this;
	}
}
