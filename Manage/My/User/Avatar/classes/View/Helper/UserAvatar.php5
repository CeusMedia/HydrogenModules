<?php
class View_Helper_Avatar{

	public function __construct( $env ){
		$this->env	= $env;
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_my_user_avatar.', TRUE );
	}

	public function render(){
		$model	= new Model_User_Avatar( $this->env );
		$avatar	= $model->getByIndex( 'userId', $userId );
		if( !$avatar )
			return '';
		$path	= $this->moduleConfig->get( 'path.images' );
		$url	= $this->env->url.$path.$avatar->filename;
		return UI_HTML_Tag::create( 'img', NULL, array(
			'src'	=> $url,
			'class'	=> 'avatar',
		) );
	}

	static public function renderStatic( $env, $userId ){
		$helper	= new self( $env );
		$helper->setUser( $userId );
		return $helper->render();
	}

	public function setUser( $userObjectOrId ){
		if( is_object( $userObjectOrId ) )
			$this->userId	= $userObjectOrId->userId;
		else if( is_int( $userObjectOrId ) )
			$this->userId	= $userObjectOrId;
		else
			throw new InvalidArgumentException( "Given data is neither an user object nor an user ID" );
	}
}
?>
