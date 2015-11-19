<?php
class View_Helper_Navigation_Bootstrap_AccountMenu{

	protected $gravatar;
	protected $user;
	protected $useGravatar		= FALSE;
	protected $linksInside		= array();
	protected $linksOutside		= array();
	public $guestLabel			= "Guest";
	public $guestEmail			= "<em>(not logged in)</em>";

	public function addInsideLink( $path, $label, $icon = NULL ){
		$this->linksInside[]	= (object)array(
			'icon'		=> $icon,
			'label'		=> $label,
			'link'		=> $path,
		);
	}

	public function addInsideLinkLine(){
		$this->linksInside[]	= 'line';
	}

	public function addOutsideLink( $path, $label, $icon = NULL ){
		$this->linksOutside[]	= (object)array(
			'icon'		=> $icon,
			'label'		=> $label,
			'link'		=> $path,
		);
	}

	public function addOutsideLinkLine(){
		$this->linksOutside[]	= 'line';
	}

	public function setLinks( $menu, $scope ){
		$this->menu		= $menu;
		$this->scope	= $scope;
	}

    protected function renderLabelWithIcon( $entry ){
        if( !isset( $entry->icon ) )
            return $entry->label;
        $class  = $entry->icon;
        if( !preg_match( "/^fa/", $entry->icon ) )
            $class  = 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
        $icon   = UI_HTML_Tag::create( 'i', '', array( 'class' => $class ) );
        return $icon.'&nbsp;'.$entry->label;
    }

	public function render( $classMenu = "" ){
		if( $this->user ){
			$username	= $this->user->username;
			$email		= $this->user->email;
		}
		else{
			$username	= $this->guestLabel;
			$email		= $this->guestEmail;
		}
		if( $this->menu ){
			$links	= $this->renderMenuLinks();
		}
		else{
			if( $this->user )
				$links		= $this->renderSetLinks( $this->linksInside );
			else
				$links		= $this->renderSetLinks( $this->linksOutside );
		}
		if( $this->useGravatar ){
			if( !empty( $this->gravatar ) )
				$gravatar	= 'http://www.gravatar.com/avatar/'.$this->gravatar.'?s=32&d=mm&r=g';
			else if( !empty( $this->email ) )
				$gravatar	= 'http://www.gravatar.com/avatar/'.md5( strtolower( trim( $this->email ) ) ).'?s=32&d=mm&r=g';
			else
				$gravatar	= 'http://www.gravatar.com/avatar/?s=32&d=mm&r=g';
		}

		return '
<div id="account-menu" class="dropdown '.$classMenu.'">
	<div id="drop-account" role="button" class="dropdown-toggle" data-toggle="dropdown">
		<div class="avatar">
			<img src="'.$gravatar.'"/>
		</div>
		<div class="labels">
			<div class="username">'.$username.'</div>
			<div class="email">'.$email.'</div>
		</div>
	</div>
	'.$links.'
</div>';
	}

	protected function renderMenuLinks(){
		$list	= array();
		$pages	= $this->menu->getPages( $this->scope );
		foreach( $pages as $page ){
			$class	= $page->active ? 'active' : NULL;
//			$href	= $page->path == "index" ? './' : './'.$page->link;
			$link	= UI_HTML_Tag::create( 'a', self::renderLabelWithIcon( $page ), array(
				'role'		=> "menuitem",
				'tabindex'	=> "-1",
				'href'		=> $page->link,
			) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array(
			'class'				=> "dropdown-menu pull-right",
			'role'				=> "menu",
			'aria-labelledby'	=> "drop-account",
		) );
	}

	protected function renderSetLinks( $links ){
		foreach( $links as $link ){
			if( is_object( $link ) ){
				$icon	= "";
				if( $link->icon )
					$icon	= UI_HTML_Tag::create( 'i', "", array( 'class' => $link->icon ) ).'&nbsp;';
				$attributes	= array(
					'role'		=> "menuitem",
					'tabindex'	=> "-1",
					'href'		=> $link->link,
				);
				$link	= UI_HTML_Tag::create( 'a', $icon.$link->label, $attributes );
				$list[]	= UI_HTML_Tag::create( 'li', $link , array( 'role' => 'presentation' ) );
			}
			else{
				$attributes	= array(
					'role'	=> "presentation",
					'class'	=> "divider"
				);
				$list[]	= UI_HTML_Tag::create( 'li', "", $attributes );
			}
		}
		$attributes	= array(
			'class'				=> "dropdown-menu pull-right",
			'role'				=> "menu",
			'aria-labelledby'	=> "drop-account",
		);
		$links	= UI_HTML_Tag::create( 'ul', $list, $attributes );
		return $links;
	}

	public function setUser( $user, $gravatarHash = NULL ){
		$this->user			= $user;
		$this->gravatar		= md5( strtolower( trim( $user->email ) ) );
		if( $gravatarHash )
			$this->gravatar		= $gravatarHash;
	}

	public function useGravatar( $boolean = NULL ){
		$this->useGravatar	= (boolean)$boolean;
	}
}
?>
