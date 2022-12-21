<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

extract( $view->populateTexts( ['top', 'bottom', 'service'], 'html/shop/' ) );

$panels	= [];
foreach( $servicePanels as $servicePanel ){
	$key	= (float) $servicePanel->priority.'.'.time();
	$panels[$key]	= HtmlTag::create( 'div', $servicePanel->content, array(
		'id'	=> 'panel-shop-service-'.$servicePanel->key,
	) );
	ksort( $panels );
}

$panels		= join( $panels );

//$textService	= !empty( $delivery ) ? $delivery : '';

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-service' );
$helperTabs->setContent( $panels );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

return $textTop.$helperTabs->render().$textBottom;
