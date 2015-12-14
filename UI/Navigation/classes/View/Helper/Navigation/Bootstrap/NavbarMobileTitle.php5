<?php
class View_Helper_Navigation_Bootstrap_NavbarMobileTitle extends View_Helper_Navigation_Bootstrap_Navbar{

	/**
	 *	@todo 		kriss: remove after abstract interface and abstract of Hydrogen view helper are updated
	 */
	public function __toString(){
		return $this->render();
	}

	public function renderLogo(){
		if( strlen( trim( $this->logoTitle ) ) || strlen( trim( $this->logoIcon ) ) ){
			$icon	= "";
			if( $this->logoIcon ){
				$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $icon ) );
			}
			$label	= $icon.$this->logoTitle;
			if( $this->logoLink )
				$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $this->logoLink ) );
			return UI_HTML_Tag::create( 'div', $label, array( 'class' => "brand" ) );
		}
		return '';
	}

	public function render(){
		$brand	= $this->renderLogo();
		$iconBars	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bars' ) );
		$link		= UI_HTML_Tag::create( 'a', $iconBars, array( 'href' => '#menu' ) );
		$trigger	= UI_HTML_Tag::create( 'div', $link, array( 'id' => "mmenu-trigger-left", 'class' => "mmenu-trigger" ) );
		return $trigger.$brand;
	}
}
?>
