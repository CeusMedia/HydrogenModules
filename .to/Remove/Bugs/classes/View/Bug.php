<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Bug extends View
{
	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'site.bug.css' );
	}

	protected function renderOptions( $options, $key, $values, $class = '' )
	{
		$list		= [];
		if( !is_array( $values ) )
			$values = $values ? [$values] : array();
		foreach( $options as $key => $value ){
			$selected	= !strlen( $key ) && !$values;
			if( strlen( $key ) )
				$selected	= in_array( $key, $values );
			$attributes	= array(
				'value'		=> $key,
				'class'		=> strlen( $key ) ? sprintf( $class, $key ) : '',
				'selected'	=>  $selected ? 'selected' : NULL,
			);
			$list[]	= HtmlTag::create( 'option', $value, $attributes );
		}
		return join( $list );
	}
}
