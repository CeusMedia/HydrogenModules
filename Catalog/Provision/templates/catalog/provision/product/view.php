<?php


$list	= UI_HTML_Tag::create( 'div', 'Keine Lizenzen vorhanden.', array( 'class' => 'alert alert-notice' ) );
if( $licenses ){
	$list	= array();
	foreach( $licenses as $license ){
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', array(
				UI_HTML_Tag::create( 'h4', $license->title ),
				UI_HTML_Tag::create( 'p', $license->description ),
				UI_HTML_Tag::create( 'a', 'in den Warenkorb', array(
					'href'		=> './shop/addArticle/1/'.$license->productLicenseId,
				 	'class'		=> 'btn',
				) ),
			) ),
		) );
	}
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $tbody ), array( 'class' => 'table' ) );
}

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $product->title.' Lizenzen' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
//		UI_HTML_Tag::create( 'div', array(), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
