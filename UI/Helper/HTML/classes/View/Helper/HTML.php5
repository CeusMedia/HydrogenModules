<?php
class View_Helper_HTML{
//	public function __construct();
}

class HTML/* extends UI_HTML_Elements*/ {

	const BR = '<br/>';
	const HR = '<hr/>';

	static $prefixIdInput	= 'input_';
	static $prefixIdForm	= 'form_';

	static public function Button( $name, $label, $class ){
		$attributes['type']		= 'submit';
		$attributes['name']		= $name;
		$attributes['class']	= $class;
		return UI_HTML_Tag::create( 'button', $label, $attributes );
	}

	static public function Buttons( $content ){
		return HTML::DivClass( 'buttonbar',
			$content.
			HTML::DivClass( 'column-clear' )
		);
	}

	static public function Checkbox( $name, $value, $checked = FALSE, $class = NULL, $readonly = NULL ){
		$attributes	= array(
			'type'		=> 'checkbox',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'value'		=> htmlspecialchars( $value, ENT_COMPAT, 'UTF-8' ),
			'class'		=> $class,
			'checked'	=> $checked ? 'checked' : NULL,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'input', NULL, $attributes );
	}

	static public function Def( $term, $definitions ){
		if( !is_array( $definitions ) )
			$definitions	= array( $definitions );
		foreach( $definitions as $nr => $definition )
			$definitions[$nr]	= UI_HTML_Tag::create( 'dd', $definition );
		$definitions	= join( $definitions );

		return UI_HTML_Tag::create( 'dt', $term ).$definitions;
	}

	static public function DivClass( $class, $content = '', $attributes = array() ){
		return UI_HTML_Tag::create( 'div', $content, array( 'class' => $class ) );
	}

	static public function DivID( $id, $content, $attributes = array() ){
		return UI_HTML_Tag::create( 'div', $content, array( 'id' => $id ) );
	}

	static public function Dl( $definitions ){
		if( is_array( $definitions ) )
			$definitions	= join( $definitions );
		return UI_HTML_Tag::create( 'dl', $definitions );
	}

	static public function Fields( $content, $class = NULL ){
		return UI_HTML_Tag::create( 'fieldset', $content, array( 'class' => $class ) );
	}

	static public function File( $name, $class = NULL, $readonly = NULL ){
		$attributes	= array(
			'type'		=> 'file',
			'name'		=> $name,
			'id'		=> self::$prefixIdInput.$name,
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'input', NULL, $attributes );
	}

	static public function Form( $url, $name, $content ){
		$enctype	= NULL;
		if( substr_count( $content, ' type="file"' ) )
			$enctype	= 'multipart/form-data';
		$attributes	= array(
			'name'		=> $name,
			'action'	=> $url,
			'id'		=> $name === NULL ? NULL : self::$prefixIdForm.$name,
			'method'	=> "post",
			'enctype'	=> $enctype,
		);
		return UI_HTML_Tag::create( 'form', $content, $attributes );
	}

	static public function H2( $label, $class = NULL ){
		return self::Heading( 2, $label, $class );
	}

	static public function H3( $label, $class = NULL ){
		return self::Heading( 3, $label, $class );
	}

	static public function H4( $label, $class = NULL ){
		return self::Heading( 4, $label, $class );
	}

	static public function Heading( $level, $label, $class = NULL ){
		return UI_HTML_Tag::create( 'h'.$level, htmlentities( $label, ENT_COMPAT, 'UTF-8' ), array( 'class' => $class ) );
	}

	static public function Icon( $key, $white = NULL ){
		$class		= "icon-".$key.( $white ? " icon-white" : "" );
		return UI_HTML_Tag::create( 'i', '', array( 'class' => $class ) );
	}

	static public function Image( $source, $title, $class = NULL, $attributes = array() ){
		$attributes['class']	= htmlentities( $class, ENT_QUOTES, 'UTF-8' );
		$attributes['src']		= htmlentities( $source, ENT_QUOTES, 'UTF-8' );
		$attributes['alt']		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
		return UI_HTML_Tag::create( 'img', NULL, $attributes );
	}

	static public function Input( $name, $value, $class = NULL, $readonly = NULL ){
		$attributes	= array(
			'type'		=> 'text',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'value'		=> htmlspecialchars( $value, ENT_COMPAT, 'UTF-8' ),
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		if( preg_match( "/(mandatory|required)/", $class ) )
			$attributes['required']	= 'required';
		return UI_HTML_Tag::create( 'input', NULL, $attributes );
	}

	static public function Label( $inputName = NULL, $content, $class = NULL ){
		$attributes	= array(
			'for'		=> $inputName === NULL ? NULL : self::$prefixIdInput.$inputName,
			'class'		=> $class,
		);
		return UI_HTML_Tag::create( 'label', $content, $attributes );
	}

	static public function Legend( $content, $class = NULL ){
		return UI_HTML_Tag::create( 'legend', $content, array( 'class' => $class ) );
	}

	static public function Li( $content, $class = NULL ){
		return UI_HTML_Tag::create( 'li', $content, array( 'class' => $class ) );
	}

	static public function LiClass( $class, $content ){
		return HTML::Li( $content, $class );
	}

	static public function Link( $href, $label, $class = NULL ){
		$attributes['href']		= htmlentities( $href, ENT_QUOTES, 'UTF-8' );
		$attributes['class']	= htmlentities( $class, ENT_QUOTES, 'UTF-8' );
		return UI_HTML_Tag::create( 'a', $label, $attributes );
	}

	static public function LinkButton( $href, $label, $class = NULL ){
		$attributes['onclick']	= 'document.location.href=\''.$href.'\'';
		$attributes['class']	= $class;
		$attributes['type']		= 'button';
		return UI_HTML_Tag::create( 'button', $label, $attributes );
	}

	static function Options( $items, $selected = NULL, $keys = array() ){
		if( !count( $items ) )
			return '';
		$values	= array_values( $items );
		$first	= array_shift( $values );
		if( is_object( $first ) && count( $keys ) === 2 ){
			$list	= array();
			foreach( $items as $item ){
				$key	= $item->{$keys[0]};
				$label	= $item->{$keys[1]};
				$list[$key]	= $label;
			}
			$items	= $list;
		}
		return UI_HTML_Elements::Options( $items, $selected );
	}

	static public function Password( $name, $class = NULL, $readonly = NULL ){
		$attributes		= array(
			'type'		=> 'password',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'input', NULL, $attributes );
	}

	static public function Select( $name, $options, $class = NULL, $readonly = NULL, $onChange = NULL ){
		if( is_array( $options ) ){
			$selected	= isset( $options['_selected'] ) ? $options['_selected'] : NULL;
			$options	= self::Options( $options, $selected );
		}
		if( preg_match( '/^[a-z0-9_-]+$/i', $onChange ) )
			$onChange	= "document.getElementById('".self::$prefixIdForm.$onChange."').submit();";
		$attributes	= array(
			'id'		=> str_replace( "[]", "", self::$prefixIdInput.$name ),
			'name'		=> $name,
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
			'multiple'	=> substr( trim( $name ), -2 ) == "[]"	? "multiple" : NULL,
			'onchange'	=> $onChange,
		);
		if( $readonly )
			self::addReadonlyAttributes( $attributes, $readonly );
		return UI_HTML_Tag::create( "select", $options, $attributes );
	}

	static public function SpanClass( $class, $content = '', $attributes = array() ){
		return UI_HTML_Tag::create( 'span', $content, array( 'class' => $class ) );
	}

	static public function Tag( $nodeName, $content, $attributes ){
		return new UI_HTML_Tag( $nodeName, $content, $attributes );
	}

	static public function Text( $name, $content, $class = NULL, $numberRows = NULL, $readonly = NULL ){
		$content	= htmlspecialchars( $content, ENT_COMPAT, 'UTF-8' );
		$attributes	= array(
			'name'		=> $name,
			'id'		=> self::$prefixIdInput.$name,
			'class'		=> $class,
			'rows'		=> $numberRows,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return UI_HTML_Tag::create( 'textarea', $content, $attributes );
	}

	static public function UlClass( $class, $content ){
		return UI_HTML_Tag::create( 'ul', $content, array( 'class' => $class ) );
	}
}
?>
