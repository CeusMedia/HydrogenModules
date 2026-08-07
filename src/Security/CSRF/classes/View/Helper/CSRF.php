<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_CSRF
{
	protected Environment $env;
	protected ?string $formName		= NULL;
	protected ?bool $useHint		= NULL;
	protected ?bool $useRefresh		= NULL;

	/**
	 *	@param		Environment	$env
	 *	@param		string		$formName
	 *	@param		bool|NULL	$useRefresh		Flag: also render JavaScript to reload after token duration
	 *	@param		bool|NULL	$useHint		Flag: show hint if not refreshing
	 *	@return		string
	 */
	public static function renderStatic( Environment $env, string $formName, ?bool $useRefresh = NULL, ?bool $useHint = NULL ): string
	{
		$helper	= new self( $env );
		$helper->setFormName( $formName );
		if( NULL !== $useRefresh )
			$helper->setUseRefresh( $useRefresh );
		if( NULL !== $useHint )
			$helper->setUseHint( $useHint );
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
		if( !$this->env->getModules()->has( 'Security_CSRF' ) )
			return '';
		$formName	= $formName ?: $this->formName;
		if( !$formName )
			throw new RuntimeException( 'No form name set' );
//		$token	= $this->env->getLogic()->get( 'CSRF' )->getToken( $formName );
		$logic	= Logic_CSRF::getInstance( $this->env );
		$token	= $logic->getToken( $formName );
		$input1	= HtmlTag::create( 'input', NULL, [
			'type'	=> 'hidden',
			'name'	=> 'csrf_token',
			'value'	=> $token,
			'data-invocation'	=> self::class,
			'data-form'			=> $formName,
		] );
		$input2	= HtmlTag::create( 'input', NULL, [
			'type'	=> 'hidden',
			'name'	=> 'csrf_form_name',
			'value'	=> $formName
		] );
		$moduleConfig	= $this->env->getModules()->get( 'Security_CSRF' )->getConfigAsDictionary()->getAll( '', TRUE );
		$useHint		= TRUE === $this->useHint || ( NULL === $this->useHint && $moduleConfig->get( 'auto.hint' ) );
		$useRefresh		= TRUE === $this->useRefresh || ( NULL === $this->useRefresh && $moduleConfig->get( 'auto.refresh' ) );
		$useLifetime	= 0 !== $logic->getTokenDuration();
		$hasDatetimeHelper	= $this->env->getModules()->has( 'UI_Helper_Datetime' );

		$hint		= '';
		$refresh	= '';
		if( $useLifetime && $useRefresh ){
			$script	= sprintf( 'window.setTimeout(function(){window.location.reload()}, %d)', $logic->getTokenDuration() * 1000 );
			$this->env->getPage()->js->addScriptOnReady( $script );
		}
		else if( $useHint && $useLifetime ){
			$message	= '';
			if( $hasDatetimeHelper ){
				$helperTime		= new View_Helper_Datetime( $this->env );
				$tokenLifetime	= $helperTime->getDurationPhraseFromSeconds( $logic->getTokenDuration() );
				$message		= sprintf( 'Dieses gesicherte Formular ist nur %s gültig.', $tokenLifetime );
			}
			else{
				$tokenLifetime	= floor( $logic->getTokenDuration() / 60 );
				$message	= sprintf( 'Dieses gesicherte Formular ist nur %d Minuten gültig.', $tokenLifetime );
			}
			if( '' !== $message )
				$hint	= HtmlTag::create( 'div', $message, ['class' => 'alert alert-info'] );

		}
		return $input1.$input2.$hint.$refresh;
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

	/**
	 *	@param		bool		$switch
	 *	@return		self
	 */
	public function setUseHint( bool $switch ): self
	{
		$this->useHint	= $switch;
		return $this;
	}

	/**
	 *	@param		bool		$switch
	 *	@return		self
	 */
	public function setUseRefresh( bool $switch ): self
	{
		$this->useRefresh	= $switch;
		return $this;
	}
}
