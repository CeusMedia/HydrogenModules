<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_CSRF
{
	protected Environment $env;
	protected ?string $formName		= NULL;

	/**
	 *	@param		Environment	$env
	 *	@param		?string		$formName
	 *	@return		string
	 */
	public static function renderStatic( Environment $env, ?string $formName = NULL ): string
	{
		$helper	= new self( $env );
		$helper->setFormName( $formName );
		return $helper->render();
	}

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@todo		remove formName after other modules have been updated
	 */
	public function render( ?string $formName = NULL ): string
	{
		$formName	= $formName ?: $this->formName;
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

	/**
	 *	@param		string		$formName
	 *	@return		self
	 */
	public function setFormName( string $formName ): self
	{
		$this->formName	= $formName;
		return $this;
	}
}
