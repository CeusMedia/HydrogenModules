<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Module extends Abstraction
{
	public function __construct( Web $env ){
		$this->setEnv( $env );
		$this->logic	= Logic_Module::getInstance( $env );
		$this->modules	= [];
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
		$link		= HtmlTag::create( 'a', $title, ['href' => $url] );
		$span		= HtmlTag::create( 'span', $link, ['class' => 'icon module module-status-'.$status] );
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
				$options	= HtmlElements::Options( $words, $strValue );
				$attributes	= [
					'class'		=> 's'.$class.' active-'.$strValue,
					'name'		=> $name,
					'id'		=> 'input_'.$name,
					'readonly'	=> $readonly ? 'readonly' : NULL
				];
				$input		= HtmlTag::create( 'select', $options, $attributes );
				break;
			case 'int':
			case 'integer':
			case 'float':
			case 'double':
			case 'real':
				$input	= HtmlTag::create( 'input', NULL, [
					'type'		=> 'text',
					'name'		=> 'config['.$item->key.']',
					'id'		=> 'config['.$item->key.']',
					'value'		=> $item->value,
					'data-init'	=> $item->value,
					'class'		=> 's'.$class,
					'readonly'	=> $readonly ? 'readonly' : NULL
				] );
				break;
			default:
				$input	= HtmlTag::create( 'input', NULL, [
					'type'		=> 'text',
					'name'		=> 'config['.$item->key.']',
					'id'		=> 'config['.$item->key.']',
					'value'		=> $item->value,
					'data-init'	=> $item->value,
					'class'		=> 'max'.$class,
					'readonly'	=> $readonly ? 'readonly' : NULL
				] );
				if( count( $item->values ) ){
					$options	= array_combine( $item->values, $item->values );
					$options	= HtmlElements::Options( $options, $item->value );
					$input	= HtmlTag::create( 'select', $options, array(
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

	static public function renderModuleConfigLabel( $module, $item ){
		$class		= "";
		$name		= 'config['.$item->key.']';
		if( $item->mandatory ){
			if( $item->mandatory == "yes" )
				$class = " mandatory";
			else if( preg_match( "/^.+:.*$/", $item->mandatory ) ){
				list( $relatedKey, $relatedValue )	= explode( ':', $item->mandatory );
				$relatedValue	= explode( ',', $relatedValue );
				if( isset( $module->config[$relatedKey] ) ){
					if( in_array( $module->config[$relatedKey]->value, $relatedValue ) )
						$class = " mandatory";
				}
			}
		}
		$label		= $item->key;
		if( strlen( trim( $title = htmlentities( $item->title, ENT_QUOTES, 'UTF-8' ) ) ) )
			$label	= HtmlTag::create( 'acronym', $item->key, ['title' => $title] );
		$attributes	= ['class' => $class, 'for' => 'input_'.$name];
		$label		= HtmlTag::create( 'label', $label, $attributes );
		return $label;
	}
}
?>