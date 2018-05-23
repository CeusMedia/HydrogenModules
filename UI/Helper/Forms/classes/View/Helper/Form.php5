<?php
class View_Helper_Form/* extends CMF_Hydrogen_View_Helper*/{

	protected $emv;
	protected $blocks				= array();
	protected $form;
	protected $formId;
	protected $modelBlock;
	protected $modelForm;
	protected $returnCode;

	public function __construct( $env ){
		$this->env	= $env;
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelBlock	= new Model_Form_Block( $this->env );
		foreach( $this->modelBlock->getAll() as $item )
			$this->blocks[$item->identifier]	= $item;
		$this->returnCode	= (int) $this->env->getRequest()->get( 'rc' );
	}

	public function renderStatic( $env, $formId ){
		$helper	= new View_Helper_Form( $env );
		return $helper->setId( $formId )->render();
	}

	public function setId( $formId ){
		$form	= $this->modelForm->get( $formId );
		if( !$form )
			throw new RangeException( 'Invalid form ID given: '.$formId );
		$this->form		= $form;
		$this->formId	= $formId;
		return $this;
	}

	public function render(){
		$resultMessages		= $this->renderResultMessages();
		$content			= $resultMessages;
		if( !$this->returnCode )
			$content		.= $this->renderForm();
		while( preg_match( '/\[block_(\S+)\]/su', $content ) ){
			$identifier		= preg_replace( '/.*\[block_(\S+)\].*/su', "\\1", $content );
			$replace		= isset( $this->blocks[$identifier] ) ? $this->blocks[$identifier]->content : '';
			$pattern		= '/'.preg_quote( '[block_'.$identifier.']' ).'/su';
			$content		= preg_replace( $pattern, $replace, $content, 1 );
		}
		if( !$this->returnCode ){
			$form		= $this->modelForm->get( $this->formId );
			$clientUrl	= $this->env->getConfig()->get( 'app.base.url' );
			$devMode	= $form->status < Model_Form::STATUS_ACTIVATED ? 'true' : 'false';
			$scripts	= join( '', array(
				UI_HTML_Tag::create( 'script', 'jQuery(document).ready(function(){Forms.init("'.$clientUrl.'", '.$devMode.').apply("form-'.$this->formId.'");});' ),
	//			UI_HTML_Tag::create( 'script', 'FormOptionals.init();' ),
			) );
			$content	.= $scripts;
		}
		return $content;
	}

	protected function renderForm(){
		$form		= $this->modelForm->get( $this->formId );
		$button		= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', 'abschicken', array( 'type' => 'submit', 'name' => 'send', 'class' => 'cmsmasters_button' ) ),
		), array( 'class' => 'cmforms-row' ) );
		if( substr_count( $form->content, '[block_row_button]' ) )
			$button	= '';
		return UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'input', NULL, array( 'type' => 'hidden', 'name' => 'formId', 'value' => $this->formId ) ),
			$form->content,
			$button,
		), array(
			'id'		=> 'form-'.$this->formId,
			'action' 	=> '#',//'./?action=fill&id='.$this->formId,
			'method' 	=> 'post',
			'class'		=> 'cmforms',
			'onsubmit'	=> 'Forms.sendForm(this); return false;',
		) );
	}

	protected function renderResultMessages(){
		$messageCode	= '';
		$messageError	= '';
		$messageSuccess	= '';
		$blocks			= ADT_List_Dictionary::create( $this->blocks )->getAll( 'message_' );
		if( $this->returnCode === 2 && isset( $blocks['result_confirmed'] ) )
			$messageCode	=  UI_HTML_Tag::create( 'div', $blocks['result_confirmed']->content, array(
				'class'	=> 'form-message-code',
			) );
		else if( $this->returnCode === 3 && isset( $blocks['result_confirmed_already'] ) )
			$messageCode	=  UI_HTML_Tag::create( 'div', $blocks['result_confirmed_already']->content, array(
				'class'	=> 'form-message-code',
			) );
		if( isset( $blocks['error'] ) ){
			$messageError	= UI_HTML_Tag::create( 'div', $blocks['error']->content, array(
				'class'	=> 'form-message-error',
				'style'	=> 'display: none',
			) );
		}
		if( isset( $blocks['success'] ) ){
			$messageSuccess	= UI_HTML_Tag::create( 'div', $blocks['success']->content, array(
				'class'	=> 'form-message-success',
				'style'	=> 'display: none',
			) );
		}
		$form		= $this->modelForm->get( $this->formId );
		if( $form->type == Model_Form::TYPE_CONFIRM ){
			if( isset( $blocks['success_confirm'] ) ){
				$messageSuccess	= UI_HTML_Tag::create( 'div', $blocks['success_confirm']->content, array(
					'class'	=> 'form-message-success',
					'style'	=> 'display: none',
				) );
			}
		}
		return join( '', array(
			$messageCode,
			$messageError,
			$messageSuccess,
		) );
	}
}