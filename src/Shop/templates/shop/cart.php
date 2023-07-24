<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['cart'];

$tablePositions	= '<p><em class="muted">'.$w->empty.'</em></p>';
$buttonbar		= '';

if( count( $positions = $cart->get( 'positions' ) ) ){
	$helperCart		= new View_Helper_Shop_CartPositions( $env );
	$helperCart->setPositions( $positions );
	$helperCart->setDeliveryAddress( $address );
	$helperCart->setChangeable( TRUE );
	$tablePositions	= $helperCart->render();
	$buttonbar		= HtmlTag::create( 'div', array(
		new \CeusMedia\Bootstrap\Button\Link( './shop/customer', $w->buttonToCustomer, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right', !$positions )
	), ['class' => 'buttonbar well well-small'] );
}

$tabContent	= '
	<h3>'.$w->heading.'</h3>
	'.$tablePositions.'
	'.$buttonbar.'
';

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-cart' );
$helperTabs->setContent( $tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/' ) );

return $textTop.$helperTabs->render().$textBottom;
