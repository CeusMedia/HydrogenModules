<?php
use CeusMedia\Bootstrap\Dropdown\Menu as DropdownMenu;
use CeusMedia\Bootstrap\Dropdown\Trigger as DropdownTrigger;
use CeusMedia\Bootstrap\Button\Group as Dropdown;

class View_Helper_Navigation_Bootstrap_PlusMenu extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $class		= 'plus-menu';
	protected $buttonClass	= '';
	protected $buttonIcon	= 'fa fa-fw fa-plus';
	protected $links		= array();
	protected $alignRight	= FALSE;
	protected $alignBottom	= FALSE;

	protected $moduleKey	= 'ui_navigation_bootstrap_plusmenu';
	protected $moduleConfig;

	public function __construct( CMF_Hydrogen_Environment $env ){
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

	public function addLink( $url, $label, $icon = NULL ): self
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
		if( !$this->menu && !$this->links )
			return '';
		$dropdown	= new DropdownMenu();
		$dropdown->setAlign( !$this->alignRight );
		if( $this->menu )
			foreach( $this->menu->getPages( $this->scope ) as $page )
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
		$group		= Dropdown::create()->add( $button )->add( $dropdown );
		if( $this->alignBottom )
			$group->addClass( 'dropup' );
		$container	= UI_HTML_Tag::create( 'div', $group, array( 'class' => $this->class ) );
		return (string) $container;
	}

	public function setAlignRight( $alignRight ): self
	{
		$this->alignRight	= $alignRight;
		return $this;
	}

	public function setAlignBottom( $alignBottom ): self
	{
		$this->alignBottom	= $alignBottom;
		return $this;
	}

	public function setButtonClass( $buttonClass ): self
	{
		$this->buttonClass	= $buttonClass;
		return $this;
	}

	public function setButtonIcon( $buttonIcon ): self
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
