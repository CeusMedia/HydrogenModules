<?php
$w				= (object) $words['checkout'];

$iconSubmit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonPrev	= new \CeusMedia\Bootstrap\LinkButton( './shop/conditions', $w->buttonToConditions, 'not-pull-right', 'fa fa-fw fa-arrow-left' );
if( count( $paymentBackends ) > 1 )
	$buttonPrev	= new \CeusMedia\Bootstrap\LinkButton( './shop/payment', $w->buttonToPayment, 'not-pull-right', 'fa fa-fw fa-arrow-left' );

if( $w->linkCreditCard )
	$w->labelCreditCard	= UI_HTML_Tag::create( 'a', $w->labelCreditCard, array( 'href' => $w->linkCreditCard, 'target' => '_blank' ) );
if( $w->linkDebitCard )
	$w->labelDebitCard	= UI_HTML_Tag::create( 'a', $w->labelDebitCard, array( 'href' => $w->linkDebitCard, 'target' => '_blank' ) );
$labelCard	= sprintf( $w->labelCard, $w->labelCreditCard, $w->labelDebitCard );

$taxMode	= $configShop->get( 'tax.included' ) ? $w->taxInclusive : $w->taxExclusive;
$taxLabel	= UI_HTML_Tag::create( 'small', '('.$taxMode.' '.$w->labelTax.')', array( 'class' => 'muted' ) );
$alert		= $w->alertNotice ? '<div class="alert alert-notice">'.$w->alertNotice.'</div>' : NULL;
$form		= '
<form action="./shop/payment/stripe/perCreditCard" method="post" id="payment-form">
	'.$alert.'
	<div class="row-fluid">
		<div class="span12">
			<label for="card-element">'.$labelCard.'</label>
			<div id="card-element"><!-- a Stripe Element will be inserted here. --></div>
			<div id="card-errors" role="alert"><!-- Used to display Element errors --></div>
		</div>
	</div>
	<div class="row-fluid">
		'.$w->labelAmount.': <span>'.number_format( $order->price, 2, ',', '.' ).' '.$order->currency.' '.$taxLabel.'</span><br/>
		<br/>
	</div>
	<div class="buttonbar well well-small">
		'.$buttonPrev.'
		<button class="btn btn-primary" id="card-submit" disabled="disabled">'.$iconSubmit.' '.$w->buttonNext.'</button>
	</div>
</form>';

$w				= (object) $words['modal-loading'];
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
	if('.count( $paymentBackends ).'){
		jQuery("#card-submit").bind("click", function(event){
			jQuery("#modalLoadingPayment").modal();
		});
	}
});
</script>';

$script	= 'ShopPaymentStripe.apply("#card-element" ,"payment-form", "card-errors", "card-submit");';
$env->page->js->addScriptOnReady( $script );

$panel	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $w->heading ),
	UI_HTML_Tag::create( 'div', array(
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-checkout' );
$helperTabs->setContent( $panel );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/payment/stripe/perCreditCard' ) );

return $textTop.$helperTabs->render().$textBottom.$modalLoading;
