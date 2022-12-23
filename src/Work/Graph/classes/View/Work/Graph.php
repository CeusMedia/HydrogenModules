<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
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
				$default	= HtmlTag::create( 'small', '('.$default.')', ['class' => 'muted'] );

			if( !strlen( $value ) ){
				if( !$default )
					continue;
				$value	= $default;
			}
			else{
				if( $default )
					$value	= $value.' '.$default;
			}
			$list[]	= HtmlTag::create( 'dt', $label );
			$list[]	= HtmlTag::create( 'dd', $value );
		}
		if( !$list )
			return;
		return HtmlTag::create( 'dl', $list, array(
			'class'	=> 'dl-horizontal',
		) );
	}

}