<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCart		= HtmlTag::create( 'i', '', array( 'class' => 'shopping-cart' ) );
$iconProduct	= HtmlTag::create( 'i', '', array( 'class' => 'arrow-right' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCart		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );
	$iconLicense	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cube' ) );
	$iconProducts	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cubes' ) );
}

$list	= HtmlTag::create( 'div', 'Keine Lizenzen vorhanden.', array( 'class' => 'alert alert-notice' ) );
if( $licenses ){
	$list	= [];
	foreach( $licenses as $license ){
		$list[]	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'h4', $license->title ),
				HtmlTag::create( 'p', $license->description ),
				HtmlTag::create( 'a', $iconLicense.'&nbsp;weiter', array(
//					'href'		=> './catalog/provision/product/license/'.$license->productLicenseId,
					'href'		=> $logic->getProductLicenseUri( $license ),
					'class'		=> 'btn btn-large',
				) ),
				'&nbsp',
				HtmlTag::create( 'a', $iconCart.'&nbsp;in den Warenkorb', array(
					'href'		=> './shop/addArticle/1/'.$license->productLicenseId,
				 	'class'		=> 'btn btn-large',
				) ),
			), array( 'class' => 'catalog-provision-license-list-item-container' ) ),
		), array( 'class' => 'catalog-provision-license-list-item' ) );
	}
	$list	= HtmlTag::create( 'div', $list, array( 'class' => 'catalog-provision-license-list' ) );
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'catalog/provision/product/view/' ) );
if( !$textTop )
	$textTop	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'h2', array(
 			HtmlTag::create( 'a', $iconProducts.'&nbsp;Produkt: ', array(
				'href'		=> "catalog/provision/product",
				'class'		=> "muted",
			) ),
			$product->title,
		) ),
	) );

return $textTop.$list.$textBottom;
