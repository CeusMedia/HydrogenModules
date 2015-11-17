<?php
class View_Helper_Navigation_Bootstrap_Navbar extends CMF_Hydrogen_View_Helper_Abstract{

	protected $container			= FALSE;
	protected $inverse				= FALSE;
	protected $logoTitle;
	protected $logoLink;
	protected $logoIcon;
	protected $position				= "static";
	protected $helperAccountMenu;
	protected $helperNavigation;
	protected $linksToSkip			= array();

	public function render(){
		$this->env->getPage()->addBodyClass( "navbar-".$this->position );

		if( $this->helperNavigation ){
			$this->helperNavigation->setInverse( $this->inverse );
			$this->helperNavigation->setLinksToSkip( $this->linksToSkip );
			$links	= $this->helperNavigation->render();

			if( $this->container )
				$links	= UI_HTML_Tag::create( 'div', $links, array( 'class' => 'container' ) );

			$inner	= UI_HTML_Tag::create( 'div', $links, array( 'class' => 'navbar-inner' ) );
			$class	= "navbar navbar-".$this->position."-top";
			if( $this->inverse )
				$class	.= ' navbar-inverse';
			$links	= UI_HTML_Tag::create( 'div', $inner, array( 'class' => $class ) );
		}
		else{
			$helperNavbar	= new CMF_Hydrogen_View_Helper_Navigation_SingleAutoTabs( $this->env );
			$helperNavbar->classContainer	= "navbar navbar-".$this->position."-top";
			$helperNavbar->classWidget		= "navbar-inner";
			$helperNavbar->classHelper		= "nav";
			$helperNavbar->classTab			= "";
			$helperNavbar->classTabActive	= "active";
			$helperNavbar->setContainer( $this->container );
			foreach( $this->linksToSkip as $path )
				$helperNavbar->skipLink( $path );
			if( $this->inverse )
				$helperNavbar->classContainer	.= " navbar-inverse";
			$links			= $helperNavbar->render();
		}

		$inverse		= $this->inverse ? 'inverse' : '';

		$accountMenu	= "";
		if( $this->helperAccountMenu )
			$accountMenu	= $this->helperAccountMenu->render( $inverse );
		$content		= $this->renderLogo().$links.$accountMenu;
		if( $this->inverse )
			$content	= UI_HTML_Tag::create( 'div', $content, array( 'class' => 'inverse' ) );
		return $content;
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

	public function setNavigationHelper( $helper ){
		$this->helperNavigation	= $helper;
	}

	public function setContainer( $boolean = NULL ){
		$this->container	= (boolean) $boolean;
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
