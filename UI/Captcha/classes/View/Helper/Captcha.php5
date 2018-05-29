<?php
class View_Helper_Captcha /*extends CMF_Hydrogen_View_Helper*/{

	protected $background	= array( 255, 255, 255 );
	protected $height		= 55;
	protected $fontSize		= 16

	public function __construct( $env ){
		$this->env			= $env;
		$this->session		= $this->env->getSession();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
	}

	static public function checkCaptcha( $env, $word ){
		return $env->getSession()->get( 'captcha' ) == $word;
	}

	public function setBackgroundColor( $red, $green, $blue ){
		$this->background	= array( $red, $green, $blue );
		return $this;
	}

	public function setHeight( $height ){
		$this->height	= $height;
		return $this;
	}

	public function setFontSize( $size ){
		$this->fontSize	= $size;
		return $this;
	}

	public function render(){
		$captcha	= new UI_Image_Captcha();
		$captcha->useUnique	= TRUE;
		if( $this->moduleConfig->get( 'strength' ) == 'hard' ){
			$captcha->useDigits	= TRUE;
			$captcha->useLarge	= TRUE;
		}
		$word		= $captcha->generateWord();
		$this->session->set( 'captcha', $word );
		$captcha->background	= $this->background;
	//	$captcha->width			= 100;
		$captcha->height		= $this->height;
		$captcha->fontSize		= $this->fontSize;
		$captcha->offsetX		= 0;
		$captcha->offsetY		= 0;
		$captcha->font			= "./themes/common/font/Tahoma.ttf";
		$filePath				= "captcha_".uniqid().".jpg";
		$captcha->generateImage( $word, $filePath );
		$image	= base64_encode( file_get_contents( $filePath ) );
		unlink( $filePath );
		return UI_HTML_Tag::create( 'img', NULL, array( 'src' => 'data:image/jpg;base64,'.$image ) );
	}
}
