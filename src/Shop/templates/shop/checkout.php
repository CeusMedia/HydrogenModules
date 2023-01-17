<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Bootstrap\Button\Link as LinkButton;
use CeusMedia\Bootstrap\Button\Submit as SubmitButton;

$w				= (object) $words['checkout'];

$helperAddress	= new View_Helper_Shop_AddressView( $env );
$helperCart		= new View_Helper_Shop_CartPositions( $env );
$helperCart->setPositions( $cart->get( 'positions' ) );
$helperCart->setDeliveryAddress( $address );
$helperCart->setChangeable( TRUE );
$helperCart->setForwardPath( 'shop/checkout' );
//$helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_HTML_LIST );
$tablePositionsDesktop	= HtmlTag::create( 'div', $helperCart->render(), ['class' => 'hidden-phone'] );
$tablePositionsPhone	= HtmlTag::create( 'div', $helperCart->render(), ['class' => 'visible-phone'] );
$tablePositions			= $tablePositionsDesktop.$tablePositionsPhone;

extract( $view->populateTexts( ['top', 'bottom', 'checkout.top', 'checkout.bottom'], 'html/shop/' ) );

$buttonPrev	= new LinkButton( './shop/conditions', $w->buttonToConditions, 'not-pull-right', 'fa fa-fw fa-arrow-left' );
if( count( $paymentBackends ) > 1 && $cartTotal > 0 )
	$buttonPrev	= new LinkButton( './shop/payment', $w->buttonToPayment, 'not-pull-right', 'fa fa-fw fa-arrow-left' );

$buttonNext	= new SubmitButton( 'save', $w->buttonNext, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' );
if( !$paymentBackends || $cartTotal == 0 )
	$buttonNext	= new SubmitButton( 'save', $w->buttonNextPriceless, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' );

$tabContent	= HtmlTag::create( 'div', array(
	$textCheckoutTop,
	HtmlTag::create( 'form', array(
		HtmlTag::create( 'h4', $words['panel-cart']['heading'] ),
		$tablePositions,
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'h4', $words['panel-customer']['heading'] ),
				$helperAddress->setAddress( $customer->addressDelivery ),
			), ['class' => 'span6'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'h4', $words['panel-billing']['heading'] ),
				$helperAddress->setAddress( $customer->addressBilling ),
			), ['class' => 'span6'] ),
		), ['class' => 'row-fluid'] ),
		$textCheckoutBottom,
		HtmlTag::create( 'div', [
			$buttonPrev, ' ',
			$buttonNext,
		], ['class' => 'buttonbar well well-small'] ),
	), ['method' => 'post', 'action' => './shop/checkout', 'id' => 'form-shop-checkout'] ),
) );

$w				= (object) $words['modal-loading-payment'];
$modalLoading	= '<div id="modalLoadingPayment" class="modal hide not-fade">
	<div class="modal-header">
		<h4>'.$w->heading.'</h4>
	</div>
	<div class="modal-body">
		<big><i class="fa fa-fw fa-spin fa-circle-o-notch"></i> '.$w->title.'</big><br/>
		<br/>
		<p>'.$w->message.'</p>
		<p><strong>'.$w->slogan.'</strong></p>
		<br/>
	</div>
</div><script>
jQuery(document).ready(function(){
	if('.( count( $paymentBackends ) && $cartTotal > 0 ).'){
		jQuery("#form-shop-checkout button[type=submit]").on("click", function(event){
			jQuery("#modalLoadingPayment").modal();
		});
	}
});
</script>';

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/' ) );

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-checkout' );
$helperTabs->setContent( $tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

return $textTop.$helperTabs->render().$textBottom.$modalLoading;
