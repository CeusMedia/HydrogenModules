<?php

use CeusMedia\Common\Net\HTTP\Post as HttpPost;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\Image\Captcha as ImageCaptcha;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Captcha /*extends CMF_Hydrogen_View_Helper*/
{
	protected array $background		= [255, 255, 255];
	protected int $height			= 55;
	protected int|float $fontSize	= 16;
	protected string $format		= 'image';
	protected string $mode			= 'default';
	protected string $recaptchaApi	= 'https://www.google.com/recaptcha/api.js';

	protected Environment $env;
	protected $session;
	protected $moduleConfig;
	protected ImageCaptcha $captcha;

	public CONST FORMAT_IMAGE		= 0;
	public CONST FORMAT_RAW			= 1;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->session		= $this->env->getSession();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
		$this->captcha		= new ImageCaptcha();
		$this->captcha->useUnique	= TRUE;
	}

	public static function checkCaptcha( Environment $env, ?string $word )
	{
		$moduleConfig	= $env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
		if( 'recaptcha' === $moduleConfig->get( 'mode' ) ){
			$url		= 'https://www.google.com/recaptcha/api/siteverify';
			$request	= new HttpPost( $url );
			$request->setContentType( 'application/x-www-form-urlencoded' );
			$request->setContent( http_build_query( [
				'secret'	=> $moduleConfig->get( 'recaptcha.secret' ),
				'response'	=> $word
			] ) );
			$response	= json_decode( $request->send() );
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
		$this->background	= [$red, $green, $blue];
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
		return $this;
	}

	public function setHeight( int $height ): self
	{
		$this->captcha->height	= $height;
		return $this;
	}

	public function setMode( string $mode ): self
	{
		if( !in_array( $mode, ['default', 'recaptcha'] ) )
			throw new InvalidArgumentException( 'Invalid mode' );
		$this->mode	= $mode;
		return $this;
	}

	public function setLength( int $length ): self
	{
		$this->captcha->length	= max( 1, min( 8, (int) $length ) );
		return $this;
	}

	public function setStrength( string $strength ): self
	{
		switch( strtolower( $strength ) ){
			case 'soft':
				$this->captcha->useDigits	= FALSE;
				$this->captcha->useLarges	= FALSE;
				break;
			case 'hard':
				$this->captcha->useDigits	= TRUE;
				$this->captcha->useLarges	= TRUE;
				break;
		}
		return $this;
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
		if( self::FORMAT_RAW === $this->format )
			return $image;
		return HtmlTag::create( 'img', NULL, [
			'src'	=> 'data:image/jpg;base64,'.base64_encode( $image ),
			'class'	=> 'captcha-image',
		] );
	}

	protected function renderRecaptcha(): string
	{
		$this->env->getPage()->js->addUrl( 'https://www.google.com/recaptcha/api.js' );
		return HtmlTag::create( 'div', '', [
			'class'			=> "g-recaptcha",
			'data-sitekey'	=> $this->moduleConfig->get( 'recaptcha.key' ),
		] );
	}
}
