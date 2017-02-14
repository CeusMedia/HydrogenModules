<?php
class View_Admin_Config extends CMF_Hydrogen_View {

	protected function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.admin.config.css' );
	}

/*	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'admin/config' );						//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );										//  register index tab
//		$context->registerTab( 'module', $words->tabs['module'], 1 );										//  register module tab
//		$context->registerTab( 'direct', $words->tabs['direct'], 1 );										//  register direct tab
	}*/

	public function edit(){
	}

	public function direct(){
	}

	public function index(){
	}

	public function module(){
	}

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
				$class		= $isNumeric || strlen( $item->value ) < 10  ? "span3" : "span12";
				$input		= new UI_HTML_Tag( 'textarea', $value, array(
					'name'			=> $moduleId.'|'.$item->key,
					'multiple'		=> 'multiple',
					'class'			=> $class,
					'rows'			=> count( explode( ",", $item->value ) ),
				) );
			}
			else{
				$class		= $isNumeric || strlen( $item->value ) < 10  ? "span3" : "span12";
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
