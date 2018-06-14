<?php
class View_Helper_Captcha /*extends CMF_Hydrogen_View_Helper*/{

	protected $background	= array( 255, 255, 255 );
	protected $height		= 55;
	protected $fontSize		= 16;
	protected $format		= 'image';

	CONST FORMAT_IMAGE		= 0;
	CONST FORMAT_RAW		= 1;

	public function __construct( $env ){
		$this->env			= $env;
		$this->session		= $this->env->getSession();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
		$this->captcha		= new UI_Image_Captcha();
		$this->captcha->useUnique	= TRUE;
	}

	static public function checkCaptcha( $env, $word ){
		return $env->getSession()->get( 'captcha' ) == $word;
	}

	public function setBackgroundColor( $red, $green, $blue ){
		$this->background	= array( $red, $green, $blue );
		return $this;
	}

	public function setFontSize( $size ){
		$this->fontSize	= $size;
		return $this;
	}

	public function setFormat( $format ){
		$this->format	= $format;
	}

	public function setHeight( $height ){
		$this->captcha->height	= $height;
		return $this;
	}

	public function setWidth( $width ){
		$this->captcha->width	= $width;
		return $this;
	}

	public function setLength( $length ){
		$this->captcha->length	= max( 1, min( 8, (int) $length ) );
	}

	public function setStrength( $strength ){
		switch( strtolower( $strength ) ){
			case 'soft':
				$this->captcha->useDigits	= FALSE;
				$this->captcha->useLarge	= FALSE;
				break;
			case 'hard':
				$this->captcha->useDigits	= TRUE;
				$this->captcha->useLarge	= TRUE;
				break;
		}
	}

	public function render(){
		$word		= $this->captcha->generateWord();
		$this->session->set( 'captcha', $word );
		$this->captcha->background	= $this->background;
		$this->captcha->fontSize	= $this->fontSize;
		$this->captcha->offsetX		= 0;
		$this->captcha->offsetY		= 0;
		$this->captcha->font		= "./themes/common/font/tahoma.ttf";
		$pathName	= $this->moduleConfig->get( 'path' );
		$filePath	= $pathName."captcha_".uniqid().".jpg";
		$this->captcha->generateImage( $word, $filePath );
		$image	= file_get_contents( $filePath );
		unlink( $filePath );
		if( $this->format === self::FORMAT_RAW )
			return $image;
		return UI_HTML_Tag::create( 'img', NULL, array(
			'src'	=> 'data:image/jpg;base64,'.base64_encode( $image ),
			'class'	=> 'captcha-image',
		) );
	}
}
