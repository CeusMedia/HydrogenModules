<?php

extract( $view->populateTexts( array( 'top', 'bottom', 'service' ), 'html/shop/' ) );

$panels	= array();
foreach( $servicePanels as $servicePanel ){
	$key	= (float) $servicePanel->priority.'.'.time();
	$panels[$key]	= UI_HTML_Tag::create( 'div', $servicePanel->content, array(
		'id'	=> 'panel-shop-service-'.$servicePanel->key,
	) );
	ksort( $panels );
}

$panels		= join( $panels );

//$textService	= !empty( $delivery ) ? $delivery : '';

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-service' );
$helperTabs->setContent( $panels );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

return $textTop.$helperTabs->render().$textBottom;
?>
