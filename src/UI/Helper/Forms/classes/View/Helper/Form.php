<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Form extends Abstraction
{
	public const RETURN_CODE_NONE				= 0;
	public const RETURN_CODE_CONFIRMED			= 2;
	public const RETURN_CODE_ALREADY_CONFIRMED	= 3;


	protected ?Environment $env;
	protected array $blocks						= [];
	protected ?object $form						= NULL;
	protected int|string|NULL $formId			= NULL;
	protected Model_Form_Block $modelBlock;
	protected Model_Form $modelForm;
	protected int $returnCode;
	protected ?string $mode						= NULL;

	/**
	 *	@param		Environment		$env
	 *	@param		int|string		$formId
	 *	@param		string|NULL		$mode		Rendering mode: 'extended' or empty string
	 *	@param		int|NULL		$onReturnCode
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public static function renderStatic( Environment $env, int|string $formId, ?string $mode = NULL, ?int $onReturnCode = self::RETURN_CODE_NONE ): string
	{
		$helper	= new View_Helper_Form( $env );
		$helper->setId( $formId )->setMode( $mode );
		$helper->returnCode	= $onReturnCode;
		return $helper->render();
	}

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelBlock	= new Model_Form_Block( $this->env );
		foreach( $this->modelBlock->getAll() as $item )
			$this->blocks[$item->identifier]	= $item;
		$this->returnCode	= (int) $this->env->getRequest()->get( 'rc', self::RETURN_CODE_NONE );
	}

	/**
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function render(): string
	{
		$resultMessages	= $this->renderResultMessages();
		$content		= $resultMessages;

		if( self::RETURN_CODE_NONE === $this->returnCode ){
			$content	.= $this->renderForm();
			$form		= $this->modelForm->get( $this->formId );
			$clientUrl	= $this->env->getConfig()->get( 'app.base.url' );
			$devMode	= $form->status < Model_Form::STATUS_ACTIVATED ? 'true' : 'false';
			$script		= 'Forms.init("'.$clientUrl.'", '.$devMode.').apply("form-'.$this->formId.'");';
			$scripts	= join( '', [
				HtmlTag::create( 'script', 'jQuery(document).ready(function(){'.$script.'});' ),
	//			HtmlTag::create( 'script', 'FormOptionals.init();' ),
			] );
			$content	.= $scripts;
		}
		return $content;
	}

	/**
	 *	@param		int|string $formId
	 *	@return		self
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setId( int|string $formId ): self
	{
		$form	= $this->modelForm->get( $formId );
		if( !$form )
			throw new RangeException( 'Invalid form ID given: '.$formId );
		$this->form		= $form;
		$this->formId	= $formId;
		return $this;
	}

	/**
	 *	@param		string|NULL $mode
	 *	@return		self
	 */
	public function setMode( ?string $mode ): self
	{
		if( in_array( $mode, [NULL, '', 'extended'] ) ){
			$this->mode	= (string) $mode;
		}
		return $this;
	}

	/**
	 *	@param		string		$content
	 *	@return		string
	 */
	protected function injectCaptcha( string $content ): string
	{
		$pattern	= '/'.preg_quote( '[helper_captcha]', '/' ).'/';
		if( preg_match( $pattern, $content ) ){
			$helper			= new View_Helper_Captcha( $this->env );
			$replacement	= $helper->render();
			$content		= preg_replace( $pattern, $replacement, $content );
		}
		return $content;
	}

	/**
	 *	@param		string		$content
	 *	@return		string
	 */
	protected function injectFormBlocks( string $content ): string
	{
		$counter	= 0;
		while( preg_match( '/\[block_(\S+)\]/su', $content ) ){
			$counter++;
			$identifier		= preg_replace( '/.*\[block_(\S+)\].*/su', "\\1", $content );
			$replace		= isset( $this->blocks[$identifier] ) ? $this->blocks[$identifier]->content : '';
			$pattern		= '/'.preg_quote( '[block_'.$identifier.']' ).'/su';
			if( $this->mode === 'extended' && strlen( trim( $replace ) ) ){
				$replace	= HtmlTag::create( 'div', $replace, [
					'class'		=> 'form-view-block',
					'id'		=> 'form-view-block-'.$this->formId.'-'.$counter,
				], [
					'identifier'	=> $identifier,
					'block-id'		=> $this->blocks[$identifier]->blockId,
					'title'			=> $this->blocks[$identifier]->title,
				] );
			}
			$content		= preg_replace( $pattern, $replace, $content, 1 );
		}
		return $content;
	}

	/**
	 *	@param		bool		$injectBlocksAndCaptcha		Flag: also inject form blocks and captcha by shortcodes, default: yes
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderForm( bool $injectBlocksAndCaptcha = TRUE ): string
	{
		$form		= $this->modelForm->get( $this->formId );
		$button		= HtmlTag::create( 'div', [
			HtmlTag::create( 'button', 'abschicken', ['type' => 'submit', 'name' => 'send', 'class' => 'cmsmasters_button btn btn-primary'] ),
		], ['class' => 'cmforms-row'] );
		if( substr_count( $form->content, '[block_row_button]' ) )
			$button	= '';
		$content	= HtmlTag::create( 'form', [
			HtmlTag::create( 'input', NULL, [
				'type'		=> 'hidden',
				'name'		=> 'formId',
				'id'		=> 'input_formId',
				'value'		=> $this->formId,
			] ),
			$form->content,
			$button,
		], [
			'id'			=> 'form-'.$this->formId,
			'data-id'		=> $this->formId,
			'action' 		=> '#',//'./?action=fill&id='.$this->formId,
			'method' 		=> 'post',
			'class'			=> 'cmforms',
			'onsubmit'		=> 'Forms.sendForm(this); return false;',
		] );
		if( $injectBlocksAndCaptcha ){
			$content	= $this->injectFormBlocks( $content );
			$content	= $this->injectCaptcha( $content );
		}
		return $content;
	}

	protected function renderResultMessages(): string
	{
		$messageCode	= '';
		$messageError	= '';
		$messageSuccess	= '';
		$blocks			= Dictionary::create( $this->blocks )->getAll( 'message_' );
		if( self::RETURN_CODE_CONFIRMED === $this->returnCode && isset( $blocks['result_confirmed'] ) )
			$messageCode	=  HtmlTag::create( 'div', $blocks['result_confirmed']->content, [
				'class'		=> 'form-message-code',
			] );
		else if( self::RETURN_CODE_ALREADY_CONFIRMED === $this->returnCode && isset( $blocks['result_confirmed_already'] ) )
			$messageCode	=  HtmlTag::create( 'div', $blocks['result_confirmed_already']->content, [
				'class'		=> 'form-message-code',
			] );
		if( isset( $blocks['error'] ) ){
			$messageError	= HtmlTag::create( 'div', $blocks['error']->content, [
				'class'		=> 'form-message-error',
				'style'		=> 'display: none',
			] );
		}
		if( isset( $blocks['success'] ) ){
			$messageSuccess	= HtmlTag::create( 'div', $blocks['success']->content, [
				'class'		=> 'form-message-success',
				'style'		=> 'display: none',
			] );
		}
		$form		= $this->modelForm->get( $this->formId );
		if( Model_Form::TYPE_CONFIRM == $form->type ){
			if( isset( $blocks['success_confirm'] ) ){
				$messageSuccess	= HtmlTag::create( 'div', $blocks['success_confirm']->content, [
					'class'	=> 'form-message-success',
					'style'	=> 'display: none',
				] );
			}
		}
		return join( '', [
			$messageCode,
			$messageError,
			$messageSuccess,
		] );
	}
}
