<?php
class View_Helper_Module extends CMF_Hydrogen_View_Helper_Abstract{

	public function __construct( $env ){
		$this->setEnv( $env );
		$this->logic	= Logic_Module::getInstance( $env );
		$this->modules	= array();
		foreach( $this->logic->model->getAll() as $module )
			$this->modules[$module->id]	= $module;
	}
	
	public function renderModuleLink( $moduleId, $status = 0 ){
		$title	= $moduleId;
		if( array_key_exists( $moduleId, $this->modules ) ){
			$module	= $this->modules[$moduleId];
			$title	= htmlspecialchars( $module->title, ENT_QUOTES, 'UTF-8' );
		}
		$url		= './admin/module/viewer/'.$moduleId;
		$link		= UI_HTML_Tag::create( 'a', $title, array( 'href' => $url ) );
		$span		= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'icon module module-status-'.$status ) );
		return $span;
	}

	static public function renderModuleConfigInput( $item, $words, $readonly = FALSE ){
		if( !$item )
			return "";
		$class	= "";
		if( $item->mandatory ){
			if( $item->mandatory == "yes" )
				$class = " mandatory";
			else if( preg_match( "/^.+:.*$/", $item->mandatory ) ){
				list( $relatedKey, $relatedValue )	= explode( ':', $item->mandatory );
				$relatedValue	= explode( ',', $relatedValue );
				if( isset( $moduleSource->config[$relatedKey] ) ){
					if( in_array( $moduleSource->config[$relatedKey]->value, $relatedValue ) )
						$class = " mandatory";
				}
			}
		}
		$name	= 'config['.$item->key.']';
		switch( $item->type ){
			case 'boolean':
				$strValue	= $item->value === TRUE ? 'yes' : 'no';
				$options	= UI_HTML_Elements::Options( $words, $strValue );
				$attributes	= array(
					'class'		=> 's'.$class.' active-'.$strValue,
					'name'		=> $name,
					'id'		=> 'input_'.$name,
					'readonly'	=> $readonly ? 'readonly' : NULL
				);
				$input		= UI_HTML_Tag::create( 'select', $options, $attributes );
				break;
			case 'int':
			case 'integer':
			case 'float':
			case 'double':
			case 'real':
				$input	= UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'config['.$item->key.']',
					'id'		=> 'config['.$item->key.']',
					'value'		=> $item->value,
					'data-init'	=> $item->value,
					'class'		=> 's'.$class,
					'readonly'	=> $readonly ? 'readonly' : NULL
				) );
				break;
			default:
				$input	= UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'config['.$item->key.']',
					'id'		=> 'config['.$item->key.']',
					'value'		=> $item->value,
					'data-init'	=> $item->value,
					'class'		=> 'max'.$class,
					'readonly'	=> $readonly ? 'readonly' : NULL
				) );
				if( count( $item->values ) ){
					$options	= array_combine( $item->values, $item->values );
					$options	= UI_HTML_Elements::Options( $options, $item->value );
					$input	= UI_HTML_Tag::create( 'select', $options, array(
						'name'		=> 'config['.$item->key.']',
						'id'		=> 'config['.$item->key.']',
						'data-init'	=> addslashes( $item->value ),
						'value'		=> addslashes( $item->value ),
						'class'		=> 'm'.$class,
						'readonly'	=> $readonly ? 'readonly' : NULL
					) );
				}
				break;
		}
		return $input;
	}
}
?>