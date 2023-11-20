<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_HTML
{
//	public function __construct();
}

class HTML/* extends \CeusMedia\Common\UI\HTML\Elements*/
{
	const BR = '<br/>';
	const HR = '<hr/>';

	public static string $prefixIdInput	= 'input_';
	public static string $prefixIdForm	= 'form_';

	public static function Abbr( string $label, string $title = NULL ): string
	{
		if( !strlen( trim( $title ) ) )
			$label;
		$attributes	= [
			'title'		=> $title
		];
		return self::Tag( 'abbr', $label, $attributes );
	}

	public static function Button( string $name, string $label, string $class ): string
	{
		$attributes['type']		= 'submit';
		$attributes['name']		= $name;
		$attributes['class']	= $class;
		return self::Tag( 'button', $label, $attributes );
	}

	public static function Buttons( $content ): string
	{
		$content	= is_array( $content ) ? join( $content ) : $content;
		return self::DivClass( 'buttonbar',
			$content.
			self::DivClass( 'column-clear' )
		);
	}

	public static function Checkbox( string $name, $value, bool $checked = FALSE, string $class = NULL, bool $readonly = NULL ): string
	{
		$attributes	= array(
			'type'		=> 'checkbox',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'value'		=> htmlspecialchars( $value, ENT_COMPAT, 'UTF-8' ),
			'class'		=> $class,
			'checked'	=> $checked ? 'checked' : NULL,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		return self::Tag( 'input', NULL, $attributes );
	}

	public static function Def( string $term, array $definitions ): string
	{
		foreach( $definitions as $nr => $definition )
			$definitions[$nr]	= HtmlTag::create( 'dd', $definition );
		return self::Tag( 'dt', $term ).join( $definitions );
	}

	public static function DivClass( string $class, $content = '', array $attributes = [] ): string
	{
		return self::Tag( 'div', $content, ['class' => $class] );
	}

	public static function DivID( string $id, $content, array $attributes = [] ): string
	{
		return self::Tag( 'div', $content, ['id' => $id] );
	}

	public static function Dl( array $definitions ): string
	{
		return self::Tag( 'dl', join( $definitions ) );
	}

	public static function Fields( $content, string $class = NULL ): string
	{
		return self::Tag( 'fieldset', $content, ['class' => $class] );
	}

	public static function File( string $name, string $class = NULL, bool $readonly = NULL ): string
	{
		$attributes	= [
			'type'		=> 'file',
			'name'		=> $name,
			'id'		=> self::$prefixIdInput.$name,
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		];
		return self::Tag( 'input', NULL, $attributes );
	}

	public static function Form( string $url, string $name, $content, array $attributes = [] ): string
	{
		$enctype	= NULL;
		if( is_array( $content ) )
			$content	= join( $content );
		if( substr_count( $content, ' type="file"' ) )
			$enctype	= 'multipart/form-data';
		$attributes		= array_merge( [
			'name'		=> $name,
			'action'	=> $url,
			'id'		=> $name === NULL ? NULL : self::$prefixIdForm.$name,
			'method'	=> "post",
			'enctype'	=> $enctype,
		], $attributes );
		return self::Tag( 'form', $content, $attributes );
	}

	public static function H2( string $label, string $class = NULL ): string
	{
		return self::Heading( 2, $label, $class );
	}

	public static function H3( string $label, string $class = NULL ): string
	{
		return self::Heading( 3, $label, $class );
	}

	public static function H4( string $label, string $class = NULL ): string
	{
		return self::Heading( 4, $label, $class );
	}

	public static function Heading( string $level, string $label, string $class = NULL ): string
	{
		return self::Tag( 'h'.$level, htmlentities( $label, ENT_COMPAT, 'UTF-8' ), ['class' => $class] );
	}

	public static function Icon( string $key, bool $white = NULL ): string
	{
		$class		= "icon-".$key.( $white ? " icon-white" : "" );
		return self::Tag( 'i', '', ['class' => $class] );
	}

	public static function Image( string $source, string $title, string $class = NULL, array $attributes = [] ): string
	{
		$attributes['class']	= htmlentities( $class, ENT_QUOTES, 'UTF-8' );
		$attributes['src']		= htmlentities( $source, ENT_QUOTES, 'UTF-8' );
		$attributes['alt']		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
		return self::Tag( 'img', NULL, $attributes );
	}

	public static function Input( string $name, $value, string $class = NULL, bool $readonly = NULL ): string
	{
		$attributes	= array(
			'type'		=> 'text',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'value'		=> htmlspecialchars( $value ?? '', ENT_COMPAT, 'UTF-8' ),
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		);
		if( preg_match( "/(mandatory|required)/", $class ) )
			$attributes['required']	= 'required';
		return self::Tag( 'input', NULL, $attributes );
	}

	public static function Label( string $inputName = NULL, $content, string $class = NULL, string $acronym = NULL, string $suffix = NULL ): string
	{
		if( $acronym )
			$content	= self::Tag( 'abbr', $content, ['title' => $acronym] );
		if( $suffix )
			$content	.= '&nbsp;'.self::Tag( 'small', '('.$suffix.')', ['class' => 'muted'] );
		$attributes	= [
			'for'		=> $inputName === NULL ? NULL : self::$prefixIdInput.$inputName,
			'class'		=> $class,
		];
		return self::Tag( 'label', $content, $attributes );
	}

	public static function Legend( $content, string $class = NULL ): string
	{
		return self::Tag( 'legend', $content, ['class' => $class] );
	}

	public static function Li( $content, string $class = NULL ){
		return self::Tag( 'li', $content, ['class' => $class] );
	}

	public static function LiClass( string $class, $content ): string
	{
		return self::Li( $content, $class );
	}

	public static function Link( string $href, string $label, string $class = NULL ): string
	{
		$attributes['href']		= htmlentities( $href, ENT_QUOTES, 'UTF-8' );
		$attributes['class']	= htmlentities( $class, ENT_QUOTES, 'UTF-8' );
		return self::Tag( 'a', $label, $attributes );
	}

	public static function LinkButton( string $href, string $label, string $class = NULL ): string
	{
		$attributes['onclick']	= 'document.location.href=\''.$href.'\'';
		$attributes['class']	= $class;
		$attributes['type']		= 'button';
		return self::Tag( 'button', $label, $attributes );
	}

	static function Options( array $items, $selected = NULL, array $keys = [] ): string
	{
		if( !count( $items ) )
			return '';
		$values	= array_values( $items );
		$first	= array_shift( $values );
		if( is_object( $first ) && count( $keys ) === 2 ){
			$list	= [];
			foreach( $items as $item ){
				$key	= $item->{$keys[0]};
				$label	= $item->{$keys[1]};
				$list[$key]	= $label;
			}
			$items	= $list;
		}
		return HtmlElements::Options( $items, $selected );
	}

	public static function Password( string $name, string $class = NULL, bool $readonly = NULL ): string
	{
		$attributes		= [
			'type'		=> 'password',
			'id'		=> self::$prefixIdInput.$name,
			'name'		=> $name,
			'class'		=> $class,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		];
		return self::Tag( 'input', NULL, $attributes );
	}

	public static function Select( string $name, $options, string $class = NULL, bool $readonly = NULL, string $onChange = NULL ): string
	{
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
		return HTML::Tag( "select", $options, $attributes );
	}

	public static function SpanClass( string $class, $content = '', array $attributes = [] ): string
	{
		return HTML::Tag( 'span', $content, ['class' => $class] );
	}

	public static function Tag( string $nodeName, $content = NULL, array $attributes = [], array $data = [] ): string
	{
		return HtmlTag::create( $nodeName, $content, $attributes );
	}

	public static function Text( string $name, $content, string $class = NULL, int $numberRows = NULL, bool $readonly = NULL ): string
	{
		$content	= htmlspecialchars( $content, ENT_COMPAT, 'UTF-8' );
		$attributes	= [
			'name'		=> $name,
			'id'		=> self::$prefixIdInput.$name,
			'class'		=> $class,
			'rows'		=> $numberRows,
			'readonly'	=> $readonly ? 'readonly' : NULL,
		];
		return HTML::Tag( 'textarea', $content, $attributes );
	}

	public static function UlClass( string $class, $content ): string
	{
		return HTML::Tag( 'ul', $content, ['class' => $class] );
	}
}
