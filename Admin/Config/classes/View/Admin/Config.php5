<?php
class View_Admin_Config extends CMF_Hydrogen_View {
	public function index(){}
	public function module(){}

	public function renderConfigInput( $moduleId, $item ){
		$isNumeric		= in_array( $item->type, array( "integer", "float" ) ) || preg_match( "/^[0-9\.]+$/", $item->value );
		if( $item->values ){
			$values		= array_combine( $item->values, $item->values );
			$options	= UI_HTML_Elements::Options( $values, $item->value );
			$class		= $isNumeric ? "span3" : "span12";
				$input		= new UI_HTML_Tag( 'select', $options, array(
				'name'	=> $moduleId.'|'.$item->key,
				'class'	=> $class,
			) );
		}
		else if( $item->type === "boolean" ){
			$inputYes	= UI_HTML_Tag::create( 'input', NULL, array(
				'name'		=> $moduleId.'|'.$item->key,
				'type'		=> 'radio',
				'value'		=> 'yes',
				'checked'	=> !!$item->value ? 'checked' : NULL,
			) ).'&nbsp;yes';
			$inputNo	= UI_HTML_Tag::create( 'input', NULL, array(
				'name'		=> $moduleId.'|'.$item->key,
				'type'		=> 'radio',
				'value'		=> 'no',
				'checked'	=> !$item->value ? 'checked' : NULL,
			) ).'&nbsp;no';
			$inputYes		= UI_HTML_Tag::create( 'label', $inputYes, array( 'class' => 'checkbox inline' ) );
			$inputNo		= UI_HTML_Tag::create( 'label', $inputNo, array( 'class' => 'checkbox inline' ) );
			$input			= $inputYes.$inputNo;
		}
		else{
			if( preg_match( "/,/", $item->value ) ){
				$value		= str_replace( ",", "\n", htmlentities( $item->value, ENT_QUOTES, 'UTF-8' ) );
				$input		= new UI_HTML_Tag( 'textarea', $value, array(
					'name'			=> $moduleId.'|'.$item->key,
					'multiple'		=> 'multiple',
					'class'			=> 'span6',
					'rows'			=> count( explode( ",", $item->value ) ),
				) );
			}
			else{
				$class		= $isNumeric ? "span3" : "span12";
				$input		= new UI_HTML_Tag( 'input', NULL, array(
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
}
