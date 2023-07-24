<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Form extends Abstraction
{
	protected ?Environment $env;
	protected array $blocks						= [];
	protected ?object $form						= NULL;
	protected ?string $formId					= NULL;
	protected Model_Form_Block $modelBlock;
	protected Model_Form $modelForm;
	protected int $returnCode;
	protected ?string $mode						= NULL;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelBlock	= new Model_Form_Block( $this->env );
		foreach( $this->modelBlock->getAll() as $item )
			$this->blocks[$item->identifier]	= $item;
		$this->returnCode	= (int) $this->env->getRequest()->get( 'rc' );
	}

	public function render(): string
	{
		$resultMessages	= $this->renderResultMessages();
		$content		= $resultMessages;
		if( !$this->returnCode )
			$content	.= $this->renderForm();
		$counter		= 0;
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
		$pattern	= '/'.preg_quote( '[helper_captcha]', '/' ).'/';
		if( preg_match( $pattern, $content ) ){
			$helper			= new View_Helper_Captcha( $this->env );
			$replacement	= $helper->render();
			$content		= preg_replace( $pattern, $replacement, $content );
		}
		if( !$this->returnCode ){
			$form		= $this->modelForm->get( $this->formId );
			$clientUrl	= $this->env->getConfig()->get( 'app.base.url' );
			$devMode	= $form->status < Model_Form::STATUS_ACTIVATED ? 'true' : 'false';
			$scripts	= join( '', array(
				HtmlTag::create( 'script', 'jQuery(document).ready(function(){Forms.init("'.$clientUrl.'", '.$devMode.').apply("form-'.$this->formId.'");});' ),
	//			HtmlTag::create( 'script', 'FormOptionals.init();' ),
			) );
			$content	.= $scripts;
		}
		return $content;
	}

	public static function renderStatic( Environment $env, string $formId ): string
	{
		$helper	= new View_Helper_Form( $env );
		return $helper->setId( $formId )->render();
	}

	public function setId( string $formId ): self
	{
		$form	= $this->modelForm->get( $formId );
		if( !$form )
			throw new RangeException( 'Invalid form ID given: '.$formId );
		$this->form		= $form;
		$this->formId	= $formId;
		return $this;
	}

	public function setMode( ?string $mode ): self
	{
		if( in_array( $mode, [NULL, '', 'extended'] ) ){
			$this->mode	= (string) $mode;
		}
		return $this;
	}

	protected function renderForm(): string
	{
		$form		= $this->modelForm->get( $this->formId );
		$button		= HtmlTag::create( 'div', array(
			HtmlTag::create( 'button', 'abschicken', ['type' => 'submit', 'name' => 'send', 'class' => 'cmsmasters_button btn btn-primary'] ),
		), ['class' => 'cmforms-row'] );
		if( substr_count( $form->content, '[block_row_button]' ) )
			$button	= '';
		return HtmlTag::create( 'form', array(
			HtmlTag::create( 'input', NULL, [
				'type'		=> 'hidden',
				'name'		=> 'formId',
				'id'		=> 'input_formId',
				'value'		=> $this->formId,
			] ),
			$form->content,
			$button,
		), array(
			'id'			=> 'form-'.$this->formId,
			'data-id'		=> $this->formId,
			'action' 		=> '#',//'./?action=fill&id='.$this->formId,
			'method' 		=> 'post',
			'class'			=> 'cmforms',
			'onsubmit'		=> 'Forms.sendForm(this); return false;',
		) );
	}

	protected function renderResultMessages(): string
	{
		$messageCode	= '';
		$messageError	= '';
		$messageSuccess	= '';
		$blocks			= Dictionary::create( $this->blocks )->getAll( 'message_' );
		if( $this->returnCode === 2 && isset( $blocks['result_confirmed'] ) )
			$messageCode	=  HtmlTag::create( 'div', $blocks['result_confirmed']->content, [
				'class'	=> 'form-message-code',
			] );
		else if( $this->returnCode === 3 && isset( $blocks['result_confirmed_already'] ) )
			$messageCode	=  HtmlTag::create( 'div', $blocks['result_confirmed_already']->content, [
				'class'	=> 'form-message-code',
			] );
		if( isset( $blocks['error'] ) ){
			$messageError	= HtmlTag::create( 'div', $blocks['error']->content, [
				'class'	=> 'form-message-error',
				'style'	=> 'display: none',
			] );
		}
		if( isset( $blocks['success'] ) ){
			$messageSuccess	= HtmlTag::create( 'div', $blocks['success']->content, [
				'class'	=> 'form-message-success',
				'style'	=> 'display: none',
			] );
		}
		$form		= $this->modelForm->get( $this->formId );
		if( $form->type == Model_Form::TYPE_CONFIRM ){
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
