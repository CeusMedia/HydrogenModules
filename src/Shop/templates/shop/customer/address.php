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

$helperForm		= new View_Helper_Shop_AddressForm( $env );
$helperForm->setAddress( $address );
$helperForm->setHeading( $w->heading );
$helperForm->setType( $address->type );
if( strlen( trim( $w->textTop ) ) )
	$helperForm->setTextTop( HtmlTag::create( 'p', $w->textTop ) );
$tabContent	= $helperForm->render();

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$hint		= HtmlTag::create( 'a', $iconCancel.' zurück zur Auswahl', [
	'href'		=> './shop/customer',
	'class'		=> 'btn btn-small',
] );

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/' ) );

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-customer' );
$helperTabs->setContent( $hint.$tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );
//$helperTabs->setWhiteIcons( $options->get( 'tabs.icons.white' ) );
$tabs	= $helperTabs->render();

return $textTop.$tabs.$textBottom;
