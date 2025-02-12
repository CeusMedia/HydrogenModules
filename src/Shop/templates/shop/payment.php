<?php

use CeusMedia\Bootstrap\Button;
use CeusMedia\Bootstrap\Button\Link as LinkButton;
use CeusMedia\Bootstrap\Button\Submit as SubmitButton;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Environment $env */
/** @var View_Shop $view */
/** @var Model_Shop_Cart $cart */
/** @var Model_Shop_Payment_BackendRegister $paymentBackends */
/** @var ?object $billingAddress */
/** @var float $cartTotal */
/** @var array $words */
/** @var array $backendPrices */

$w		= (object) $words['payment'];

//print_m($cart->getAll());

$list	= [];
foreach( $paymentBackends->getAll() as $paymentBackend ){
	$icon	= '';
	if( $paymentBackend->countries && !in_array( $billingAddress->country, $paymentBackend->countries ) )
		continue;
	$path	= $env->getConfig()->get( 'path.images' ).'paymentProviderLogo/medium/';
	if( $paymentBackend->icon ){
		$icon	= '&nbsp;&nbsp;&nbsp;'.HtmlTag::create( 'i', '', ['class' => $paymentBackend->icon] ).'&nbsp;&nbsp;&nbsp;';
		if( preg_match( '/\.(png|jpe?g?)$/i', $paymentBackend->icon ) )
			$icon	= HtmlTag::create( 'img', NULL, ['src' => $path.$paymentBackend->icon] );
	}
	$fees	= $backendPrices[$paymentBackend->key];

	$costs  = '';
	if( NULL !== $fees ){
		$fees  = number_format( $fees, 2, ',', '.' );
		$costs  = HtmlTag::create( 'div', 'Gebühr: '.$fees.'€', ['class' => 'item-fees'] );
	}

	$desc   = '';
	if( '' !== ( $paymentBackend->description ?? '' ) ){
		$desc   = HtmlTag::create( 'div', $paymentBackend->description, ['class' => 'item-desc'] );
	}

	$cont   = HtmlTag::create( 'div', [
		HtmlTag::create( 'div', $icon, ['class' => 'item-icon'] ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', $paymentBackend->label, ['class' => 'item-title'] ),
			$desc,
			$costs,
		], ['class' => 'item-data'] ),
	], ['class' => 'item', 'style' => 'display: flex; width: 100%'] );

	$link	= HtmlTag::create( 'a', $cont, [
		'href'	=> './shop/setPaymentBackend/'.$paymentBackend->key,
		'class' => ' '.( $cart->get( 'paymentMethod' ) === $paymentBackend->key ? 'current' : '' ),
//		'style' => 'display: inline-block; float: left; padding: 0.5em',
	] );
	$key	= $paymentBackend->priority.'.'.uniqid();
	$list[$key]	= HtmlTag::create( 'li', $link, ['class' => 'payment-method-list-item'] );
}
ksort( $list );
$list	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled payment-method-list'] );

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
