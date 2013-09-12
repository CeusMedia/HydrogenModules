<?php

$words	= (object) $words;
$w		= (object) $words->index;

$statusIcons		= array(
	-6		=> 'remove',
	-5		=> 'arrow-left',
	-4		=> 'remove',
	-3		=> 'remove',
	-2		=> 'remove',
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'arrow-right',
	2		=> 'arrow-right',
	3		=> 'arrow-right',
	4		=> 'arrow-right',
	5		=> 'arrow-right',
	6		=> 'ok',
);


$listOrders	= array();
foreach( $orders as $order ){
	$attributes		= array( 'href' => './manage/shop/order/edit/'.$order->order_id );
	$iconStatus		= UI_HTML_Tag::create( 'i', "", array( 'class' => 'icon-'.$statusIcons[$order->status] ) );
	$link	= UI_HTML_Tag::create( 'a', "#".$order->order_id, $attributes );

	$customer	= $order->customer ? $order->customer->lastname.', '.$order->customer->firstname : "-";
	$customer	= UI_HTML_Tag::create( 'div', $customer, array( 'class' => 'autocut' ) );
	$customer	= UI_HTML_Tag::create( 'a', $customer, $attributes );
	$link		= UI_HTML_Tag::create( 'small', "#".$order->order_id, array( 'class' => 'muted' ) );
	$cellLink		= UI_HTML_Tag::create( 'td', $link );
	$cellCustomer	= UI_HTML_Tag::create( 'td', $customer );
	$cellStatus		= UI_HTML_Tag::create( 'td', '<small>'./*$iconStatus.' '.*/$words->states[$order->status].'</small>' );
	$cellCreated	= UI_HTML_Tag::create( 'td', '<small>'.( $order->created ? date( 'd.m.Y', $order->created ) : "-" ).'</small>' );
	$cellModified	= UI_HTML_Tag::create( 'td', '<small>'.( $order->edited ? date( 'd.m.Y', $order->edited ) : '-' ).'</small>' );
	$rowColor		= "info";
	if( in_array( $order->status, array( 6 ) ) )
		$rowColor	= 'success';
	else if( in_array( $order->status, array( -5, 2, 3, 4, 5 ) ) )
		$rowColor	= 'warning';
	else if( in_array( $order->status, array( -6, -4, -3, -2, -1 ) ) )
		$rowColor	= 'error';
	$cells			= array( $cellLink, $cellCustomer, $cellStatus, $cellCreated, $cellModified );
	$attributes		= array( 'class' => $rowColor );
	$listOrders[]	= UI_HTML_Tag::create( 'tr', $cells, $attributes );
}
$tableRows		= join( $listOrders );
$tableHeads		= UI_HTML_Elements::TableHeads( array(
	$w->columnId,
	$w->columnCustomer,
	$w->columnStatus,
	$w->columnCreated,
	$w->columnModified,
) );
$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '5%', '46%', '15%', '12%', '12%' ) );
$tableHead		= UI_HTML_Tag::create( 'thead', $tableHeads );
$tableBody		= UI_HTML_Tag::create( 'tbody', $tableRows );
$listOrders		= UI_HTML_Tag::create( 'table', $tableColumns.$tableHead.$tableBody, array( 'class' => 'table table-condensed table-hover table-striped' ) );

$pagination		= new CMM_Bootstrap_PageControl( './manage/shop/order', $pageNr, ceil( $total / 20 ) );

return '
<div class="order-list-content">
	'.$this->renderMainTabs().'
<!--	<h3>'.$w->heading.'</h3>-->
	<div class="row-fluid">
		<div class="span3">
			<h4>Filter</h4>
			'.$view->loadTemplateFile( 'manage/shop/order/filter.php' ).'
		</div>
		<div class="span9">
			'.$listOrders.'
			'.$pagination.'
		</div>
	</div>
</div>
';
?>
