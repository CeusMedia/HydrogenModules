<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Work_Issue extends View
{
	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	/**
	 *	@param		array		$options
	 *	@param		$key
	 *	@param		$values
	 *	@param		string		$class
	 *	@return		string
	 */
	public function renderOptions( array $options, $key, $values, string $class = '' ): string
	{
		$list		= [];
		if( !is_array( $values ) )
			$values = $values ? [$values] : [];
		foreach( $options as $key => $value ){
			$selected	= !strlen( $key ) && !$values;
			if( strlen( $key ) )
				$selected	= in_array( $key, $values );
			$attributes	= [
				'value'		=> $key,
				'class'		=> strlen( $key ) ? sprintf( $class, $key ) : '',
				'selected'	=>  $selected ? 'selected' : NULL,
			];
			$list[]	= HtmlTag::create( 'option', $value, $attributes );
		}
		return join( $list );
	}
}
