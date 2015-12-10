<?php
class View_Helper_UserAvatar{

	protected $userId;
	protected $size;
	protected $env;
	protected $moduleConfig;
	protected $modelAvatar;

	public function __construct( $env ){
		$this->env			= $env;
		$this->modelAvatar	= new Model_User_Avatar( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_my_user_avatar.', TRUE );
	}

	public function get(){
		if( !$this->userId )
			throw new RuntimeException( "No user set" );
		$avatar	= $this->modelAvatar->getByIndices( array(
			'userId'	=> $this->userId,
//			'status'	=> 1,
		) );
		if( !$avatar )
			return NULL;
		$path	= $this->moduleConfig->get( 'path.images' );
		$avatar->url	= $this->env->url.$path.$avatar->filename;
		return $avatar;
	}

	public function getImageUrl(){
		$avatar	= $this->get();
		if( $avatar )
			return $avatar->url;
		if( class_exists( 'View_Helper_Gravatar' ) ){
			$helper	= new View_Helper_Gravatar( $this->env );
			$helper->setUser( $this->userId );
			$helper->setSize( $this->size );
			return $helper->getImageUrl();
		}
	}

	public function has(){
		return (bool) $this->get();
	}

	public function render(){
		$avatar	= $this->get();
		if( !$avatar ){
			if( class_exists( 'View_Helper_Gravatar' ) ){
				$helper	= new View_Helper_Gravatar( $this->env );
				$helper->setUser( $this->userId );
				$helper->setSize( $this->size );
				return $helper->render();
			}
			return '';
		}
		$path	= $this->moduleConfig->get( 'path.images' );
		$url	= $this->env->url.$path.$avatar->filename;
		return UI_HTML_Tag::create( 'img', NULL, array(
			'src'		=> $url,
			'class'		=> 'avatar',
			'width'		=> $this->size,
			'height'	=> $this->size,
		) );
	}

	static public function renderStatic( $env, $userObjectOrId, $size ){
		$helper	= new self( $env );
		$helper->setUser( $userObjectOrId );
		$helper->setSize( $size );
		return $helper->render();
	}

	public function setSize( $size ){
		$this->size	= $size;
	}

	public function setUser( $userObjectOrId ){
		if( is_object( $userObjectOrId ) )
			$this->userId	= (int) $userObjectOrId->userId;
		else if( is_int( $userObjectOrId ) )
			$this->userId	= $userObjectOrId;
		else
			throw new InvalidArgumentException( "Given data is neither an user object nor an user ID" );
	}
}
?>
