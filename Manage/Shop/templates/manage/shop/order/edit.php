<?php

$labelsCustomer	= $this->getWords( 'customer', 'manage/shop' );

$baseUrl	= './manage/shop/order/setStatus/'.$order->orderId.'/';
$buttons	= array( new \CeusMedia\Bootstrap\LinkButton( './manage/shop/order', '', 'btn-small', 'arrow-left' ) );

$states	= array(
	(object) array(
		'enabled'	=> TRUE,
		'from'		=> array( -5, -4, 2 ),
		'to'		=> -6,
		'label'		=> 'erstattet',
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	),
	(object) array(
		'enabled'	=> FALSE,
		'from'		=> array( 4, 5, 6 ),
		'to'		=> -5,
		'label'		=> 'reklamiert',
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-left',
	),
	(object) array(
		'enabled'	=> FALSE,
		'from'		=> array( 3 ),
		'to'		=> -4,
		'label'		=> 'nicht zugestellt',
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	),
	(object) array(
		'enabled'	=> FALSE,
		'from'		=> array( 2 ),
		'to'		=> -3,
		'label'		=> 'nicht bezahlt',
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	),
	(object) array(
		'enabled'	=> TRUE,
		'from'		=> array( 2 ),
		'to'		=> -2,
		'label'		=> 'storniert',
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	),
	(object) array(
		'enabled'	=> FALSE,
		'from'		=> array( 0 ),
		'to'		=> -1,
		'label'		=> 'abbrechen',
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	),
	(object) array(
		'enabled'	=> FALSE,
		'from'		=> array( 2 ),
		'to'		=> 1,
		'label'		=> 'nicht bezahlt',
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	),
	(object) array(
		'enabled'	=> FALSE,
		'from'		=> array(  -6, -2 ),
		'to'		=> 2,
		'label'		=> 'bestellt',
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-right',
	),
	(object) array(
		'enabled'	=> TRUE,
		'from'		=> array( -3, 2 ),
		'to'		=> 3,
		'label'		=> 'bezahlt',
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-right',
	),
	(object) array(
		'enabled'	=> FALSE,
		'from'		=> array( -4, 3 ),
		'to'		=> 4,
		'label'		=> 'teilweise',
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-right',
	),
	(object) array(
		'enabled'	=> TRUE,
		'from'		=> array( 3, 4 ),
		'to'		=> 5,
		'label'		=> 'zugestellt',
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-right',
	),
	(object) array(
		'enabled'	=> TRUE,
		'from'		=> array( 5 ),
		'to'		=> 6,
		'label'		=> 'fertig!',
		'class'		=> 'btn-success',
		'icon'		=> 'ok',
	),
);

foreach( $states as $status ){
	if( $status->enabled ){
		if( in_array( $order->status, $status->from ) ){
			$buttons[]	= new \CeusMedia\Bootstrap\LinkButton(
				$baseUrl.$status->to,
				$status->label,
				'btn-small '.$status->class,
				$status->icon
			);
		}
	}
}
$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="icon-question-sign icon-white"></i>', array(
	'class'		=> 'btn btn-info btn-small fancybox-auto',
	'href'		=> $env->getConfig()->get( 'path.images' ).'states.png',
	'target'	=> '_blank',
) );

$buttons	= new \CeusMedia\Bootstrap\ButtonToolbar( array( new \CeusMedia\Bootstrap\ButtonGroup( $buttons ) ) );


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
		<div class="content-panel">
			<h4>Bestellung</h4>
			<div class="content-panel-inner">
				<dl class="dl-horizontal">
					<dt>Order-ID</dt><dd>'.$order->orderId.'</dd>
					<dt>Kunden-ID</dt><dd>'.$order->customerId.'</dd>
					<dt>Datum</dt><dd>'.date( "d.m.Y", $order->createdAt ).' <small><em>'.date( "H:i:s", $order->createdAt ).'</em></small><dd>
					<dt>Status</dt><dd>'.$words['states'][$order->status].'<dd>
		<!--			<dt>Status</dt><dd><select name="status" id="input_status">'.$optStatus.'</select><dd>-->
				</dl>
				'.$buttons.'
			</div>
		</div>
	</div>';
if( $order->customer )
	$panels[]	= '
	<div class="span4">
		<div class="content-panel">
			<h4>Lieferanschrift</h4>
			<div class="content-panel-inner">
				'.renderDataList( array( 'institution', 'firstname', 'lastname', 'email', 'phone', 'country', 'region', 'city', 'postcode', 'address' ), $order->customer, $labelsCustomer ).'
			</div>
		</div>
	</div>';
if( $order->customer && $order->customer->alternative )
	$panels[]	= '
	<div class="span4">
		<div class="content-panel">
			<h4>Rechnungsanschrift</h4>
			<div class="content-panel-inner">
				'.renderDataList( array( 'billing_institution', 'billing_firstname', 'billing_lastname', 'billing_country', 'billing_region', 'billing_city', 'billing_postcode', 'billing_address', 'billing_tnr', 'billing_phone', 'billing_email' ), $order->customer, $labelsCustomer ).'
			</div>
		</div>
	</div>';

$w		= (object) $words['positions'];
$rows	= array();
foreach( $order->positions as $position ){
	$url	= './'.$position->bridge->data->backendUriPath.'edit/'.$position->articleId;
	if( substr_count( $position->bridge->data->backendUriPath, "%s" ) )
		$url	= './'.sprintf( $position->bridge->data->backendUriPath, (string)$position->articleId );
	$link	= UI_HTML_Tag::create( 'a', $position->article->title, array( 'href' => $url ) );

	$cellBridge		= new UI_HTML_Tag( 'td', $position->bridge->data->title, array( 'class' => 'cell-position-bridge' ) );
	$cellTitle		= new UI_HTML_Tag( 'td', $link, array( 'class' => 'cell-position-title' ) );
	$cellQuantity	= new UI_HTML_Tag( 'td', $position->quantity, array( 'class' => 'cell-position-quantity' ) );
	$cellStatus		= new UI_HTML_Tag( 'td', new \CeusMedia\Bootstrap\ButtonGroup( array(
		new \CeusMedia\Bootstrap\LinkButton(
			'./manage/shop/order/setPositionStatus/'.$position->positionId.'/1',
			'bestellt',
			'btn-small btn-warning',
			'arrow-right',
			$order->status < 1 || $position->status != 0
		),
		new \CeusMedia\Bootstrap\LinkButton(
			'./manage/shop/order/setPositionStatus/'.$position->positionId.'/2',
			'geliefert',
			'btn-small btn-success',
			'ok',
			$order->status < 1 || $position->status == 2
		),
	) ), array( 'class' => 'cell-position-actions' ) );

	$rowColor		= $position->status == 1 ? 'warning' : ( $position->status == 2 ? 'success' : 'error' );
	$cells			= array( $cellQuantity, $cellTitle, $cellBridge, $cellStatus );
	$attributes		= array( 'class' => $rowColor );
	$rows[]			= UI_HTML_Tag::create( 'tr', $cells, $attributes );
}

$tableHeads		= UI_HTML_Elements::TableHeads( array(
	$w->head_quantity,
	$w->head_article,
	$w->head_bridge,
	$w->head_status,
) );
$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '60', '', '220', '180' ) );
$tableHead		= UI_HTML_Tag::create( 'thead', $tableHeads );
$tableBody		= UI_HTML_Tag::create( 'tbody', $rows );
$tableArticles	= UI_HTML_Tag::create( 'table', $tableColumns.$tableHead.$tableBody, array( 'class' => 'table table-condensed table-hover table-striped' ) );

$linkBack	= '<a href="./manage/shop/order">&laquo;&nbsp;zurück</a>';
#$linkBack	= new \CeusMedia\Bootstrap\LinkButton( './manage/shop/order', 'zurück', 'btn-small', 'arrow-left' );

$tabs		= View_Manage_Shop::renderTabs( $env, 'order' );

return $tabs.'
<style>
.panels .dl-horizontal dt {
	width: 100px;
	}
.panels .dl-horizontal dd {
	margin-left: 120px;
	}
table.table td.cell-position-bridge {
	font-size: 0.9em;
	color: rgba(51, 51, 51, 0.85);
	}
table.table td.cell-position-quantity {
	font-size: 1.1em;
	text-align: right;
	padding-right: 0.8em;
	font-weight: bold;
	}
</style>
<div>
	<h3><span class="muted">Bestellung</span> <span>#'.$order->orderId.'</span></h3>
	<div class="row-fluid panels">
		'.join( $panels ).'
	</div>
	<div class="content-panel">
		<h4>Artikel</h4>
		<div class="content-panel-inner">
			'.$tableArticles.'
		</div>
	</div>
</div>';
?>
