<?php

$labelsCustomer	= $this->getWords( 'customer', 'manage/shop' );

$baseUrl	= './manage/shop/order/setStatus/'.$order->order_id.'/';
$buttons	= array( new CMM_Bootstrap_LinkButton( './manage/shop/order', '', 'btn-small', 'arrow-left' ) );

if( in_array( $order->status, array( 2 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."-3", 'nicht bezahlt', 'btn-small btn-danger', 'remove' );
if( in_array( $order->status, array( 2 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."-2", 'storniert', 'btn-small btn-danger', 'remove' );
if( in_array( $order->status, array( 0 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."-1", 'abbrechen', 'btn-small btn-danger', 'remove' );
if( in_array( $order->status, array( -6, -2 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."2", 'bestellt', 'btn-small btn-warning', 'arrow-right' );
if( in_array( $order->status, array( -3, 2 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."3", 'bezahlt', 'btn-small btn-warning', 'arrow-right' );
if( in_array( $order->status, array( -5 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."-6", 'erstattet', 'btn-small btn-danger', 'remove' );
if( in_array( $order->status, array( 3 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."-4", 'nicht zugestellt', 'btn-small btn-danger', 'remove' );
if( in_array( $order->status, array( -4, 3 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."4", 'teilweise', 'btn-small btn-warning', 'arrow-right' );
if( in_array( $order->status, array( -5, -4, 3, 4 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."5", 'zugestellt', 'btn-small btn-warning', 'arrow-right' );
if( in_array( $order->status, array( 4, 5, 6 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."-5", 'reklamiert', 'btn-small btn-warning', 'arrow-left' );
if( in_array( $order->status, array( 5 ) ) )
	$buttons[]	= new CMM_Bootstrap_LinkButton( $baseUrl."6", 'fertig!', 'btn-small btn-success', 'ok' );

	
$buttons	= new CMM_Bootstrap_ButtonToolbar( array( new CMM_Bootstrap_ButtonGroup( $buttons ) ) );

function renderDataList( $keys, $data, $labels ){
	$list	= array();
	foreach( $keys as $key ){
		if( isset( $data->$key ) && strlen( trim( $data->$key ) ) ){
			$list[]	= UI_HTML_Tag::create( 'dt', $labels->$key );
			$list[]	= UI_HTML_Tag::create( 'dd', $data->$key );
		}
	}
	if( $list )
		return UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );
}

$optStatus	= $words['states'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, (string)$order->status );

$panels	= array();
$panels[]	= '
	<div class="span4">
		<h4>Bestellung</h4>
		<dl class="dl-horizontal">
			<dt>Order-ID</dt><dd>'.$order->order_id.'</dd>
			<dt>Kunden-ID</dt><dd>'.$order->customer_id.'</dd>
			<dt>Datum</dt><dd>'.date( "d.m.Y", $order->created ).' <small><em>'.date( "H:i:s", $order->created ).'</em></small><dd>
			<dt>Status</dt><dd>'.$words['states'][$order->status].'<dd>
<!--			<dt>Status</dt><dd><select name="status" id="input_status">'.$optStatus.'</select><dd>-->
		</dl>
		'.$buttons.'
	</div>';
if( $order->customer )
	$panels[]	= '
	<div class="span4">
		<h4>Lieferanschrift</h4>
		'.renderDataList( array( 'firstname', 'lastname', 'email', 'phone', 'country', 'region', 'city', 'postcode', 'address' ), $order->customer, $labelsCustomer ).'
	</div>';
if( $order->customer && $order->customer->alternative )
	$panels[]	= '
	<div class="span4">
		<h4>Rechnungsanschrift</h4>
		'.renderDataList( array( 'billing_institution', 'billing_firstname', 'billing_lastname', 'billing_country', 'billing_region', 'billing_city', 'billing_postcode', 'billing_address', 'billing_tnr', 'billing_phone', 'billing_email' ), $order->customer, $labelsCustomer ).'
	</div>';

$w		= (object) $words['positions'];
$rows	= array();
foreach( $order->positions as $position ){
	$url	= './manage/catalog/article/edit/'.$position->article_id;
	$link	= UI_HTML_Tag::create( 'a', $position->article->title, array( 'href' => $url ) );

	$cellTitle		= UI_HTML_Tag::create( 'td', $link );
	$cellQuantity	= UI_HTML_Tag::create( 'td', $position->quantity );
	$cellStatus		= UI_HTML_Tag::create( 'td', new CMM_Bootstrap_ButtonGroup( array(
		new CMM_Bootstrap_LinkButton(
			'./manage/shop/order/setPositionStatus/'.$position->position_id.'/1',
			'bestellt',
			'btn-small btn-warning',
			'arrow-right',
			$order->status < 1 || $position->status != 0
		),
		new CMM_Bootstrap_LinkButton(
			'./manage/shop/order/setPositionStatus/'.$position->position_id.'/2',
			'geliefert',
			'btn-small btn-success',
			'ok',
			$order->status < 1 || $position->status == 2
		),
	) ) );
	
	$rowColor		= $position->status == 1 ? 'warning' : ( $position->status == 2 ? 'success' : 'error' );
	$cells			= array( $cellTitle, $cellQuantity, $cellStatus );
	$attributes		= array( 'class' => $rowColor );
	$rows[]			= UI_HTML_Tag::create( 'tr', $cells, $attributes );
}

$tableHeads		= UI_HTML_Elements::TableHeads( array(
	$w->head_article,
	$w->head_quantity,
	$w->head_status,
) );
$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '75%', '10%', '20%' ) );
$tableHead		= UI_HTML_Tag::create( 'thead', $tableHeads );
$tableBody		= UI_HTML_Tag::create( 'tbody', $rows );
$tableArticles	= UI_HTML_Tag::create( 'table', $tableColumns.$tableHead.$tableBody, array( 'class' => 'table table-condensed table-hover table-striped' ) );

$linkBack	= '<a href="./manage/shop/order">&laquo;&nbsp;zurück</a>';
#$linkBack	= new CMM_Bootstrap_LinkButton( './manage/shop/order', 'zurück', 'btn-small', 'arrow-left' );

return $this->renderMainTabs().'
<style>
.panels .dl-horizontal dt {
	width: 100px;
	}
.panels .dl-horizontal dd {
	margin-left: 120px;
	}
</style>
<div>
	<h3><span class="muted">Bestellung</span> <span>#'.$order->order_id.'</span></h3>
	<div class="row-fluid panels">
		'.join( $panels ).'
	</div>
	<h4>Artikel</h4>
	'.$tableArticles.'
</div>';
?>
