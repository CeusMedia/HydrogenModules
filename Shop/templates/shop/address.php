<?php

$w	= (object) $words['panel-customer-address-delivery'];
if( $address->type == 2 )
	$w	= (object) $words['panel-customer-address-billing'];

$helper		= new View_Helper_Shop_AddressForm( $env );
$helper->setAddress( $address );
$helper->setHeading( $w->heading );
$helper->setType( 0 );
if( strlen( trim( $w->textTop ) ) )
	$helper->setTextTop( UI_HTML_Tag::create( 'p', $w->textTop ) );
$tabContent	= $helper->render();


extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/' ) );

$helper		= new View_Helper_Shop_Tabs( $env );
$helper->setCurrent( 1 );
$helper->setContent( $tabContent );
$helper->setPaymentBackends( $this->getData( 'paymentBackends' ) );
//$helper->setWhiteIcons( $options->get( 'tabs.icons.white' ) );
$tabs	= $helper->render();

return $textTop.$tabs.$textBottom;
