<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<string,array<string,string>> $words */
/** @var Model_Shop_Cart $cart */
/** @var View_Shop $view */
/** @var int|string $userId */

$w			= (object) $words['customer'];

$customerMode   = $cart->get( 'customerMode' );
if( Model_Shop_Cart::CUSTOMER_MODE_ACCOUNT === $customerMode ){
 	if( $userId )
		$tabContent	= $view->loadTemplateFile( 'shop/customer/inside.php' );
	else
		$tabContent	= $view->loadTemplateFile( 'shop/customer/outside.php' );
}
if( Model_Shop_Cart::CUSTOMER_MODE_GUEST === $customerMode ){
	$tabContent	= $view->loadTemplateFile( 'shop/customer/inside.php' );
}

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/' ) );

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-customer' );
$helperTabs->setContent( $tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

return $textTop.$helperTabs->render().$textBottom;
