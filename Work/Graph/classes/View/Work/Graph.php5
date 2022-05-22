<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Graph extends View{

	public function index(){}
	public function node(){}
	public function edge(){}

	public function renderFacts( $facts ){
		$list	= [];
		foreach( $facts as $fact ){
			if( !isset( $fact[2] ) )
				$fact[2]	= NULL;
			list( $label, $value, $default )	= $fact;
			if( !( $default === NULL || strlen( $default ) === 0 ) )
				$default	= UI_HTML_Tag::create( 'small', '('.$default.')', array( 'class' => 'muted' ) );

			if( !strlen( $value ) ){
				if( !$default )
					continue;
				$value	= $default;
			}
			else{
				if( $default )
					$value	= $value.' '.$default;
			}
			$list[]	= UI_HTML_Tag::create( 'dt', $label );
			$list[]	= UI_HTML_Tag::create( 'dd', $value );
		}
		if( !$list )
			return;
		return UI_HTML_Tag::create( 'dl', $list, array(
			'class'	=> 'dl-horizontal',
		) );
	}

}
