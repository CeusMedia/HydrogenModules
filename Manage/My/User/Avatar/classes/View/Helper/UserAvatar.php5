<?php
class View_Helper_UserAvatar{

	protected $userId;
	protected $size;
	protected $env;
	protected $moduleConfig;
	protected $modelAvatar;
	protected $useGravatar;
	protected $cache			= array();

	public function __construct( $env ){
		$this->env			= $env;
		$this->modelAvatar	= new Model_User_Avatar( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_my_user_avatar.', TRUE );
		$this->useGravatar( $this->moduleConfig->get( 'fallback.gravatar' ) );
	}

	public function get(){
		if( !$this->userId )
			throw new RuntimeException( "No user set" );
		if( isset( $this->cache[$this->userId] ) )
			return $this->cache[$this->userId];
		$avatar	= $this->modelAvatar->getByIndices( array(
			'userId'	=> $this->userId,
//			'status'	=> 1,
		) );
		$this->cache[$this->userId]	= $avatar;
		if( !$avatar )
			return NULL;
		return $avatar;
	}

	public function getImageUrl(){
		$avatar	= $this->get();
		if( $avatar ){
			$path		= $this->moduleConfig->get( 'path.images' );
			$filename	= $this->userId.'_'.$avatar->filename;
			if( $this->size < $this->moduleConfig->get( 'image.size.large' ) )
				$filename	= $this->userId.'__'.$avatar->filename;
			if( $this->size < $this->moduleConfig->get( 'image.size.medium' ) )
				$filename	= $this->userId.'___'.$avatar->filename;
			return $this->env->url.$path.$filename;
		}
		if( $this->useGravatar ){
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
		if( $avatar ){
			return UI_HTML_Tag::create( 'img', NULL, array(
				'src'		=> $this->getImageUrl(),
				'class'		=> 'avatar',
				'width'		=> $this->size,
				'height'	=> $this->size,
			) );
		}
		if( $this->useGravatar ){
			$helper	= new View_Helper_Gravatar( $this->env );
			$helper->setUser( $this->userId );
			$helper->setSize( $this->size );
			return $helper->render();
		}
		return '';
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

	/**
	 *	Toggle to use Gravatar module as fallback.
	 *	Detects module UI:Helper:Gravatar.
	 *	@access		public
	 *	@param		boolean		$boolean		Flag: use Gravatar module if available
	 *	@return		void
	 */
	public function useGravatar( $boolean ){
		$hasGravatarModule	= $this->env->getModules()->has( 'UI_Helper_Gravatar' );
		$this->useGravatar	= $hasGravatarModule && $boolean;
	}

}
?>
