<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Catalog_Provision_Product $view */
/** @var Logic_Catalog_Provision $logic */
/** @var object[] $licenses */

$w	= (object) $env->getLanguage()->getSection( 'catalog/provision', 'product.view' );

$iconCart		= HtmlTag::create( 'i', '', ['class' => 'shopping-cart'] );
$iconProduct	= HtmlTag::create( 'i', '', ['class' => 'arrow-right'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCart		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-shopping-cart'] );
	$iconLicense	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cube'] );
	$iconProducts	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cubes'] );
}

$list	= HtmlTag::create( 'div', $w->noEntries, ['class' => 'alert alert-notice'] );
if( $licenses ){
	$list	= [];
	foreach( $licenses as $license ){
		$list[]	= HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'h4', $license->title ),
				HtmlTag::create( 'p', $license->description ),
				HtmlTag::create( 'a', $iconLicense.'&nbsp;'.$w->buttonNext, [
//					'href'		=> './catalog/provision/product/license/'.$license->productLicenseId,
					'href'		=> $logic->getProductLicenseUri( $license ),
					'class'		=> 'btn btn-large',
				] ),
				'&nbsp',
				HtmlTag::create( 'a', $iconCart.'&nbsp;'.$w->buttonAddToCart, [
					'href'		=> './shop/addArticle/1/'.$license->productLicenseId,
				 	'class'		=> 'btn btn-large',
				] ),
			], ['class' => 'catalog-provision-license-list-item-container'] ),
		], ['class' => 'catalog-provision-license-list-item'] );
	}
	$list	= HtmlTag::create( 'div', $list, ['class' => 'catalog-provision-license-list'] );
}

extract( $view->populateTexts( ['top', 'bottom'], 'catalog/provision/product/view/' ) );
if( !$textTop )
	$textTop	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h2', [
 			HtmlTag::create( 'a', $iconProducts.'&nbsp;'.$w->labelProduct, [
				'href'		=> "catalog/provision/product",
				'class'		=> "muted",
			] ),
			$product->title,
		] ),
	] );

return $textTop.$list.$textBottom;
