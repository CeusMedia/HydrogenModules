<?php

$w	= (object) $words['customer-delivery'];
if( $address->type == 2 )
	$w	= (object) $words['customer-billing'];

$helper		= new View_Helper_Shop_AddressForm( $env );
$helper->setAddress( $address );
$helper->setHeading( $w->heading );
$helper->setType( 0 );
if( strlen( trim( $w->textTop ) ) )
	$helper->setTextTop( UI_HTML_Tag::create( 'p', $w->textTop ) );
$tabContent	= $helper->render();

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/' ) );

$helper		= new View_Helper_Shop_Tabs( $env );
$helper->setCurrent( 'shop-customer' );
$helper->setContent( $tabContent );
$helper->setCartTotal( $cartTotal );
$helper->setPaymentBackends( $this->getData( 'paymentBackends' ) );
//$helper->setWhiteIcons( $options->get( 'tabs.icons.white' ) );
$tabs	= $helper->render();
return $textTop.$tabs.$textBottom;
