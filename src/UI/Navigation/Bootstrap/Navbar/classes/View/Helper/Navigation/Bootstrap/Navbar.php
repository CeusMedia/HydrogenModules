<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Navigation_Bootstrap_Navbar extends Abstraction
{
	protected bool $container				= FALSE;
	protected bool $inverse					= FALSE;
	protected ?string $logoTitle			= NULL;
	protected ?string $logoLink				= NULL;
	protected ?string $logoIcon				= NULL;
	protected string $position				= 'static';
	protected ?object $helperAccountMenu	= NULL;
	protected ?object $helperNavigation		= NULL;
	protected array $linksToSkip			= [];
	protected bool $hideOnMobileDevice		= FALSE;

	/**
	 *	@todo 		 remove after abstract interface and abstract of Hydrogen view helper are updated
	 */
	public function __toString(): string
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

		if( !$this->helperNavigation )
			throw new RuntimeException( 'No navigation helper set' );
		$this->helperNavigation->setInverse( $this->inverse );
		$this->helperNavigation->setLinksToSkip( $this->linksToSkip );
		$links	= $this->helperNavigation->render();

		if( $this->container )
			$links	= HtmlTag::create( 'div', $links, ['class' => 'container'] );

		$inner	= HtmlTag::create( 'div', $links, ['class' => 'navbar-inner'] );
		$class	= "navbar navbar-".$this->position."-top";
		if( $this->inverse )
			$class	.= ' navbar-inverse';
		$links		= HtmlTag::create( 'div', $inner, ['class' => $class] );

		$inverse	= $this->inverse ? 'inverse' : '';

		$accountMenu	= "";
		if( $this->helperAccountMenu )
			$accountMenu	= $this->helperAccountMenu->render( $inverse );
		$content		= $this->renderLogo().$links.$accountMenu;
		$classes		= [];
		if( $this->inverse )
			$classes[]	= 'inverse';
		if( $this->hideOnMobileDevice )
			$classes[]	= 'visible-desktop';
		if( $classes )
			$content	= HtmlTag::create( 'div', $content, array( 'class' => $classes) );
		return $content;
	}

	public function renderLogo(): string
	{
		if( 0 === strlen( trim( $this->logoTitle ?? '' ) ) && 0 === strlen( trim( $this->logoIcon ?? '' ) ) )
			return '';
		$icon	= '';
		if( $this->logoIcon ){
			$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
			$icon	= HtmlTag::create( 'i', '', ['class' => $icon] );
		}
		$label	= $icon.$this->logoTitle;
		if( $this->logoLink )
			$label	= HtmlTag::create( 'a', $label, ['href' => $this->logoLink] );
		return HtmlTag::create( 'div', $label, ['id' => "navbar-logo"] );
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
