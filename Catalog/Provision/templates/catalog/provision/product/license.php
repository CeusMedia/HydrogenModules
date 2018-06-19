<?php

$iconCart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'shopping-cart' ) );
$iconProduct	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'arrow-right' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );
	$iconLicense	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cube' ) );
	$iconProducts	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cubes' ) );
}

$details	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $product->title.' Lizenzen' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h4', $license->title ),
		UI_HTML_Tag::create( 'p', $license->description ),
		UI_HTML_Tag::create( 'a', $iconCart.'&nbsp;in den Warenkorb', array(
			'href'		=> './shop/addArticle/1/'.$license->productLicenseId,
		 	'class'		=> 'btn btn-large',
		) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'catalog/provision/product/license/' ) );
if( !$textTop )
	$textTop	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h2', array(
 			UI_HTML_Tag::create( 'a', $iconProducts.'&nbsp;Produkt: ', array(
				'href'		=> "catalog/provision/product",
				'class'		=> "muted",
			) ),
			$product->title,
		) ),
		UI_HTML_Tag::create( 'h3', array(
			UI_HTML_Tag::create( 'a', $iconLicense.'&nbsp;Lizenz: ', array(
				'href'	=> 'catalog/provision/product/'.$product->productId,
				'class'	=>	"muted",
			) ),
			$license->title,
		) ),
	) );

return $textTop.$details.$textBottom;
