<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;

class View_Helper_Navigation
{
	protected Environment $env;
	protected Model_Menu $menu;
	protected Dictionary $moduleConfig;
	protected bool $inverse				= FALSE;
	protected array $linksToSkip		= [];
	protected ?string $logoTitle		= NULL;
	protected ?string $logoLink			= NULL;
	protected ?string $logoIcon			= NULL;
	protected $helperAccount;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->menu			= new Model_Menu( $env );
		$this->moduleConfig	= $env->getConfig()->getAll( "module.ui_navigation.", TRUE );
	}

	public function getMenu(): Model_Menu
	{
		return $this->menu;
	}

	public function render( string $scope = 'main', string $class = NULL, string $style = NULL ): string
	{
		$class		= $class ?: $this->moduleConfig->get( 'render.desktop.class' );
		$style		= $style ?: $this->moduleConfig->get( 'render.desktop.style' );
		$argments	= [$this->env, $this->menu];
		if( !class_exists( $class ) )
			throw new RuntimeException( 'Navigation class "'.$class.'" is not existing' );
		$helper	= ObjectFactory::createObject( $class, $argments );
		$helper->setInverse( $this->inverse );
		$helper->setLinksToSkip( $this->linksToSkip );
		if( $this->logoTitle )
			$helper->setLogo( $this->logoTitle, $this->logoLink, $this->logoIcon );
		$helper->setScope( $scope );
		$helper->setStyle( $style );
		if( $this->helperAccount )
			$helper->setAccountMenuHelper( $this->helperAccount );
		return $helper->render();
	}

	public function setAccountMenuHelper( $helperAccount ): self
	{
		$this->helperAccount	= $helperAccount;
		return $this;
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
}
