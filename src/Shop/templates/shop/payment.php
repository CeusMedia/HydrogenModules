<?php

use CeusMedia\Bootstrap\Button;
use CeusMedia\Bootstrap\Button\Link as LinkButton;
use CeusMedia\Bootstrap\Button\Submit as SubmitButton;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['payment'];

//print_m($cart->getAll());

$list	= [];
foreach( $paymentBackends as $paymentBackend ){
	$icon	= '';
	if( $paymentBackend->countries && !in_array( $billingAddress->country, $paymentBackend->countries ) )
		continue;
	$path	= $env->getConfig()->get( 'path.images' ).'paymentProviderLogo/medium/';
	if( $paymentBackend->icon ){
		$icon	= '&nbsp;&nbsp;&nbsp;'.HtmlTag::create( 'i', '', ['class' => $paymentBackend->icon] ).'&nbsp;&nbsp;&nbsp;';
		if( preg_match( '/\.(png|jpe?g?)$/i', $paymentBackend->icon ) )
			$icon	= HtmlTag::create( 'img', NULL, ['src' => $path.$paymentBackend->icon] );
	}
	$link	= HtmlTag::create( 'a', $icon.'&nbsp;&nbsp;'.$paymentBackend->title, array(
		'href'	=> './shop/setPaymentBackend/'.$paymentBackend->key,
		'class' => ' '.( $cart->get( 'paymentMethod' ) === $paymentBackend->key ? 'current' : '' ),
//		'style' => 'display: inline-block; float: left; padding: 0.5em',
	) );
	$key	= $paymentBackend->priority.'.'.uniqid();
	$list[$key]	= HtmlTag::create( 'li', $link, ['class' => 'payment-method-list-item'] );
}
ksort( $list );
$list	= HtmlTag::create( 'ul', $list, array( 'class' => 'unstyled payment-method-list') );

$iconSubmit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );


$buttonPrev	= new LinkButton( './shop/conditions', $w->buttonToConditions, 'not-pull-right', 'fa fa-fw fa-arrow-left' );
//$buttonNext	= new SubmitButton( "save", $w->buttonNext, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' );
$buttonNext	= new LinkButton( './shop/checkout', $w->buttonNext, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' );
if( !$cart->get( 'paymentMethod' ) )
	$buttonNext	= new Button( $w->buttonNext, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right', TRUE );

$buttonbar	= '
<br/>
<!--<form action="shop/checkout" method="post">-->
	<div class="buttonbar well well-small">
		'.$buttonPrev.'
		'.$buttonNext.'
	</div>
<!--</form>-->';

$tabContent	= '
<h3>'.$w->heading.'</h3>
<p>'.$w->textTop.'</p>
'.$list.'
'.$buttonbar.'';

extract( $view->populateTexts( ['top', 'bottom'], 'html/shop/' ) );

$w			= (object) $words['payment'];

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-payment' );
$helperTabs->setContent( $tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

return $textTop.$helperTabs->render().$textBottom;
