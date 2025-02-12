<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Model_Shop_Payment_BackendRegister $paymentBackends */

$list	= [];
foreach( $paymentBackends->getAll() as $paymentBackend ){
	$icon	= '';
	if( $paymentBackend->icon )
		$icon	= HtmlTag::create( 'i', '', ['class' => $paymentBackend->icon] ).'&nbsp;';
	$link	= HtmlTag::create( 'a', $icon.$paymentBackend->title, [
		'href'	=> './shop/pay/'.$paymentBackend->key,
	] );
	$key	= $paymentBackend->priority.'.'.uniqid();
	$list[$key]	= HtmlTag::create( 'li', $link, ['class' => 'payment-method-list-item'] );
}
ksort( $list );
$list	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled payment-method-list'] );

$tabContent	= '
<h3>Bezahlmethode</h3>
<p>
	Bitte wählen Sie nun aus, mit welcher Methode Sie Ihre Bestellung bezahlen möchten!
</p>
'.$list.'
<br/>
<div id="modalLoadingPayment" class="modal hide not-fade">
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
</div>
<script>
jQuery(document).ready(function(){
	jQuery("ul.payment-method-list > li > a").on("click", function(event){
		jQuery("#modalLoadingPayment").modal();
	});
});
</script>
<style>
ul.payment-method-list li.payment-method-list-item {
	}
ul.payment-method-list li.payment-method-list-item a {
	display: block;
	font-size: 1.5em;
	line-height: 1.5em;
	margin-bottom: 0.2em;
	padding: 0.5em 0.75em;
	background-color: rgba(191, 191, 191, 0.05);
	border: 2px solid rgba(127, 127, 127, 0.25);
	border-radius: 0.2em;
	}
ul.payment-method-list li.payment-method-list-item a:hover {
	background-color: rgba(191, 191, 191, 0.25);
	border: 2px solid rgba(127, 127, 127, 0.5);
	}
ul.payment-method-list li.payment-method-list-item a i {
	margin-right: 0.25em;
	}
</style>
';

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/pay/' ) );

return $textTop.$view->renderTabs( $tabContent, 4, $options->get( 'tabs.icons.white' ) ).$textBottom;
