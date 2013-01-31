<?php
class View_Helper_Gravatar{

	protected $env;
	protected $config;

	public function __construct( $env ){
		$this->env		= $env;
		$this->config	= $this->env->getConfig()->getAll( 'module.ui_helper_gravatar.', TRUE );
	}

	public function getUrl( $email, $size = NULL, $rate = NULL, $default = NULL ){
		$size		= $size ? $size : $this->config->get( 'size' );
		$rate		= $rate ? $rate : $this->config->get( 'rate' );
		$default	= $default ? $default : $this->config->get( 'default' );
		$gravatar	= new Net_API_Gravatar( $size, $rate, $default );
		return $gravatar->getUrl( $email );
	}

	public function getImage( $email, $size = NULL, $attributes = array() ){
		$attributes['src']		= $this->getUrl( $email, $size );
		$attributes['width']	= $size;
		$attributes['height']	= $size;
		return UI_HTML_Tag::create( 'img', NULL, $attributes );
	}
}
?>
