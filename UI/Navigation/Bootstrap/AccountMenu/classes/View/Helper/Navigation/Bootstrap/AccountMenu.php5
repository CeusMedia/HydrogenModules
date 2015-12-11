<?php
class View_Helper_Navigation_Bootstrap_AccountMenu{

	protected $env;
	protected $user;
	protected $useAvatar		= FALSE;
	protected $linksInside		= array();
	protected $linksOutside		= array();
	protected $imageSize		= 32;
	public $guestLabel			= "Guest";
	public $guestEmail			= "<em>(not logged in)</em>";

	public function __construct( $env ){
		$this->env	= $env;
	}

	/**
	 *	@deprecated 		use menu instead
	 *	@todo 				remove
	 */
	public function addInsideLink( $path, $label, $icon = NULL ){
		$this->linksInside[]	= (object)array(
			'icon'		=> $icon,
			'label'		=> $label,
			'link'		=> $path,
		);
	}

	/**
	 *	@deprecated 		use menu instead
	 *	@todo 				remove
	 */
	public function addInsideLinkLine(){
		$this->linksInside[]	= 'line';
	}

	/**
	 *	@deprecated 		use menu instead
	 *	@todo 				remove
	 */
	public function addOutsideLink( $path, $label, $icon = NULL ){
		$this->linksOutside[]	= (object)array(
			'icon'		=> $icon,
			'label'		=> $label,
			'link'		=> $path,
		);
	}

	/**
	 *	@deprecated 		use menu instead
	 *	@todo 				remove
	 */
	public function addOutsideLinkLine(){
		$this->linksOutside[]	= 'line';
	}

	protected function renderLabelWithIcon( $entry ){
		if( !isset( $entry->icon ) )
			return $entry->label;
		$class	= $entry->icon;
		if( !preg_match( "/^fa/", $entry->icon ) )
			$class	= 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
		$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $class ) );
		return $icon.'&nbsp;'.$entry->label;
    }

	public function render( $classMenu = "" ){
		$username	= $this->guestLabel;
		$fullname	= '';
		$email		= $this->guestEmail;
		if( $this->user ){
			$username	= $this->user->username;
			$fullname	= $this->user->firstname.' '.$this->user->surname;
			$email		= $this->user->email;
		}
		if( $this->menu ){																			//  @todo: remove
			$links	= $this->renderMenuLinks();
		}
		else{																						//  @todo: remove
			if( $this->user )																		//  @todo: remove
				$links		= $this->renderSetLinks( $this->linksInside );							//  @todo: remove
			else																					//  @todo: remove
				$links		= $this->renderSetLinks( $this->linksOutside );							//  @todo: remove
		}																							//  @todo: remove
		$avatar	= '';
		if( $this->user && $this->useAvatar ){
			if( class_exists( 'View_Helper_UserAvatar' ) ){
				$helper		= new View_Helper_UserAvatar( $this->env );
//				$helper->useGravatar( )																//  @todo: implement: switch in module + gravatar module detection
				$helper->setUser( $this->user );
				$helper->setSize( $this->imageSize );
				$avatar	= $helper->render();
			}
			else if( class_exists( 'View_Helper_Gravatar' ) ){
				$helper		= new View_Helper_Gravatar( $this->env );
				$helper->setUser( $this->user );
				$helper->setSize( $this->imageSize );
				$avatar	= $helper->render();
			}
			$avatar	= UI_HTML_Tag::create( 'div', $avatar, array( 'class' => 'avatar' ) );
		}

		$labels			= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', $username, array( 'class' => 'username' ) ),
			UI_HTML_Tag::create( 'div', $fullname, array( 'class' => 'fullname' ) ),
			UI_HTML_Tag::create( 'div', $email, array( 'class' => 'email' ) ),
		), array( 'class' => 'labels' ) );
		$trigger		= UI_HTML_Tag::create( 'div', array(
			$avatar,
			$labels,
			UI_HTML_Tag::create( 'div', '', array( 'class' => 'clearfix' ) ),
		), array(
			'id' 			=> 'drop-account',
			'role'			=> 'button',
			'class'			=> 'dropdown-toggle',
			'data-toggle'	=> 'dropdown',
		) );
		return UI_HTML_Tag::create( 'div', array( $trigger, $links ), array(
			'id' => 'account-menu',
			'class' => 'dropdown '.$classMenu
		) );
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

	public function setLinks( $menu, $scope ){
		$this->menu		= $menu;
		$this->scope	= $scope;
	}

	public function setUser( $userObjectOrId ){
		if( is_object( $userObjectOrId ) )
			$this->user	= $userObjectOrId;
		else if( is_int( $userObjectOrId ) ){
			$model	= new Model_User( $this->env );
			$this->user	= $model->get( $userObjectOrId );
		}
		else
			throw new InvalidArgumentException( "Given data is neither an user object nor an user ID" );
	}

	public function setImageSize( $size ){
		$this->imageSize	= $size;
	}

	public function useAvatar( $boolean = NULL ){
		$this->useAvatar	= (boolean) $boolean;
	}
}
?>
