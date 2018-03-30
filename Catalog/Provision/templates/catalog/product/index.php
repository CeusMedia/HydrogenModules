<?php

$list	= UI_HTML_Tag::create( 'div', 'Keine Produkte vorhanden.', array( 'class' => 'alert alert-notice' ) );
if( $products ){
	$list	= array();
	foreach( $products as $product ){
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', array(
				UI_HTML_Tag::create( 'h4', $product->title ),
				UI_HTML_Tag::create( 'p', $product->description ),
				UI_HTML_Tag::create( 'a', 'Lizenzen', array(
					'href'		=> './catalog/product/view/'.$product->productId,
				 	'class'		=> 'btn',
				) ),
			) ),
		) );
	}
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $tbody ), array( 'class' => 'table' ) );
}

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Produkte' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
//		UI_HTML_Tag::create( 'div', array(), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
