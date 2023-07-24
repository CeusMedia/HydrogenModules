<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Catalog_Provision_Product $view */
/** @var object $product */
/** @var object $license */

$iconCart		= HtmlTag::create( 'i', '', ['class' => 'shopping-cart'] );
$iconProduct	= HtmlTag::create( 'i', '', ['class' => 'arrow-right'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCart		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-shopping-cart'] );
	$iconLicense	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cube'] );
	$iconProducts	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cubes'] );
}

$details	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $product->title.' Lizenzen' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'h4', $license->title ),
		HtmlTag::create( 'p', $license->description ),
		HtmlTag::create( 'a', $iconCart.'&nbsp;in den Warenkorb', [
			'href'		=> './shop/addArticle/1/'.$license->productLicenseId,
		 	'class'		=> 'btn btn-large',
		] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

extract( $view->populateTexts( ['top', 'bottom'], 'catalog/provision/product/license/' ) );
if( !$textTop )
	$textTop	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h2', [
 			HtmlTag::create( 'a', $iconProducts.'&nbsp;Produkt: ', [
				'href'		=> "catalog/provision/product",
				'class'		=> "muted",
			] ),
			$product->title,
		] ),
		HtmlTag::create( 'h3', [
			HtmlTag::create( 'a', $iconLicense.'&nbsp;Lizenz: ', [
				'href'	=> 'catalog/provision/product/'.$product->productId,
				'class'	=>	"muted",
			] ),
			$license->title,
		] ),
	] );

return $textTop.$details.$textBottom;
