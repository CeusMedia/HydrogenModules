<?php
class View_Helper_Captcha /*extends CMF_Hydrogen_View_Helper*/
{
	protected $background	= array( 255, 255, 255 );
	protected $height		= 55;
	protected $fontSize		= 16;
	protected $format		= 'image';
	protected $mode			= 'default';
	protected $recaptchaApi	= 'https://www.google.com/recaptcha/api.js';

	CONST FORMAT_IMAGE		= 0;
	CONST FORMAT_RAW		= 1;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env			= $env;
		$this->session		= $this->env->getSession();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
		$this->captcha		= new UI_Image_Captcha();
		$this->captcha->useUnique	= TRUE;
	}

	public static function checkCaptcha( CMF_Hydrogen_Environment $env, string $word )
	{
		$moduleConfig	= $env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
		if( $moduleConfig->get( 'mode' ) === 'recaptcha' ){
			$request	= new Net_HTTP_Post();
			$url		= 'https://www.google.com/recaptcha/api/siteverify';
			$response	= json_decode( $request->send( $url, array(
				'response'	=> $env->getRequest()->get( 'g-recaptcha-response' ),
				'secret'		=> $moduleConfig->get( 'recaptcha.secret' ),
				'remoteip'	=> getEnv( 'REMOTE_ADDR' ),
			) ) );
			return $response->success;
		}
		return $env->getSession()->get( 'captcha' ) == $word;
	}

	public function render(): string
	{
		if( $this->mode === "recaptcha" )
			return $this->renderRecaptcha();
		return $this->renderDefault();
	}

	public function setBackgroundColor( int $red, int $green, int $blue ): self
	{
		$this->background	= array( $red, $green, $blue );
		return $this;
	}

	public function setFontSize( int $size ): self
	{
		$this->fontSize	= $size;
		return $this;
	}

	public function setFormat( int $format ): self
	{
		$this->format	= $format;
	}

	public function setHeight( int $height ): self
	{
		$this->captcha->height	= $height;
		return $this;
	}

	public function setMode( string $mode ): self
	{
		if( !in_array( $mode, array( 'default', 'recaptcha' ) ) )
			throw new InvalidArgumentException( 'Invalid mode' );
		$this->mode	= $mode;
	}

	public function setLength( int $length ): self
	{
		$this->captcha->length	= max( 1, min( 8, (int) $length ) );
	}

	public function setStrength( string $strength ): self
	{
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

	public function setWidth( int $width ): self
	{
		$this->captcha->width	= $width;
		return $this;
	}

	protected function renderDefault(): string
	{
		$word		= $this->captcha->generateWord();
		$this->session->set( 'captcha', $word );
		$this->captcha->background	= $this->background;
		$this->captcha->fontSize	= $this->fontSize;
		$this->captcha->offsetX		= 0;
		$this->captcha->offsetY		= 0;
		$this->captcha->font		= "./themes/common/font/tahoma.ttf";
		$pathName	= $this->moduleConfig->get( 'default.path' );
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

	protected function renderRecaptcha(): string
	{
		$this->env->getPage()->js->addUrl( 'https://www.google.com/recaptcha/api.js' );
		return UI_HTML_Tag::create( 'div', '', array(
			'class'					=> "g-recaptcha",
			'data-sitekey'	=> $this->moduleConfig->get( 'recaptcha.key' ),
		) );
	}
}
