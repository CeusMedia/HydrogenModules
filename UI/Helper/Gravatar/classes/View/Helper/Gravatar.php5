<?php
class View_Helper_Gravatar{

	protected $config;
	protected $env;
	protected $user;
	protected $size		= 32;
	protected $rating	= 'g';
	protected $default	= 'mm';

	public function __construct( $env ){
		$this->env		= $env;
		$config	= $this->env->getConfig()->getAll( 'module.ui_helper_gravatar.', TRUE );
		if( $config->get( 'size' ) )
			$this->setSize( $config->get( 'size' ) );
		if( $config->get( 'rate' ) )
			$this->setRating( $config->get( 'rate' ) );
		if( $config->get( 'default' ) )
			$this->setDefault( $config->get( 'default' ) );
	}

	public function getImageUrl(){
		if( !$this->user )
			throw new RuntimeException( "No user set" );
		$gravatar	= new Net_API_Gravatar( $this->size, $this->rating, $this->default );
		return $gravatar->getUrl( $this->user->email );
	}

	static public function renderStatic( $email, $size = NULL, $attributes = array() ){
		$attributes['src']		= $this->getUrl( $email, $size );
		$attributes['width']	= $size;
		$attributes['height']	= $size;
		return UI_HTML_Tag::create( 'img', NULL, $attributes );
	}

	public function render(){
		if( !$this->user )
			throw new RuntimeException( "No user set" );
		$attributes['src']		= $this->getImageUrl( $this->user->email, $this->size, $this->rating );
		$attributes['width']	= $this->size;
		$attributes['height']	= $this->size;
		return UI_HTML_Tag::create( 'img', NULL, $attributes );
	}

	public function setDefault( $theme ){
		$this->default	= $theme;
	}

	public function setRating( $rating ){
		$this->rating	= $rating;
	}

	public function setSize( $size ){
		$this->size		= $size;
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
}
?>
