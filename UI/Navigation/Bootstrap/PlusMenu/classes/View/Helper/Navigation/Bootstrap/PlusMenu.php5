<?php
use CeusMedia\Bootstrap\Dropdown\Menu as DropdownMenu;
use CeusMedia\Bootstrap\Dropdown\Trigger as DropdownTrigger;
use CeusMedia\Bootstrap\Button\Group as Dropdown;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Navigation_Bootstrap_PlusMenu extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $class		= 'plus-menu';
	protected $buttonClass	= '';
	protected $buttonIcon	= 'fa fa-fw fa-plus';
	protected $links		= [];
	protected $alignRight	= FALSE;
	protected $alignBottom	= FALSE;

	protected $moduleKey	= 'ui_navigation_bootstrap_plusmenu';
	protected $moduleConfig;

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
		$this->links[]	= (object) array(
			'url'		=> $url,
			'label'		=> $label,
			'icon'		=> $icon,
		);
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

		$buttonClasses	= array( 'btn', 'dropdown-toggle' );
		if( $this->buttonClass )
			$buttonClasses[]	= $this->buttonClass;
		$iconPlus	= UI_HTML_Tag::create( 'i', '', array( 'class' => $this->buttonIcon ) );
		$button		= UI_HTML_Tag::create( 'button', $iconPlus, array(
			'class'			=> join( ' ', $buttonClasses ),
			'data-toggle'	=> 'dropdown',
		) );
		$group		= (new Dropdown())->add( $button )->add( $dropdown );
		if( $this->alignBottom )
			$group->addClass( 'dropup' );
		$container	= UI_HTML_Tag::create( 'div', $group, array( 'class' => $this->class ) );
		return (string) $container;
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
