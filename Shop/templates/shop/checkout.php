<?php
$w				= (object) $words['checkout'];

$helperAddress	= new View_Helper_Shop_AddressView( $env );
$helperCart		= new View_Helper_Shop_CartPositions( $env );
$helperCart->setPositions( $cart->get( 'positions' ) );
$helperCart->setDeliveryAddress( $address );
$helperCart->setChangeable( TRUE );
$helperCart->setForwardPath( 'shop/checkout' );
//$helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_HTML_LIST );
$tablePositionsDesktop	= UI_HTML_Tag::create( 'div', $helperCart->render(), array( 'class' => 'hidden-phone' ) );
$tablePositionsPhone	= UI_HTML_Tag::create( 'div', $helperCart->render(), array( 'class' => 'visible-phone' ) );
$tablePositions			= $tablePositionsDesktop.$tablePositionsPhone;

extract( $view->populateTexts( array( 'top', 'bottom', 'checkout.top', 'checkout.bottom' ), 'html/shop/' ) );

$buttonPrev	= new \CeusMedia\Bootstrap\LinkButton( './shop/conditions', $w->buttonToConditions, 'not-pull-right', 'fa fa-fw fa-arrow-left' );
if( count( $paymentBackends ) > 1 && $cartTotal > 0 )
	$buttonPrev	= new \CeusMedia\Bootstrap\LinkButton( './shop/payment', $w->buttonToPayment, 'not-pull-right', 'fa fa-fw fa-arrow-left' );

$buttonNext	= new \CeusMedia\Bootstrap\SubmitButton( 'save', $w->buttonNext, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' );
if( !$paymentBackends || $cartTotal == 0 )
	$buttonNext	= new \CeusMedia\Bootstrap\SubmitButton( 'save', $w->buttonNextPriceless, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' );

$tabContent	= UI_HTML_Tag::create( 'div', array(
	$textCheckoutTop,
	UI_HTML_Tag::create( 'form', array(
		UI_HTML_Tag::create( 'h4', $words['panel-cart']['heading'] ),
		$tablePositions,
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'h4', $words['panel-customer']['heading'] ),
				$helperAddress->setAddress( $customer->addressDelivery ),
			), array( 'class' => 'span6' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'h4', $words['panel-billing']['heading'] ),
				$helperAddress->setAddress( $customer->addressBilling ),
			), array( 'class' => 'span6' ) ),
		), array( 'class' => 'row-fluid' ) ),
		$textCheckoutBottom,
		UI_HTML_Tag::create( 'div', array(
			$buttonPrev, ' ',
			$buttonNext,
		), array( 'class' => 'buttonbar well well-small' ) ),
	), array( 'method' => 'post', 'action' => './shop/checkout', 'id' => 'form-shop-checkout' ) ),
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

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/' ) );

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-checkout' );
$helperTabs->setContent( $tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

return $textTop.$helperTabs->render().$textBottom.$modalLoading;
?>
