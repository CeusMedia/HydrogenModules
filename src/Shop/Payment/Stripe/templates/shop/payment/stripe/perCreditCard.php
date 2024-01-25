<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Model_Shop_Payment_Register $paymentBackends */

$w				= (object) $words['checkout'];

$iconSubmit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonPrev	= new \CeusMedia\Bootstrap\LinkButton( './shop/conditions', $w->buttonToConditions, 'not-pull-right', 'fa fa-fw fa-arrow-left' );
if( count( $paymentBackends->getAll() ) > 1 )
	$buttonPrev	= new \CeusMedia\Bootstrap\LinkButton( './shop/payment', $w->buttonToPayment, 'not-pull-right', 'fa fa-fw fa-arrow-left' );

if( $w->linkCreditCard )
	$w->labelCreditCard	= HtmlTag::create( 'a', $w->labelCreditCard, ['href' => $w->linkCreditCard, 'target' => '_blank'] );
if( $w->linkDebitCard )
	$w->labelDebitCard	= HtmlTag::create( 'a', $w->labelDebitCard, ['href' => $w->linkDebitCard, 'target' => '_blank'] );
$labelCard	= sprintf( $w->labelCard, $w->labelCreditCard, $w->labelDebitCard );

$taxMode	= $configShop->get( 'tax.included' ) ? $w->taxInclusive : $w->taxExclusive;
$taxLabel	= HtmlTag::create( 'small', '('.$taxMode.' '.$w->labelTax.')', ['class' => 'muted'] );
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

$script	= 'ShopPaymentStripe.apply("#card-element" ,"payment-form", "card-errors", "card-submit");';
$env->page->js->addScriptOnReady( $script );

$panel	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		$form,
	], ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

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
	if('.count( $paymentBackends->getAll() ).'){
		jQuery("#card-submit").on("click", function(event){
			jQuery("#modalLoadingPayment").modal();
		});
	}
});
</script>';

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-checkout' );
$helperTabs->setContent( $panel );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/payment/stripe/perCreditCard' ) );

return $textTop.$helperTabs->render().$textBottom.$modalLoading;
