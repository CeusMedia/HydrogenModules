<?php
class View_Helper_CSRF{

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function render( $formName ){
		$token	= Logic_CSRF::getInstance( $this->env )->getToken( $formName );
		$input1	= UI_HTML_Tag::create( 'input', NULL, array(
			'type'	=> 'hidden',
			'name'	=> 'csrf_token',
			'value'	=> $token
		) );
		$input2	= UI_HTML_Tag::create( 'input', NULL, array(
			'type'	=> 'hidden',
			'name'	=> 'csrf_form_name',
			'value'	=> $formName
		) );
		return $input1.$input2;
	}

	static public function renderStatic( $env, $formName ){
		$helper	= new self( $env );
		return $helper->render( $formName );
	}
}
