<?php

$wordsShop	= $env->getLanguage()->getWords( 'shop' );

$table	= UI_HTML_Tag::create( 'div', 'Noch keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $orders ){
	$rows	= array();
	foreach( $orders as $item ){
		$link	= UI_HTML_Tag::create( 'a', 'Bestellung am '.date( 'd.m.Y', $item->createdAt ), array(
			'href'	=> './manage/my/order/view/'.$item->orderId,
		) );
		$status	= $wordsShop['statuses-order'][$item->status];
		$status	= UI_HTML_Tag::create( 'acronym', $status, array( 'title' => $wordsShop['statuses-order-title'][$item->status] ) );
		$method	= $item->paymentMethod;
		if( isset( $paymentBackends[$item->paymentMethod] ) )
			$method	= $paymentBackends[$item->paymentMethod]->title;
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', number_format( $item->priceTaxed ).' '.$item->currency ),
			UI_HTML_Tag::create( 'td', $method ),
			UI_HTML_Tag::create( 'td', $status ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '', '100px', '200px', '100px' );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Bestellung', 'Preis', 'Bezahlmethode', 'Zustand' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-striped table-fixed' ) );
}

$pagination	= new \CeusMedia\Bootstrap\PageControl( 'manage/my/order/view', $page, $pages );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Meine Bestellungen' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', $pagination, array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
