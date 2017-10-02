<?php
class View_Helper_Mangopay_Error{

	protected $env;
	protected $map			= array();

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

	public function render(){
		if( (int) $this->code === 0 )
			return '';
		return $this->map[$this->code]->label;
	}
}
