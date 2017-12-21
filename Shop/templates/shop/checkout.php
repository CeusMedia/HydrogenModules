<?php
$w				= (object) $words['checkout'];

$helperAddress	= new View_Helper_Shop_AddressView( $env );
$helperCart		= new View_Helper_Shop_CartPositions( $env );
$helperCart->setPositions( $positions );
//$helperCart->setChangeable( TRUE );

extract( $view->populateTexts( array( 'top', 'bottom', 'checkout.top', 'checkout.bottom' ), 'html/shop/' ) );

$buttonPrev	= new \CeusMedia\Bootstrap\LinkButton( './shop/conditions', $w->buttonToConditions, 'not-pull-right', 'fa fa-fw fa-arrow-left' );
if( count( $paymentBackends ) > 1 )
	$buttonPrev	= new \CeusMedia\Bootstrap\LinkButton( './shop/payment', $w->buttonToPayment, 'not-pull-right', 'fa fa-fw fa-arrow-left' );

$buttonNext	= new \CeusMedia\Bootstrap\SubmitButton( 'save', $w->buttonNext, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' );
if( !$paymentBackends /* || !$price*/ )
	$buttonNext	= new \CeusMedia\Bootstrap\SubmitButton( 'save', $w->buttonNextPriceless, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' );

$tabContent	= UI_HTML_Tag::create( 'div', array(
	$textCheckoutTop,
	UI_HTML_Tag::create( 'form', array(
		UI_HTML_Tag::create( 'h4', $words['panel-cart']['heading'] ),
		$helperCart->render(),
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
		UI_HTML_Tag::create( 'br', NULL ),
		UI_HTML_Tag::create( 'div', array(
			$buttonPrev, ' ',
			$buttonNext,
		), array( 'class' => 'buttonbar well well-small' ) ),
	), array( 'method' => 'post', 'action' => './shop/checkout', 'id' => 'form-shop-checkout' ) ),
	$textCheckoutBottom,
) );

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-checkout' );
$helperTabs->setContent( $tabContent );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/' ) );

$modalLoading	= '<div id="modalLoadingPayment" class="modal hide not-fade">
	<div class="modal-header">
		<h4>Weiterleitung</h4>
	</div>
	<div class="modal-body">
		<big><i class="fa fa-fw fa-spin fa-circle-o-notch"></i> Einen Moment bitte…</big>
		<br/>
		<br/>
		<p>
			Sie werden nun zum Bezahlanbieter weitergeleitet.<br/>
			Das kann ein paar Sekunden dauern.<br/>
			Bitte warten Sie einen kleinen Moment.
		</p>
		<p><strong>Vielen Dank!</strong></p>
		<br/>
	</div>
</div><script>
var needsPayment = '.count( $paymentBackends ).';
jQuery(document).ready(function(){
	if(needsPayment){
		jQuery("#form-shop-checkout button[type=submit]").bind("click", function(event){
			jQuery("#modalLoadingPayment").modal();
		});
	}
});
</script>';

return $textTop.$helperTabs->render().$textBottom.$modalLoading;
?>
