<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCart		= HtmlTag::create( 'i', '', ['class' => 'shopping-cart'] );
$iconProduct	= HtmlTag::create( 'i', '', ['class' => 'arrow-right'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCart		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-shopping-cart'] );
	$iconLicense	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cube'] );
	$iconProducts	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cubes'] );
}

$details	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $product->title.' Lizenzen' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'h4', $license->title ),
		HtmlTag::create( 'p', $license->description ),
		HtmlTag::create( 'a', $iconCart.'&nbsp;in den Warenkorb', array(
			'href'		=> './shop/addArticle/1/'.$license->productLicenseId,
		 	'class'		=> 'btn btn-large',
		) ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

extract( $view->populateTexts( ['top', 'bottom'], 'catalog/provision/product/license/' ) );
if( !$textTop )
	$textTop	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'h2', array(
 			HtmlTag::create( 'a', $iconProducts.'&nbsp;Produkt: ', array(
				'href'		=> "catalog/provision/product",
				'class'		=> "muted",
			) ),
			$product->title,
		) ),
		HtmlTag::create( 'h3', array(
			HtmlTag::create( 'a', $iconLicense.'&nbsp;Lizenz: ', array(
				'href'	=> 'catalog/provision/product/'.$product->productId,
				'class'	=>	"muted",
			) ),
			$license->title,
		) ),
	) );

return $textTop.$details.$textBottom;
