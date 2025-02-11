<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Shop_Customer $view */
/** @var Entity_Address $address */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['customer-delivery'];
if( $address->type == 2 )
	$w	= (object) $words['customer-billing'];

$helper		= new View_Helper_Shop_AddressForm( $env );
$helper->setAddress( $address );
$helper->setHeading( $w->heading );
$helper->setType( $address->type );
if( strlen( trim( $w->textTop ) ) )
	$helper->setTextTop( HtmlTag::create( 'p', $w->textTop ) );
$tabContent	= $helper->render();

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/' ) );

$helper		= new View_Helper_Shop_Tabs( $env );
$helper->setCurrent( 'shop-customer' );
$helper->setContent( $tabContent );
$helper->setCartTotal( $cartTotal );
$helper->setPaymentBackends( $this->getData( 'paymentBackends' ) );
//$helper->setWhiteIcons( $options->get( 'tabs.icons.white' ) );
$tabs	= $helper->render();
return $textTop.$tabs.$textBottom;
