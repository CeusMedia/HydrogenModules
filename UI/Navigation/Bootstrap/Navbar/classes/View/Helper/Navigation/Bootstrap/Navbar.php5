<?php
class View_Helper_Navigation_Bootstrap_Navbar extends CMF_Hydrogen_View_Helper_Abstract{

	protected $inverse				= FALSE;
	protected $logoTitle;
	protected $logoLink;
	protected $logoIcon;
	protected $position				= "static";
	protected $helperAccountMenu;
	protected $linksToSkip			= array();

	public function render(){
		$this->env->getPage()->addBodyClass( "navbar-".$this->position );
		$helperNavbar	= new CMF_Hydrogen_View_Helper_Navigation_SingleAutoTabs( $this->env );
		$helperNavbar->classContainer	= "navbar navbar-".$this->position."-top";
		$helperNavbar->classWidget		= "navbar-inner";
		$helperNavbar->classHelper		= "nav";
		$helperNavbar->classTab			= "";
		$helperNavbar->classTabActive	= "active";
		foreach( $this->linksToSkip as $path )
			$helperNavbar->skipLink( $path );

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
			$icon	= "";
			if( $this->logoIcon ){
				$icon	= $this->inverse ? $this->logoIcon.' icon-white' : $this->logoIcon;
				$icon	= '<i class="'.$icon.'"></i> ';
			}
			$label	= $icon.$this->logoTitle;
			if( $this->logoLink )
				$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $this->logoLink ) );
			return '<div id="logo">'.$label.'</div>';
		}
		return '';
	}

	public function setAccountMenuHelper( View_Helper_Navigation_Bootstrap_AccountMenu $helper ){
		$this->helperAccountMenu	= $helper;
	}

	public function setInverse( $boolean = NULL ){
		$this->inverse	= (boolean) $boolean;
	}

	public function setLinksToSkip( $links ){
		$this->linksToSkip	= $links;
	}

	public function setLogo( $title, $url = NULL, $icon = NULL ){
		$this->logoTitle	= $title;
		$this->logoLink		= $url;
		$this->logoIcon		= $icon;
	}

	public function setPosition( $mode ){
		$this->position	= $mode;
	}
}
?>
