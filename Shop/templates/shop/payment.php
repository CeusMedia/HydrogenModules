<?php

$list	= array();
foreach( $paymentBackends as $paymentBackend ){
	$icon	= '';
	if( $paymentBackend->icon )
		$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $paymentBackend->icon ) ).'&nbsp;';
	$link	= UI_HTML_Tag::create( 'a', $icon.$paymentBackend->title, array(
		'href'	=> './shop/setPaymentBackend/'.$paymentBackend->key,
	) );
	$key	= $paymentBackend->priority.'.'.uniqid();
	$list[$key]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'payment-method-list-item' ) );
}
ksort( $list );
$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled payment-method-list') );

$tabContent	= '
<h3>Bezahlmethode</h3>
<p>
	Bitte wählen Sie nun aus, mit welcher Methode Sie Ihre Bestellung bezahlen möchten!
</p>
'.$list.'
<br/>
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

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/pay/' ) );

$w			= (object) $words['payment'];

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-payment' );
$helperTabs->setContent( $tabContent );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

$buttonbar	= '
<br/>
<form action="shop/customer" method="post">
	<div class="buttonbar well well-small">
		'.new \CeusMedia\Bootstrap\LinkButton( './shop/conditions', $w->buttonToConditions, 'not-pull-right', 'fa fa-fw fa-arrow-left' ).'
		'.new \CeusMedia\Bootstrap\SubmitButton( "save", $w->buttonNext, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' ).'
	</div>
</form>';

return $textTop.$helperTabs->render().$buttonbar.$textBottom;
?>
