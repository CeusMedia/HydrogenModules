<?php

$iconCart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'shopping-cart' ) );
$iconProduct	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'arrow-right' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );
	$iconLicense	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cube' ) );
	$iconProducts	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cubes' ) );
}

$list	= UI_HTML_Tag::create( 'div', 'Keine Lizenzen vorhanden.', array( 'class' => 'alert alert-notice' ) );
if( $licenses ){
	$list	= [];
	foreach( $licenses as $license ){
		$list[]	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'h4', $license->title ),
				UI_HTML_Tag::create( 'p', $license->description ),
				UI_HTML_Tag::create( 'a', $iconLicense.'&nbsp;weiter', array(
//					'href'		=> './catalog/provision/product/license/'.$license->productLicenseId,
					'href'		=> $logic->getProductLicenseUri( $license ),
					'class'		=> 'btn btn-large',
				) ),
				'&nbsp',
				UI_HTML_Tag::create( 'a', $iconCart.'&nbsp;in den Warenkorb', array(
					'href'		=> './shop/addArticle/1/'.$license->productLicenseId,
				 	'class'		=> 'btn btn-large',
				) ),
			), array( 'class' => 'catalog-provision-license-list-item-container' ) ),
		), array( 'class' => 'catalog-provision-license-list-item' ) );
	}
	$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'catalog-provision-license-list' ) );
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'catalog/provision/product/view/' ) );
if( !$textTop )
	$textTop	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h2', array(
 			UI_HTML_Tag::create( 'a', $iconProducts.'&nbsp;Produkt: ', array(
				'href'		=> "catalog/provision/product",
				'class'		=> "muted",
			) ),
			$product->title,
		) ),
	) );

return $textTop.$list.$textBottom;
