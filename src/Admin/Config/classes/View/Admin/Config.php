<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Config extends View
{
	public function edit(): void
	{
	}

	public function direct(): void
	{
	}

	public function index(): void
	{
	}

	public function module(): void
	{
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		object		$item
	 *	@return		string
	 */
	public function renderConfigInput( string $moduleId, object $item ): string
	{
		$isNumeric	= in_array( $item->type, ['integer', 'float'] ) || preg_match( "/^[0-9\.]+$/", $item->value );
		if( $item->values )
			$input	= $this->renderSelectInput( $moduleId, $item, $isNumeric );
		else if( 'boolean' === $item->type )
			$input	= $this->renderBooleanInput( $moduleId, $item );
		else
			$input	= $this->renderTextInput( $moduleId, $item, $isNumeric );
		return $input;
	}

	public function view(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.admin.config.css' );
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		object		$item
	 *	@return		string
	 */
	protected function renderBooleanInput( string $moduleId, object $item ): string
	{
		$inputYes	= HtmlTag::create( 'input', NULL, [
				'name'		=> $moduleId.'|'.$item->key,
				'type'		=> 'radio',
				'value'		=> 'yes',
				'checked'	=> !!$item->value ? 'checked' : NULL,
			] ).'&nbsp;yes';
		$inputNo	= HtmlTag::create( 'input', NULL, [
				'name'		=> $moduleId.'|'.$item->key,
				'type'		=> 'radio',
				'value'		=> 'no',
				'checked'	=> !$item->value ? 'checked' : NULL,
			] ).'&nbsp;no';
		$inputYes		= HtmlTag::create( 'label', $inputYes, ['class' => 'checkbox inline'] );
		$inputNo		= HtmlTag::create( 'label', $inputNo, ['class' => 'checkbox inline'] );
		return $inputYes.$inputNo;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		object		$item
	 *	@param		bool		$isNumeric
	 *	@return		string
	 */
	protected function renderSelectInput( string $moduleId, object $item, bool $isNumeric ): string
	{
		$values		= array_combine( $item->values, $item->values );
		$options	= HtmlElements::Options( $values, $item->value );
		return HtmlTag::create( 'select', $options, [
			'name'	=> $moduleId.'|'.$item->key,
			'class'	=> $isNumeric ? 'span3' : 'span6',
		] );
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		object		$item
	 *	@param		bool		$isNumeric
	 *	@return		string
	 */
	protected function renderTextInput( string $moduleId, object $item, bool $isNumeric ): string
	{
		$class			= $isNumeric || ( strlen( $item->value ) < 10 && strlen( $item->title ) < 10 ) ? 'span3' : 'span12';
		$item->value	= str_contains( $item->key, 'password') ? '' : $item->value;
		if( str_contains( $item->value, ',' ) ){
			$value		= str_replace( ',', "\n", htmlentities( $item->value, ENT_QUOTES, 'UTF-8' ) );
			$input		= HtmlTag::create( 'textarea', $value, [
				'name'			=> $moduleId.'|'.$item->key,
				'multiple'		=> 'multiple',
				'class'			=> $class,
				'rows'			=> count( explode( ",", $item->value ) ),
			] );
		}
		else{
			$input		= HtmlTag::create( 'input', NULL, [
				'type'			=> 'text',
				'name'			=> $moduleId.'|'.$item->key,
				'class'			=> $class,
				'value'			=> htmlentities( $item->value, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> $item->title,
			] );
		}
		return $input;
	}
}
