<?php
class View_Helper_Member{

	protected $useGravatar	= TRUE;
	protected $user;
	protected $url;
	protected $mode	= 'inline';

	public function __construct( $env ){
		$this->env	= $env;
		$this->helperGravatar	= new View_Helper_Gravatar( $this->env );
		$this->modelUser		= new Model_User( $this->env );
	}

	public function render(){
		if( !$this->user )
			return "UNKNOWN";
		if( !$this->useGravatar )
			return $this->user->username;

		$image	= $this->renderImage();

		switch( $this->mode ){
			case 'thumbnail':
				$url		= sprintf( $this->url, $this->user->userId );
				$link		= UI_HTML_Tag::create( 'div', $this->user->username, array( 'class'	=> 'autocut' ) );
				$attributes	= array( 'class' => 'thumbnail' );
				if( $this->url )
					$attributes['onclick']	= "document.location.href='".$url."'";
				return UI_HTML_Tag::create( 'div', $image.$link, $attributes );
				break;
			case 'bar':
				$label		= $this->user->username;
				if( $this->url ){
					$url		= sprintf( $this->url, $this->user->userId );
					$label		= UI_HTML_Tag::create( 'a', $this->user->username, array( 'href' => $url ) );
					$image		= UI_HTML_Tag::create( 'a', $image, array( 'href' => $url ) );
				}
				$name	= $this->user->firstname.' '.$this->user->surname;
				$name	= UI_HTML_Tag::create( 'small', $name, array( 'class' => "muted" ) );
				$image	= UI_HTML_Tag::create( 'div', $image, array( 'style' => 'float: left' ) );
				$label	= '<span><b>'.$label.'</b><br/>'.$name.'</span>';
				$label	= UI_HTML_Tag::create( 'div', $label, array( 'style' => 'float: left; margin-left: 0.5em' ) );
				return UI_HTML_Tag::create( 'div', $image.$label, array( 'class' => 'user clearfix' ) );
				break;
			case 'inline':
			default:
				$label		= $this->user->username;
				$fullname	= $this->user->firstname.' '.$this->user->surname;
				if( strlen( trim( $fullname ) ) && $fullname !== $this->user->username ){
					$fullname	= UI_HTML_Tag::create( 'small', '('.$fullname.')', array( 'class' => "muted" ) );
					$label		= $label.'&nbsp;'.$fullname;
				}
				if( $this->url ){
					$url		= sprintf( $this->url, $this->user->userId );
					$label		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
					$image		= UI_HTML_Tag::create( 'a', $image, array( 'href' => $url ) );
				}
				return UI_HTML_Tag::create( 'span', $image.'&nbsp;'.$label, array( 'class' => 'user' ) );
				break;
		}
	}

	public function renderImage(){
		if( !$this->user )
			return;
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
				if( $helperAvatar && $helperAvatar->has() )
					$image	= $helperAvatar->render();
				break;
			case 'bar':
				$helperGravatar->setSize( 40 );
				$image		= $helperGravatar->render();
				if( $helperAvatar && $helperAvatar->has() )
					$image	= $helperAvatar->render();
				break;
			case 'inline':
			default:
				$helperGravatar->setSize( 20 );
				$image		= $helperGravatar->render();
				if( $helperAvatar && $helperAvatar->has() )
					$image	= $helperAvatar->render();
		}
		return $image;
	}

	static public function renderImageStatic( $env, $userObjectOrId, $url = NULL, $mode = NULL ){
		$helper	= new self( $env );
		$helper->setUser( $userObjectOrId );
		if( $url )
			$helper->setLinkUrl( $url );
		if( $mode )
			$helper->setMode( $mode );
		return $helper->renderImage();
	}

	static public function renderStatic( $env, $userObjectOrId, $url = NULL, $mode = NULL ){
		$helper	= new self( $env );
		$helper->setUser( $userObjectOrId );
		if( $url )
			$helper->setLinkUrl( $url );
		if( $mode )
			$helper->setMode( $mode );
		return $helper->render();
	}

	public function setMode( $mode ){
		$this->mode	= $mode;
	}

	public function setLinkUrl( $url ){
		$this->url	= $url;
	}

	public function setUser( $userObjectOrId ){
		if( is_object( $userObjectOrId ) )
			$this->user	= $userObjectOrId;
		else
			$this->user	= $this->modelUser->get( $userObjectOrId );
	}
}
