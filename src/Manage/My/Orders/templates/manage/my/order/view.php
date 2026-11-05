<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//print_m( $paymentBackends );die;


/** @var array $words */

$wordsShop	= $env->getLanguage()->getWords( 'shop' );
$w			= (object) $words['view'];

/*  --  PANEL: FACTS  --  */
$status	= $wordsShop['statuses-order'][$order->status];
$status	= HtmlTag::create( 'acronym', $status, ['title' => $wordsShop['statuses-order-title'][$order->status]] );
$method	= $order->paymentMethod;
if( isset( $paymentBackends[$order->paymentMethod] ) )
	$method	= $paymentBackends[$order->paymentMethod]->title;
$panelFacts	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->headingFacts ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'ul', array(
			HtmlTag::create( 'li', sprintf( $w->factPositions, count( $order->positions ) ) ),
			HtmlTag::create( 'li', sprintf( $w->factDate, date( 'd.m.Y', $order->createdAt ) ) ),
			HtmlTag::create( 'li', sprintf( $w->factPrice, $order->priceTaxed.' '.$order->currency ) ),
			HtmlTag::create( 'li', sprintf( $w->factStatus, $status ) ),
			HtmlTag::create( 'li', sprintf( $w->factPayment, $method ) ),
		), ['style' => 'font-size: 1.1em'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  PANEL: POSITIONS  --  */
$positions	= HtmlTag::create( 'div', $w->noPositions, ['class' => 'alert alert-info'] );
if( $order->positions ){
	$helper	= new View_Helper_Shop_CartPositions( $env );
	$helper->setPositions( $order->positions );
	$positions	= $helper->render();
}
$panelPositions	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->headingPositions ),
	HtmlTag::create( 'div', $positions, ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  PANEL: BILLING ADDRESS  --  */
$helper	= new View_Helper_Shop_AddressView( $env );
$helper->setAddress( $order->customer->addressBilling );
$panelBilling	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->headingBilling ),
	HtmlTag::create( 'div', $helper->render(), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  PANEL: DELIVERY  --  */
$helper->setAddress( $order->customer->addressDelivery );
$panelDelivery	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->headingDelivery ),
	HtmlTag::create( 'div', $helper->render(), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  PANEL: DATA  --  */
$panelData	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->headingData ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			print_m( $order, NULL, NULL, TRUE ),
		), ['style' => 'height: 500px; overflow-y: auto'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  GRID  --  */
return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', [
			$panelFacts,
		], ['class' => 'span4'] ),
		HtmlTag::create( 'div', [
			$panelDelivery,
		], ['class' => 'span4'] ),
		HtmlTag::create( 'div', [
			$panelBilling,
		], ['class' => 'span4'] ),
	), ['class' => 'row-fluid'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', [
			$panelPositions,
		], ['class' => 'span12'] ),
	), ['class' => 'row-fluid'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-list"></i> '.$w->buttonList, ['href' => './manage/my/order', 'class' => 'btn'] ),
		), ['class' => 'span12'] ),
	), ['class' => 'row-fluid'] ),
) );
