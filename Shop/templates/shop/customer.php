<?php
$w			= (object) $words['customer'];

if( $userId )
	$tabContent	= $this->loadTemplateFile( 'shop/customer.inside.php' );
else
	$tabContent	= $this->loadTemplateFile( 'shop/customer.outside.php' );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/' ) );

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-customer' );
$helperTabs->setContent( $tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

return $textTop.$helperTabs->render().$textBottom;
?>
