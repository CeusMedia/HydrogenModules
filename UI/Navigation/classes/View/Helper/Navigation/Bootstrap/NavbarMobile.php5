<?php
class View_Helper_Navigation_Bootstrap_NavbarMobile extends View_Helper_Navigation_Bootstrap_Navbar{

	protected $hideOnDesktop	= FALSE;

	/**
	 *	@todo 		kriss: remove after abstract interface and abstract of Hydrogen view helper are updated
	 */
	public function __toString(){
		return $this->render();
	}

	public function hideOnDesktop( $hide ){
		$this->hideOnDesktop	= $hide;
	}

	public function render(){
		$this->hideOnMobileDevice( FALSE );
		$classes	= array( 'layout-navbar-mobile navbar-fixed-top mm-fixed-top' );
		if( $this->hideOnDesktop )
			$classes[]	= 'hidden-desktop';
		return  UI_HTML_Tag::create( 'div', parent::render(), array(
			'class'	=> join( ' ', $classes ),
		) );
	}
}
