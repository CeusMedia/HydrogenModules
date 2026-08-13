<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Button\Link as LinkButton;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Environment $env */
/** @var View_Shop $view */
/** @var array<string,array<string,string>> $words */
/** @var Entity_Address $address $w */
/** @var Model_Shop_Payment_BackendRegister $paymentBackends */
/** @var Model_Shop_Cart $cart */
/** @var float $cartTotal */

$w		= (object) $words['cart'];

$tablePositions	= '<p><em class="muted">'.$w->empty.'</em></p>';
$tableShipping	= '';
$buttonbar		= '';

$positions		= $cart->get( 'positions' );
$hasPositions	= is_array( $positions ) && [] !== $positions;

if( $hasPositions ){
	$helperCart		= new View_Helper_Shop_CartPositions( $env );
	$helperCart->setPositions( $positions );
	$helperCart->setPaymentBackends( $paymentBackends );
	if( '' !== trim( $cart->get( 'paymentMethod' ) ?? '' ) )
		$helperCart->setPaymentBackend( $cart->get( 'paymentMethod' ) );
	if( is_object( $address ) )
		$helperCart->setDeliveryAddress( $address );
	$helperCart->setChangeable();
	$tablePositions	= $helperCart->render();
	$buttonNext		= new LinkButton(
		'./shop/customer',
		$w->buttonToCustomer,
		'btn-success not-pull-right',
		'fa fa-fw fa-arrow-right',
		!$positions
	);
	$buttonForget	= new LinkButton(
		'./shop/forget',
		$w->buttonForget,
		'btn pull-right',
		'fa fa-fw fa-trash',
		!$positions
	);
	$buttonbar		= HtmlTag::create( 'div', [$buttonNext, $buttonForget], ['class' => 'buttonbar well well-small'] );

	$helperShipping	= new View_Helper_Shop_Shipping( $env );
	$helperShipping->setPositions( $positions );
	if( is_object( $address ) )
		$helperShipping->setDeliveryAddress( $address );

	$tableShipping	= $helperShipping->render();
}

$tabContent	= '
	<h3>'.$w->heading.'</h3>
	'.$tablePositions.'
	'.$buttonbar.'
	<br/>
	<br/>
	'.$tableShipping.'
';

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-cart' );
$helperTabs->setContent( $tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/' ) );

return $textTop.$helperTabs->render().$textBottom;
