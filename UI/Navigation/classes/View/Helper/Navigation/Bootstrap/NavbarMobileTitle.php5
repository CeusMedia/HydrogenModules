<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Navigation_Bootstrap_NavbarMobileTitle extends View_Helper_Navigation_Bootstrap_Navbar
{
	/**
	 *	@todo 		kriss: remove after abstract interface and abstract of Hydrogen view helper are updated
	 */
	public function __toString()
	{
		return $this->render();
	}

	public function render(): string
	{
		$brand	= $this->renderLogo();
		$iconBars	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bars' ) );
		$link		= HtmlTag::create( 'a', $iconBars, array( 'href' => '#menu' ) );
		$trigger	= HtmlTag::create( 'div', $link, array( 'id' => "mmenu-trigger-left", 'class' => "mmenu-trigger" ) );
		return $trigger.$brand;
	}

	public function renderLogo(): string
	{
		if( strlen( trim( $this->logoTitle ) ) || strlen( trim( $this->logoIcon ) ) ){
			$icon	= "";
			if( $this->logoIcon ){
				$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
				$icon	= HtmlTag::create( 'i', '', array( 'class' => $icon ) );
			}
			$label	= $icon.$this->logoTitle;
			if( $this->logoLink )
				$label	= HtmlTag::create( 'a', $label, array( 'href' => $this->logoLink ) );
			return HtmlTag::create( 'div', $label, array( 'class' => "brand" ) );
		}
		return '';
	}
}
