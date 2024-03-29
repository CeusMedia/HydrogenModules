<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_CSRF
{
	protected $env;
	protected $formName;

	public function __construct( $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@todo		remove formName after other modules have been updated
	 */
	public function render( $formName = NULL )
	{
		$formName	= $formName ? $formName : $this->formName;
		if( !$formName )
			throw new RuntimeException( 'No form name set' );
//		$token	= $this->env->getLogic()->get( 'CSRF' )->getToken( $formName );
		$logic	= Logic_CSRF::getInstance( $this->env );
		$token	= $logic->getToken( $formName );
		$input1	= HtmlTag::create( 'input', NULL, [
			'type'	=> 'hidden',
			'name'	=> 'csrf_token',
			'value'	=> $token
		] );
		$input2	= HtmlTag::create( 'input', NULL, [
			'type'	=> 'hidden',
			'name'	=> 'csrf_form_name',
			'value'	=> $formName
		] );
		return $input1.$input2;
	}

	public static function renderStatic( Environment $env, $formName )
	{
		$helper	= new self( $env );
		$helper->setFormName( $formName );
		return $helper->render();
	}

	public function setFormName( $formName ): self
	{
		$this->formName	= $formName;
		return $this;
	}
}
