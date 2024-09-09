<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Navigation_Bootstrap_NavbarMobile extends View_Helper_Navigation_Bootstrap_Navbar
{
	protected bool $hideOnDesktop	= FALSE;

	/**
	 *	@todo 		 remove after abstract interface and abstract of Hydrogen view helper are updated
	 */
	public function __toString(): string
	{
		return $this->render();
	}

	public function hideOnDesktop( bool $hide ): self
	{
		$this->hideOnDesktop	= $hide;
		return $this;
	}

	public function render(): string
	{
		$this->hideOnMobileDevice( FALSE );
		$classes	= ['layout-navbar-mobile navbar-fixed-top mm-fixed-top'];
		if( $this->hideOnDesktop )
			$classes[]	= 'hidden-desktop';
		return  HtmlTag::create( 'div', parent::render(), array(
			'class'	=> join( ' ', $classes ),
		) );
	}
}
