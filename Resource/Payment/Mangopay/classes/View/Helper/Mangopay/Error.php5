<?php
class View_Helper_Mangopay_Error{

	protected $env;
	protected $code;
	protected $map			= array();
	protected $mode			= 1;

	const MODE_PLAIN		= 0;
	const MODE_HTML			= 1;

	public function __construct( $env ){
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'resource/payment/mangopay/error' );
		foreach( $this->words as $section => $pairs ){
			foreach( $pairs as $key => $label ){
				$this->map[$key]	= (object) array(
					'section'	=> $section,
					'label'		=> $label,
				);
			}
		}
//		print_m( $this->map );die;
	}

	public function setCode( $code ){
		if( !array_key_exists( $code, $this->map ) )
			throw new RangeException( sprintf( 'Unknown error code: %s', $code ) );
		$this->code	= $code;
	}

	public function setMode( $mode ){
		$this->mode		= $mode;
	}

	public function render(){
		if( (int) $this->code === 0 )
			return '';
		$message	= $this->map[$this->code]->label;
		switch( $this->mode ){
			case self::MODE_HTML:
				$code	= UI_HTML_Tag::create( 'small', '('.$this->code.')', array( 'class' => 'muted' ) );
				return $message.' '.$code;
			case self::MODE_PLAIN:
			default:
				return $message;

		}
	}
}
