<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//print_m( $paymentBackends );die;


$wordsShop	= $env->getLanguage()->getWords( 'shop' );

/*  --  PANEL: FACTS  --  */
$status	= $wordsShop['statuses-order'][$order->status];
$status	= HtmlTag::create( 'acronym', $status, array( 'title' => $wordsShop['statuses-order-title'][$order->status] ) );
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
		), array( 'style' => 'font-size: 1.1em' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  PANEL: POSITIONS  --  */
$positions	= HtmlTag::create( 'div', 'Keine Produkte in der Bestellung', array( 'class' => 'alert alert-info' ) );
if( $order->positions ){
	$helper	= new View_Helper_Shop_CartPositions( $env );
	$helper->setPositions( $order->positions );
	$positions	= $helper->render();
}
$panelPositions	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Bestellter Warenkorb' ),
	HtmlTag::create( 'div', $positions, array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  PANEL: BILLING ADDRESS  --  */
$helper	= new View_Helper_Shop_AddressView( $env );
$helper->setAddress( $order->customer->addressBilling );
$panelBilling	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Rechnungsadresse' ),
	HtmlTag::create( 'div', $helper->render(), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  PANEL: DELIVERY  --  */
$helper->setAddress( $order->customer->addressDelivery );
$panelDelivery	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Lieferadresse' ),
	HtmlTag::create( 'div', $helper->render(), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  PANEL: DATA  --  */
$panelData	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Daten' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			print_m( $order, NULL, NULL, TRUE ),
		), array( 'style' => 'height: 500px; overflow-y: auto' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

/*  --  GRID  --  */
return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			$panelFacts,
		), array( 'class' => 'span4' ) ),
		HtmlTag::create( 'div', array(
			$panelDelivery,
		), array( 'class' => 'span4' ) ),
		HtmlTag::create( 'div', array(
			$panelBilling,
		), array( 'class' => 'span4' ) ),
	), array( 'class' => 'row-fluid' ) ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			$panelPositions,
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-list"></i> zur Liste', array( 'href' => './manage/my/order', 'class' => 'btn' ) ),
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
) );
