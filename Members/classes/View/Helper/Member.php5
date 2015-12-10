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

		switch( $this->mode ){
			case 'thumbnail':
				$gravatar	= new View_Helper_Gravatar( $this->env );
				$gravatar->setUser( $this->user );
				$gravatar->setSize( 256 );
				$image		= $gravatar->render();
				if( class_exists( 'View_Helper_UserAvatar' ) ){
					$helper	= new View_Helper_UserAvatar( $this->env );
					$helper->setUser( (int) $this->user->userId );
					if( $helper->has() )
						$image	= $helper->render();
				}
				$url		= sprintf( $this->url, $this->user->userId );
				$link		= UI_HTML_Tag::create( 'div', $this->user->username, array(
					'class'	=> 'autocut'
				) );
				$attributes	= array( 'class' => 'thumbnail' );
				if( $this->url )
					$attributes['onclick']	= "document.location.href='".$url."'";
				return UI_HTML_Tag::create( 'div', $image.$link, $attributes );
				break;
			case 'inline':
			default:
				$gravatar	= new View_Helper_Gravatar( $this->env );
				$gravatar->setUser( $this->user );
				$gravatar->setSize( 20 );
				$image		= $gravatar->render();
				if( class_exists( 'View_Helper_UserAvatar' ) ){
					$helper	= new View_Helper_UserAvatar( $this->env );
					$helper->setUser( (int) $this->user->userId );
					if( $helper->has() )
						$image	= $helper->render();
				}
				$label		= $this->user->username;
				if( $this->url ){
					$url		= sprintf( $this->url, $this->user->userId );
					$label		= UI_HTML_Tag::create( 'a', $this->user->username, array(
						'href'	=> $url
					) );
					$image		= UI_HTML_Tag::create( 'a', $image, array(
						'href'	=> $url
					) );

				}
				return UI_HTML_Tag::create( 'span', $image.'&nbsp;'.$label, array(
					'class'	=> 'user',
				) );
				break;
		}
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
