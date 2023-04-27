<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//print_m( $paymentBackends );die;


$wordsShop	= $env->getLanguage()->getWords( 'shop' );

/*  --  PANEL: FACTS  --  */
$status	= $wordsShop['statuses-order'][$order->status];
$status	= HtmlTag::create( 'acronym', $status, ['title' => $wordsShop['statuses-order-title'][$order->status]] );
$method	= $order->paymentMethod;
if( isset( $paymentBackends[$order->paymentMethod] ) )
	$method	= $paymentBackends[$order->paymentMethod]->title;
$panelFacts	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Fakten' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'ul', array(
			HtmlTag::create( 'li', sprintf( '%d Artikel im Warenkorb', count( $order->positions ) ) ),
			HtmlTag::create( 'li', 'Datum: '.date( 'd.m.Y', $order->createdAt ) ),
			HtmlTag::create( 'li', 'Preis: '.$order->priceTaxed.' '.$order->currency ),
			HtmlTag::create( 'li', 'Zustand: '.$status ),
			HtmlTag::create( 'li', 'Bezahlung: '.$method ),
		), ['style' => 'font-size: 1.1em'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  PANEL: POSITIONS  --  */
$positions	= HtmlTag::create( 'div', 'Keine Produkte in der Bestellung', ['class' => 'alert alert-info'] );
if( $order->positions ){
	$helper	= new View_Helper_Shop_CartPositions( $env );
	$helper->setPositions( $order->positions );
	$positions	= $helper->render();
}
$panelPositions	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Bestellter Warenkorb' ),
	HtmlTag::create( 'div', $positions, ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  PANEL: BILLING ADDRESS  --  */
$helper	= new View_Helper_Shop_AddressView( $env );
$helper->setAddress( $order->customer->addressBilling );
$panelBilling	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Rechnungsadresse' ),
	HtmlTag::create( 'div', $helper->render(), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  PANEL: DELIVERY  --  */
$helper->setAddress( $order->customer->addressDelivery );
$panelDelivery	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Lieferadresse' ),
	HtmlTag::create( 'div', $helper->render(), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

/*  --  PANEL: DATA  --  */
$panelData	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Daten' ),
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
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-list"></i> zur Liste', ['href' => './manage/my/order', 'class' => 'btn'] ),
		), ['class' => 'span12'] ),
	), ['class' => 'row-fluid'] ),
) );
