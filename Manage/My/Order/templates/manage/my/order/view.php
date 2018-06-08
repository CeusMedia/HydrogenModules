<?php

//print_m( $paymentBackends );die;


$wordsShop	= $env->getLanguage()->getWords( 'shop' );

/*  --  PANEL: FACTS  --  */
$status	= $wordsShop['statuses-order'][$order->status];
$status	= UI_HTML_Tag::create( 'acronym', $status, array( 'title' => $wordsShop['statuses-order-title'][$order->status] ) );
$method	= $order->paymentMethod;
if( isset( $paymentBackends[$order->paymentMethod] ) )
	$method	= $paymentBackends[$order->paymentMethod]->title;
$panelFacts	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Fakten' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'ul', array(
			UI_HTML_Tag::create( 'li', sprintf( '%d Artikel im Warenkorb', count( $order->positions ) ) ),
			UI_HTML_Tag::create( 'li', 'Datum: '.date( 'd.m.Y', $order->createdAt ) ),
			UI_HTML_Tag::create( 'li', 'Preis: '.$order->priceTaxed.' '.$order->currency ),
			UI_HTML_Tag::create( 'li', 'Zustand: '.$status ),
			UI_HTML_Tag::create( 'li', 'Bezahlung: '.$method ),
		), array( 'style' => 'font-size: 1.1em' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  PANEL: POSITIONS  --  */
$positions	= UI_HTML_Tag::create( 'div', 'Keine Produkte in der Bestellung', array( 'class' => 'alert alert-info' ) );
if( $order->positions ){
	$helper	= new View_Helper_Shop_CartPositions( $env );
	$helper->setPositions( $order->positions );
	$positions	= $helper->render();
}
$panelPositions	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Bestellter Warenkorb' ),
	UI_HTML_Tag::create( 'div', $positions, array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  PANEL: BILLING ADDRESS  --  */
$helper	= new View_Helper_Shop_AddressView( $env );
$helper->setAddress( $order->customer->addressBilling );
$panelBilling	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Rechnungsadresse' ),
	UI_HTML_Tag::create( 'div', $helper->render(), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  PANEL: DELIVERY  --  */
$helper->setAddress( $order->customer->addressDelivery );
$panelDelivery	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Lieferadresse' ),
	UI_HTML_Tag::create( 'div', $helper->render(), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  PANEL: DATA  --  */
$panelData	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Daten' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			print_m( $order, NULL, NULL, TRUE ),
		), array( 'style' => 'height: 500px; overflow-y: auto' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  GRID  --  */
return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			$panelFacts,
		), array( 'class' => 'span4' ) ),
		UI_HTML_Tag::create( 'div', array(
			$panelDelivery,
		), array( 'class' => 'span4' ) ),
		UI_HTML_Tag::create( 'div', array(
			$panelBilling,
		), array( 'class' => 'span4' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			$panelPositions,
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-list"></i> zur Liste', array( 'href' => './manage/my/order', 'class' => 'btn' ) ),
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
) );
