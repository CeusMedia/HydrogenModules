<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconProducts	= '';
$iconLicense	= HtmlTag::create( 'i', '', array( 'class' => 'arrow-right' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconProducts	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cubes' ) );
	$iconLicense	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );
}

$list	= HtmlTag::create( 'div', 'Keine Produkte vorhanden.', array( 'class' => 'alert alert-notice' ) );
if( $products ){
	$list	= [];
	foreach( $products as $product ){
		$list[]	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'h4', $product->title ),
				HtmlTag::create( 'p', $product->description ),
				HtmlTag::create( 'p', array(
					HtmlTag::create( 'a', $iconLicense.'&nbsp;weiter', array(
	//					'href'		=> './catalog/provision/product/'.$product->productId.'-'.$logic->getUriPart( $product->title ),
						'href'		=> $logic->getProductUri( $product ),
					 	'class'		=> 'btn btn-large',
					) ),
				) ),
			), array( 'class' => 'catalog-provision-product-list-container' ) ),
		), array( 'class' => 'catalog-provision-product-list-item' ) );
	}
	$list	= HtmlTag::create( 'div', $list, array( 'class' => 'catalog-provision-product-list' ) );
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'catalog/provision/product/index' ) );
$textTop	= $textTop ? $textTop : '<h2>'.$iconProducts.'&nbsp;Produkte</h2>';

return $textTop.$list.$textBottom;
