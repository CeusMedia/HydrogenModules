<?php


$iconProducts	= '';
$iconLicense	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'arrow-right' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconProducts	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cubes' ) );
	$iconLicense	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );
}

$list	= UI_HTML_Tag::create( 'div', 'Keine Produkte vorhanden.', array( 'class' => 'alert alert-notice' ) );
if( $products ){
	$list	= [];
	foreach( $products as $product ){
		$list[]	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'h4', $product->title ),
				UI_HTML_Tag::create( 'p', $product->description ),
				UI_HTML_Tag::create( 'p', array(
					UI_HTML_Tag::create( 'a', $iconLicense.'&nbsp;weiter', array(
	//					'href'		=> './catalog/provision/product/'.$product->productId.'-'.$logic->getUriPart( $product->title ),
						'href'		=> $logic->getProductUri( $product ),
					 	'class'		=> 'btn btn-large',
					) ),
				) ),
			), array( 'class' => 'catalog-provision-product-list-container' ) ),
		), array( 'class' => 'catalog-provision-product-list-item' ) );
	}
	$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'catalog-provision-product-list' ) );
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'catalog/provision/product/index' ) );
$textTop	= $textTop ? $textTop : '<h2>'.$iconProducts.'&nbsp;Produkte</h2>';

return $textTop.$list.$textBottom;
