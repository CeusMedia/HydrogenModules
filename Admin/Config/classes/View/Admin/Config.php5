<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Config extends View
{
	public function edit()
	{
	}

	public function direct()
	{
	}

	public function index()
	{
	}

	public function module()
	{
	}

	public function renderConfigInput( string $moduleId, object $item ): string
	{
		$isNumeric		= in_array( $item->type, array( "integer", "float" ) ) || preg_match( "/^[0-9\.]+$/", $item->value );
		if( $item->values ){
			$values		= array_combine( $item->values, $item->values );
			$options	= HtmlElements::Options( $values, $item->value );
			$class		= $isNumeric ? "span3" : "span6";
			$input		= HtmlTag::create( 'select', $options, array(
				'name'	=> $moduleId.'|'.$item->key,
				'class'	=> $class,
			) );
		}
		else if( $item->type === "boolean" ){
			$inputYes	= HtmlTag::create( 'input', NULL, array(
				'name'		=> $moduleId.'|'.$item->key,
				'type'		=> 'radio',
				'value'		=> 'yes',
				'checked'	=> !!$item->value ? 'checked' : NULL,
			) ).'&nbsp;yes';
			$inputNo	= HtmlTag::create( 'input', NULL, array(
				'name'		=> $moduleId.'|'.$item->key,
				'type'		=> 'radio',
				'value'		=> 'no',
				'checked'	=> !$item->value ? 'checked' : NULL,
			) ).'&nbsp;no';
			$inputYes		= HtmlTag::create( 'label', $inputYes, array( 'class' => 'checkbox inline' ) );
			$inputNo		= HtmlTag::create( 'label', $inputNo, array( 'class' => 'checkbox inline' ) );
			$input			= $inputYes.$inputNo;
		}
		else{
			$class			= $isNumeric || ( strlen( $item->value ) < 10 && strlen( $item->title ) < 10 ) ? "span3" : "span12";
			$item->value	= preg_match( '/password/', $item->key ) ? '' : $item->value;
			if( preg_match( "/,/", $item->value ) ){
				$value		= str_replace( ",", "\n", htmlentities( $item->value, ENT_QUOTES, 'UTF-8' ) );
				$input		= HtmlTag::create( 'textarea', $value, array(
					'name'			=> $moduleId.'|'.$item->key,
					'multiple'		=> 'multiple',
					'class'			=> $class,
					'rows'			=> count( explode( ",", $item->value ) ),
				) );
			}
			else{
				$input		= HtmlTag::create( 'input', NULL, array(
					'type'			=> 'text',
					'name'			=> $moduleId.'|'.$item->key,
					'class'			=> $class,
					'value'			=> htmlentities( $item->value, ENT_QUOTES, 'UTF-8' ),
					'placeholder'	=> $item->title,
				) );
			}
		}
		return $input;
	}

	public function view()
	{
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'module.admin.config.css' );
	}
}
