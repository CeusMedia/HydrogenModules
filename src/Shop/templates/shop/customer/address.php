<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Shop_Customer $view */
/** @var Entity_Address $address */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['customer-delivery'];
if( Model_Address::TYPE_BILLING === $address->type )
	$w	= (object) $words['customer-billing'];

$helper		= new View_Helper_Shop_AddressForm( $env );
$helper->setAddress( $address );
$helper->setHeading( $w->heading );
$helper->setType( $address->type );
if( strlen( trim( $w->textTop ) ) )
	$helper->setTextTop( HtmlTag::create( 'p', $w->textTop ) );
$tabContent	= $helper->render();

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$hint		= HtmlTag::create( 'a', $iconCancel.' zurück zur Auswahl', [
	'href'		=> './shop/customer',
	'class'		=> 'btn btn-small',
] );

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/' ) );

$helper		= new View_Helper_Shop_Tabs( $env );
$helper->setCurrent( 'shop-customer' );
$helper->setContent( $hint.$tabContent );
$helper->setCartTotal( $cartTotal );
$helper->setPaymentBackends( $this->getData( 'paymentBackends' ) );
//$helper->setWhiteIcons( $options->get( 'tabs.icons.white' ) );
$tabs	= $helper->render();
return $textTop.$tabs.$textBottom;
