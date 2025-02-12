<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Navigation_Bootstrap_AccountMenu
{
	public string $guestLabel			= "Guest";
	public string $guestEmail			= "<em>(not logged in)</em>";

	protected Environment $env;
	protected ?Entity_User $user		= NULL;
	protected bool $showAvatar			= TRUE;
	protected bool $showEmail			= FALSE;
	protected bool $showFullname		= TRUE;
	protected bool $showUsername		= TRUE;
	protected array $linksInside		= [];
	protected array $linksOutside		= [];
	protected int $imageSize			= 32;
	protected ?Model_Menu $menu			= NULL;
	protected $scope;
	protected Dictionary $moduleConfig;

	public function __construct( Environment $env )
	{
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
	public function addInsideLink( string $path, string $label, string $icon = NULL ): self
	{
		$this->linksInside[]	= (object)[
			'icon'		=> $icon,
			'label'		=> $label,
			'link'		=> $path,
		];
		return $this;
	}

	/**
	 *	@deprecated 	use menu instead by calling setLinks
	 *	@todo   		to be removed in version 0.8
	 */
	public function addInsideLinkLine(): self
	{
		$this->linksInside[]	= 'line';
		return $this;
	}

	/**
	 *	@deprecated 	use menu instead by calling setLinks
	 *	@todo   		to be removed in version 0.8
	 */
	public function addOutsideLink( string $path, string $label, string $icon = NULL ): self
	{
		$this->linksOutside[]	= (object) [
			'icon'		=> $icon,
			'label'		=> $label,
			'link'		=> $path,
		];
		return $this;
	}

	/**
	 *	@deprecated 	use menu instead by calling setLinks
	 *	@todo   		to be removed in version 0.8
	 */
	public function addOutsideLinkLine(): self
	{
		$this->linksOutside[]	= 'line';
		return $this;
	}

	public function render( string $classMenu = '' ): string
	{
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
				return '';
		}
		else{																						//  @todo: remove
			if( $this->user )																		//  @todo: remove
				$links		= $this->renderSetLinks( $this->linksInside );							//  @todo: remove
			else																					//  @todo: remove
				$links		= $this->renderSetLinks( $this->linksOutside );							//  @todo: remove
		}																							//  @todo: remove
		$avatar	= '';
		if( NULL !== $this->user && $this->showAvatar ){														//  user is available and avatars enabled
			if( $this->env->getModules()->has( 'Manage_My_User_Avatar' ) ){							//  use user avatar helper module
				$helper			= new View_Helper_UserAvatar( $this->env );							//  create helper
				$moduleConfig	= $config->getAll( 'module.manage_my_user_avatar.', TRUE );			//  get module config
				$helper->useGravatar( (bool) $moduleConfig->get( 'use.gravatar' ) );				//  use gravatar as fallback
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
				$avatar	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user fa-3x'] );
			}
			$avatar	= HtmlTag::create( 'div', $avatar, ['class' => 'avatar'] );			//  embed avatar in container
		}

		$labels	= [];
		if( $this->showUsername )
			$labels[]	= HtmlTag::create( 'div', $username, ['class' => 'username'] );
		if( $this->showFullname )
			$labels[]	= HtmlTag::create( 'div', $fullname, ['class' => 'fullname'] );
		if( $this->showEmail )
			$labels[]	= HtmlTag::create( 'div', $email, ['class' => 'email'] );

		if( $labels )
			$labels		= HtmlTag::create( 'div', $labels, ['class' => 'labels'] );
		else
			$labels		= "";

		$trigger		= HtmlTag::create( 'div', [
			$avatar,
			$labels,
			HtmlTag::create( 'div', '', ['class' => 'clearfix'] ),
		], [
			'id' 			=> 'drop-account',
			'role'			=> 'button',
			'class'			=> 'dropdown-toggle',
			'data-toggle'	=> 'dropdown',
		] );
		return HtmlTag::create( 'div', [$trigger, $links], [
			'id' => 'account-menu',
			'class' => 'dropdown '.$classMenu
		] );
	}

	protected function renderMenuLinks(): string
	{
		$list	= [];
		$pages	= $this->menu->getPages( $this->scope );
		if( !$pages )
			return '';
		foreach( $pages as $page ){
			$class	= $page->active ? 'active' : NULL;
//			$href	= $page->path == "index" ? './' : './'.$page->link;
			$link	= HtmlTag::create( 'a', self::renderLabelWithIcon( $page ), [
				'role'		=> "menuitem",
				'tabindex'	=> "-1",
				'href'		=> $page->link,
			] );
			$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
		return HtmlTag::create( 'ul', $list, [
			'class'				=> "dropdown-menu pull-right",
			'role'				=> "menu",
			'aria-labelledby'	=> "drop-account",
		] );
	}

	protected function renderSetLinks( array $links ): string
	{
		$list	= [];
		foreach( $links as $link ){
			if( is_object( $link ) ){
				$icon	= "";
				if( $link->icon )
					$icon	= HtmlTag::create( 'i', "", ['class' => $link->icon] ).'&nbsp;';
				$attributes	= [
					'role'		=> "menuitem",
					'tabindex'	=> "-1",
					'href'		=> $link->link,
				];
				$link	= HtmlTag::create( 'a', $icon.$link->label, $attributes );
				$list[]	= HtmlTag::create( 'li', $link , ['role' => 'presentation'] );
			}
			else{
				$attributes	= [
					'role'	=> "presentation",
					'class'	=> "divider"
				];
				$list[]	= HtmlTag::create( 'li', "", $attributes );
			}
		}
		$attributes	= [
			'class'				=> "dropdown-menu pull-right",
			'role'				=> "menu",
			'aria-labelledby'	=> "drop-account",
		];
		if( !$list )
			return '';
		return HtmlTag::create( 'ul', $list, $attributes );
	}

	public function setLinks( $menu, $scope ): self
	{
		$this->menu		= $menu;
		$this->scope	= $scope;
		return $this;
	}

	/**
	 *	@param		Entity_User		$user
	 *	@return		self
	 */
	public function setUser( Entity_User $user ): self
	{
		$this->user	= $user;
		return $this;
	}

	public function setImageSize( int $size ): self
	{
		$this->imageSize	= $size;
		return $this;
	}

	/**
	 *	@deprecated		use method showAvatar instead
	 *	@todo   		to be removed in version 0.8
	 */
	public function useAvatar( bool $boolean = NULL ): self
	{
		$this->showAvatar( $boolean );
		return $this;
	}

	public function showAvatar( bool $boolean = NULL ): self
	{
		$this->showAvatar		= $boolean;
		return $this;
	}

	public function showEmail( bool $boolean = NULL ): self
	{
		$this->showEmail		= $boolean;
		return $this;
	}

	public function showFullname( bool $boolean = NULL ): self
	{
		$this->showFullname		= $boolean;
		return $this;
	}

	public function showUsername( bool $boolean = NULL ): self
	{
		$this->showUsername		= $boolean;
		return $this;
	}

	protected function renderLabelWithIcon( $entry ): string
	{
		if( !isset( $entry->icon ) )
			return $entry->label;
		$class	= $entry->icon;
//		if( !preg_match( "/^fa/", $entry->icon ) )
//			$class	= 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
		$icon	= HtmlTag::create( 'i', '', ['class' => $class] );
		return $icon.'&nbsp;'.$entry->label;
	}
}
