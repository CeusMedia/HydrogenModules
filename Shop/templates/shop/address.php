<?php

$helper		= new View_Helper_Shop_AddressForm( $env );
$helper->setAddress( $address );
$helper->setHeading( $address->type == 2 ? 'Rechnungsanschrift' : 'Lieferanschrift' );
$helper->setType( 0 );
$helper->setTextTop( '<p>Anschrift ändern.</p>' );
$tabContent	= $helper->render();


extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/' ) );

$helper		= new View_Helper_Shop_Tabs( $env );
$helper->setCurrent( 1 );
$helper->setContent( $tabContent );
$helper->setPaymentBackends( $this->getData( 'paymentBackends' ) );
//$helper->setWhiteIcons( $options->get( 'tabs.icons.white' ) );
$tabs	= $helper->render();

return $textTop.$tabs.$textBottom;
