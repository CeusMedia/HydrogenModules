<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array<string,array<string|string>> $words */
/** @var object[] $orders */
/** @var int $pageNr */
/** @var int $total */

$w		= (object) $words['index'];

$statusIcons		= [
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
];

$colorMap	= [
	Model_Shop_Order::STATUS_COMPLETED			=> 'success',
	Model_Shop_Order::STATUS_COMPLAINED			=> 'warning',
	Model_Shop_Order::STATUS_ORDERED			=> 'warning',
	Model_Shop_Order::STATUS_PAYED				=> 'warning',
	Model_Shop_Order::STATUS_PARTLY_DELIVERED	=> 'warning',
	Model_Shop_Order::STATUS_DELIVERED			=> 'warning',
	Model_Shop_Order::STATUS_REFUNDED			=> 'error',
	Model_Shop_Order::STATUS_NOT_DELIVERED		=> 'error',
	Model_Shop_Order::STATUS_NOT_PAYED			=> 'error',
	Model_Shop_Order::STATUS_REVERSED			=> 'error',
	Model_Shop_Order::STATUS_CANCELLED			=> 'error'
];

$listOrders	= [];
foreach( $orders as $order ){
	$attributes		= ['href' => './manage/shop/order/edit/'.$order->orderId];
	$iconStatus		= HtmlTag::create( 'i', "", ['class' => 'icon-'.$statusIcons[$order->status]] );
	$link	= HtmlTag::create( 'a', "#".$order->orderId, $attributes );
	$customer	= $order->customer ? $order->customer->addressDelivery->surname.', '.$order->customer->addressDelivery->firstname : "-";
	$customer	= HtmlTag::create( 'div', $customer, ['class' => 'autocut'] );
	$customer	= HtmlTag::create( 'a', $customer, $attributes );
	$link		= HtmlTag::create( 'small', "#".$order->orderId, ['class' => 'muted'] );
	$cellLink		= HtmlTag::create( 'td', $link );
	$cellCustomer	= HtmlTag::create( 'td', $customer );
	$cellStatus		= HtmlTag::create( 'td', '<small>'./*$iconStatus.' '.*/$words['states'][$order->status].'</small>' );
	$cellCreated	= HtmlTag::create( 'td', '<small>'.( $order->createdAt ? date( 'd.m.Y', $order->createdAt ) : "-" ).'</small>' );
	$cellModified	= HtmlTag::create( 'td', '<small>'.( $order->modifiedAt ? date( 'd.m.Y', $order->modifiedAt ) : '-' ).'</small>' );
	$cells			= [$cellLink, $cellCustomer, $cellStatus, $cellCreated, $cellModified];
	$attributes		= ['class' => $colorMap[$order->status] ?? 'info'];
	$listOrders[]	= HtmlTag::create( 'tr', $cells, $attributes );
}
$tableRows		= join( $listOrders );
$tableHeads		= HtmlElements::TableHeads( [
	$w->columnId,
	$w->columnCustomer,
	$w->columnStatus,
	$w->columnCreated,
	$w->columnModified,
] );
$tableColumns	= HtmlElements::ColumnGroup( ['5%', '46%', '15%', '12%', '12%'] );
$tableHead		= HtmlTag::create( 'thead', $tableHeads );
$tableBody		= HtmlTag::create( 'tbody', $tableRows );
$listOrders		= HtmlTag::create( 'table', $tableColumns.$tableHead.$tableBody, ['class' => 'table table-condensed table-hover table-striped'] );

$pagination		= new PageControl( './manage/shop/order', $pageNr, ceil( $total / 20 ) );
$tabs			= View_Manage_Shop::renderTabs( $env, 'order' );

return '
<div class="order-list-content">
	'.$tabs.'
<!--	<h3>'.$w->heading.'</h3>-->
	<div class="row-fluid">
		<div class="span3">
			'.$view->loadTemplateFile( 'manage/shop/order/filter.php' ).'
		</div>
		<div class="span9">
			<div class="content-panel">
				<div class="content-panel-inner">
					'.$listOrders.'
					'.$pagination.'
				</div>
			</div>
		</div>
	</div>
</div>
';
