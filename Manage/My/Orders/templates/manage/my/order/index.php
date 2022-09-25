<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$wordsShop	= $env->getLanguage()->getWords( 'shop' );

$table	= HtmlTag::create( 'div', 'Noch keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $orders ){
	$rows	= [];
	foreach( $orders as $item ){
		$link	= HtmlTag::create( 'a', 'Bestellung am '.date( 'd.m.Y', $item->createdAt ), array(
			'href'	=> './manage/my/order/view/'.$item->orderId,
		) );
		$status	= $wordsShop['statuses-order'][$item->status];
		$status	= HtmlTag::create( 'acronym', $status, array( 'title' => $wordsShop['statuses-order-title'][$item->status] ) );
		$method	= $item->paymentMethod;
		if( isset( $paymentBackends[$item->paymentMethod] ) )
			$method	= $paymentBackends[$item->paymentMethod]->title;
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', number_format( $item->priceTaxed ).' '.$item->currency ),
			HtmlTag::create( 'td', $method ),
			HtmlTag::create( 'td', $status ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( '', '100px', '200px', '100px' );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( 'Bestellung', 'Preis', 'Bezahlmethode', 'Zustand' ) ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-striped table-fixed' ) );
}

$pagination	= new \CeusMedia\Bootstrap\PageControl( 'manage/my/order/view', $page, $pages );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Meine Bestellungen' ),
	HtmlTag::create( 'div', array(
		$table,
		HtmlTag::create( 'div', $pagination, array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
