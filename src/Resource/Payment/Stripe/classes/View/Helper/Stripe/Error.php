<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Error
{
	protected Environment $env;
	protected int $code;
	protected array $map		= [];
	protected int $mode			= 1;
	protected array $words;

	const MODE_PLAIN		= 0;
	const MODE_HTML			= 1;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'resource/payment/stripe/error' );
		foreach( $this->words as $section => $pairs ){
			foreach( $pairs as $key => $label ){
				$this->map[$key]	= (object) [
					'section'	=> $section,
					'label'		=> $label,
				];
			}
		}
//		print_m( $this->map );die;
	}

	public function render(): string
	{
		if( $this->code === 0 )
			return '';
		$message	= $this->map[$this->code]->label;
		switch( $this->mode ){
			case self::MODE_HTML:
				$code	= HtmlTag::create( 'small', '('.$this->code.')', ['class' => 'muted'] );
				return $message.' '.$code;
			case self::MODE_PLAIN:
			default:
				return $message;
		}
	}

	public function setCode( int $code ): self
	{
		if( !array_key_exists( $code, $this->map ) )
			throw new RangeException( sprintf( 'Unknown error code: %s', $code ) );
		$this->code	= $code;
		return $this;
	}

	public function setMode( int $mode ): self
	{
		$this->mode		= $mode;
		return $this;
	}
}
