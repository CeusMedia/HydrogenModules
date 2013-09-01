<?php
class View_Helper_Bootstrap_Navbar extends CMF_Hydrogen_View_Helper_Abstract{

	protected $inverse				= FALSE;
	protected $logoTitle;
	protected $logoIcon;
	protected $position				= "static";
	protected $helperAccountMenu;

	public function render(){
		$this->env->getPage()->addBodyClass( "navbar-".$this->position );
		$helperNavbar	= new CMF_Hydrogen_View_Helper_Navigation_SingleAutoTabs( $this->env );
		$helperNavbar->classContainer	= "navbar navbar-".$this->position."-top";
		$helperNavbar->classWidget		= "navbar-inner";
		$helperNavbar->classHelper		= "nav";
		$helperNavbar->classTab			= "";
		$helperNavbar->classTabActive	= "active";

		if( $this->inverse )
			$helperNavbar->classContainer	.= " navbar-inverse";

		$inverse		= $this->inverse ? 'inverse' : '';

		$accountMenu	= "";
		if( $this->helperAccountMenu )
			$accountMenu	= $this->helperAccountMenu->render( $inverse );

		return '<div class="'.$inverse.'">
	'.$this->renderLogo().'
	'.$helperNavbar->render().'
	'.$accountMenu.'
</div>';
	}

	public function renderLogo(){
		if( strlen( trim( $this->logoTitle ) ) || strlen( trim( $this->logoIcon ) ) ){
			$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
			return '<div id="logo"><i class="'.$icon.'"></i> '.$this->logoTitle.'</div>';
		}
		return '';
	}

	public function setAccountMenuHelper( View_Helper_Bootstrap_AccountMenu $helper ){
		$this->helperAccountMenu	= $helper;
	}

	public function setInverse( $boolean = NULL ){
		$this->inverse	= (boolean) $boolean;
	}

	public function setLogo( $title, $icon = NULL ){
		$this->logoTitle	= $title;
		$this->logoIcon		= $icon;
	}

	public function setPosition( $mode ){
		$this->position	= $mode;
	}
}
?>
