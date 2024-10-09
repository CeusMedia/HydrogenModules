<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Member
{
	protected Environment $env;
	protected bool $useGravatar		= TRUE;
	protected ?Entity_User $user	= NULL;
	protected ?string $url			= NULL;
	protected string $mode			= 'inline';
	protected Model_User $modelUser;
	protected View_Helper_Gravatar $helperGravatar;

	public function __construct( Environment $env )
	{
		$this->env				= $env;
		$this->helperGravatar	= new View_Helper_Gravatar( $this->env );
		$this->modelUser		= new Model_User( $this->env );
	}

	public function render(): string
	{
		if( !$this->user )
			return "UNKNOWN";
		if( !$this->useGravatar )
			return $this->user->username;

		$image	= $this->renderImage();

		switch( $this->mode ){
			case 'thumbnail':
				$url		= sprintf( $this->url, $this->user->userId );
				$link		= HtmlTag::create( 'div', $this->user->username, ['class'	=> 'autocut'] );
				$attributes	= ['class' => 'thumbnail'];
				if( $this->url )
					$attributes['onclick']	= "document.location.href='".$url."'";
				return HtmlTag::create( 'div', $image.$link, $attributes );
				break;
			case 'bar':
				$label		= $this->user->username;
				if( $this->url ){
					$url		= sprintf( $this->url, $this->user->userId );
					$label		= HtmlTag::create( 'a', $this->user->username, ['href' => $url] );
					$image		= HtmlTag::create( 'a', $image, ['href' => $url] );
				}
				$name	= $this->user->firstname.' '.$this->user->surname;
				$name	= HtmlTag::create( 'small', $name, ['class' => "muted"] );
				$image	= HtmlTag::create( 'div', $image, ['style' => 'float: left'] );
				$label	= '<span><b>'.$label.'</b><br/>'.$name.'</span>';
				$label	= HtmlTag::create( 'div', $label, ['style' => 'float: left; margin-left: 0.5em'] );
				return HtmlTag::create( 'div', $image.$label, ['class' => 'user clearfix'] );
				break;
			case 'inline':
			default:
				$label		= $this->user->username;
				$fullname	= $this->user->firstname.' '.$this->user->surname;
				if( strlen( trim( $fullname ) ) && $fullname !== $this->user->username ){
					$fullname	= HtmlTag::create( 'small', '('.$fullname.')', ['class' => "muted"] );
					$label		= $label.'&nbsp;'.$fullname;
				}
				if( $this->url ){
					$url		= sprintf( $this->url, $this->user->userId );
					$label		= HtmlTag::create( 'a', $label, ['href' => $url] );
					$image		= HtmlTag::create( 'a', $image, ['href' => $url] );
				}
				return HtmlTag::create( 'span', $image.'&nbsp;'.$label, ['class' => 'user'] );
				break;
		}
	}

	public function renderImage(): string
	{
		if( !$this->user )
			return '';
		$helperGravatar	= new View_Helper_Gravatar( $this->env );
		$helperGravatar->setUser( $this->user );
		$helperAvatar	= NULL;
		if( class_exists( 'View_Helper_UserAvatar' ) ){
			$helperAvatar	= new View_Helper_UserAvatar( $this->env );
			$helperAvatar->setUser( (int) $this->user->userId );
		}

		switch( $this->mode ){
			case 'thumbnail':
				$helperGravatar->setSize( 256 );
				$image		= $helperGravatar->render();
				if( $helperAvatar && $helperAvatar->has() ){
					$helperAvatar->setSize( 256 );
					$image	= $helperAvatar->render();
				}
				break;
			case 'bar':
				$helperGravatar->setSize( 40 );
				$image		= $helperGravatar->render();
				if( $helperAvatar && $helperAvatar->has() ){
					$helperAvatar->setSize( 40 );
					$image	= $helperAvatar->render();
				}
				break;
			case 'inline':
			default:
				$helperGravatar->setSize( 20 );
				$image		= $helperGravatar->render();
				if( $helperAvatar && $helperAvatar->has() ){
					$helperAvatar->setSize( 20 );
					$image	= $helperAvatar->render();
				}
		}
		return $image;
	}

	public static function renderImageStatic( Environment $env, $userObjectOrId, string $url = NULL, string $mode = NULL ): string
	{
		$helper	= new self( $env );
		$helper->setUser( $userObjectOrId );
		if( $url )
			$helper->setLinkUrl( $url );
		if( $mode )
			$helper->setMode( $mode );
		return $helper->renderImage();
	}

	public static function renderStatic( Environment $env, $userObjectOrId, string $url = NULL, string $mode = NULL ): string
	{
		$helper	= new self( $env );
		$helper->setUser( $userObjectOrId );
		if( $url )
			$helper->setLinkUrl( $url );
		if( $mode )
			$helper->setMode( $mode );
		return $helper->render();
	}

	public function setMode( string $mode ): self
	{
		$this->mode	= $mode;
		return $this;
	}

	public function setLinkUrl( string $url ): self
	{
		$this->url	= $url;
		return $this;
	}

	public function setUser( string|int|object $userObjectOrId ): self
	{
		if( is_object( $userObjectOrId ) )
			$this->user	= $userObjectOrId;
		else
			$this->user	= $this->modelUser->get( $userObjectOrId );
		return $this;
	}
}
