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
				$image		= $gravatar->getImage( $this->user->email, 256 );
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
				$image		= $gravatar->getImage( $this->user->email, 20 );
				$link		= UI_HTML_Tag::create( 'a', $this->user->username, array(
					'href'	=> sprintf( $this->url, $this->user->userId )
				) );
				return UI_HTML_Tag::create( 'span', $image.'&nbsp;'.$link, array(
					'class'	=> 'user',
				) );
				break;
		}
	}

	static public function renderStatic( $env, $userObjectOrId ){
		$helper	= new self( $env );
		$helper->setUser( $userObjectOrId );
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
