<?php
class View_Helper_Navigation_Bootstrap_AccountMenu{

	protected $env;
	protected $user;
	protected $showAvatar		= TRUE;
	protected $showEmail		= FALSE;
	protected $showFullname		= TRUE;
	protected $showUsername		= TRUE;
	protected $linksInside		= array();
	protected $linksOutside		= array();
	protected $imageSize		= 32;
	public $guestLabel			= "Guest";
	public $guestEmail			= "<em>(not logged in)</em>";
	protected $menu;
	protected $scope;
	protected $moduleConfig;

	public function __construct( $env ){
		$this->env	= $env;
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.ui_navigation_bootstrap_accountmenu.', TRUE );
		$this->showAvatar		= $this->moduleConfig->get( 'show.avatar' );
		$this->showEmail		= $this->moduleConfig->get( 'show.email' );
		$this->showFullname		= $this->moduleConfig->get( 'show.fullname' );
		$this->showUsername		= $this->moduleConfig->get( 'show.username' );
	}

	/**
	 *	@deprecated 	use menu instead by calling setLinks
	 *	@todo   		to be removed in version 0.8
	 */
	public function addInsideLink( $path, $label, $icon = NULL ){
		$this->linksInside[]	= (object)array(
			'icon'		=> $icon,
			'label'		=> $label,
			'link'		=> $path,
		);
	}

	/**
	 *	@deprecated 	use menu instead by calling setLinks
	 *	@todo   		to be removed in version 0.8
	 */
	public function addInsideLinkLine(){
		$this->linksInside[]	= 'line';
	}

	/**
	 *	@deprecated 	use menu instead by calling setLinks
	 *	@todo   		to be removed in version 0.8
	 */
	public function addOutsideLink( $path, $label, $icon = NULL ){
		$this->linksOutside[]	= (object)array(
			'icon'		=> $icon,
			'label'		=> $label,
			'link'		=> $path,
		);
	}

	/**
	 *	@deprecated 	use menu instead by calling setLinks
	 *	@todo   		to be removed in version 0.8
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
		$config		= $this->env->getConfig();
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
			if( !$links )
				return;
		}
		else{																						//  @todo: remove
			if( $this->user )																		//  @todo: remove
				$links		= $this->renderSetLinks( $this->linksInside );							//  @todo: remove
			else																					//  @todo: remove
				$links		= $this->renderSetLinks( $this->linksOutside );							//  @todo: remove
		}																							//  @todo: remove
		$avatar	= '';
		if( $this->user && $this->showAvatar ){														//  user is available and avatars enabled
			if( $this->env->getModules()->has( 'Manage_My_User_Avatar' ) ){							//  use user avatar helper module
				$helper			= new View_Helper_UserAvatar( $this->env );							//  create helper
				$moduleConfig	= $config->getAll( 'module.manage_my_user_avatar.', TRUE );			//  get module config
				$helper->useGravatar( $moduleConfig->get( 'use.gravatar' ) );						//  use gravatar as fallback
				$helper->setUser( $this->user );													//  set user data
				$helper->setSize( $this->imageSize );												//  set image size
				$avatar	= $helper->render();														//  render avatar
			}
			else if( $this->env->getModules()->has( 'UI_Helper_Gravatar' ) ){					//  use gravatar helper module
				$helper		= new View_Helper_Gravatar( $this->env );								//  create helper
				$helper->setUser( $this->user );													//  set user data
				$helper->setSize( $this->imageSize );												//  set image size
				$avatar	= $helper->render();														//  render avatar
			}
			else if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) ){
				$avatar	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user fa-3x' ) );
			}
			$avatar	= UI_HTML_Tag::create( 'div', $avatar, array( 'class' => 'avatar' ) );			//  embed avatar in container
		}

		$labels	= array();
		if( $this->showUsername )
			$labels[]	= UI_HTML_Tag::create( 'div', $username, array( 'class' => 'username' ) );
		if( $this->showFullname )
			$labels[]	= UI_HTML_Tag::create( 'div', $fullname, array( 'class' => 'fullname' ) );
		if( $this->showEmail )
			$labels[]	= UI_HTML_Tag::create( 'div', $email, array( 'class' => 'email' ) );

		if( $labels )
			$labels		= UI_HTML_Tag::create( 'div', $labels, array( 'class' => 'labels' ) );
		else
			$labels		= "";

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
		if( !$pages )
			return;
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

	/**
	 *	@deprecated		use method showAvatar instead
	 *	@todo   		to be removed in version 0.8
	 */
	public function useAvatar( $boolean = NULL ){
		$this->showAvatar( $boolean );
	}

	public function showAvatar( $boolean = NULL ){
		$this->showAvatar		= (boolean) $boolean;
	}

	public function showEmail( $boolean = NULL ){
		$this->showEmail		= (boolean) $boolean;
	}

	public function showFullname( $boolean = NULL ){
		$this->showFullname		= (boolean) $boolean;
	}

	public function showUsername( $boolean = NULL ){
		$this->showUsername		= (boolean) $boolean;
	}
}
?>
