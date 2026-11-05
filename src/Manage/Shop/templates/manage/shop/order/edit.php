<?php

use CeusMedia\Bootstrap\Button\Link as LinkButton;
use CeusMedia\Bootstrap\Button\Toolbar as ButtonToolbar;
use CeusMedia\Bootstrap\Button\Group as ButtonGroup;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View_Manage_Shop_Order $view */
/** @var array<string,array<string|string>> $words */
/** @var object $order */

$baseUrl	= './manage/shop/order/setStatus/'.$order->orderId.'/';
$buttons	= [new LinkButton( './manage/shop/order', '', 'btn-small', 'arrow-left' )];

$wStates	= (object) $words['edit-states'];
$wEdit		= (object) $words['edit'];

$states	= [
	(object) [
		'enabled'	=> TRUE,
		'from'		=> [-5, -4, 3],
		'to'		=> -6,
		'label'		=> $wStates->refunded,
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	],
	(object) [
		'enabled'	=> TRUE,
		'from'		=> [4, 5, 6],
		'to'		=> -5,
		'label'		=> $wStates->complained,
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-left',
	],
	(object) [
		'enabled'	=> FALSE,
		'from'		=> [3],
		'to'		=> -4,
		'label'		=> $wStates->notDelivered,
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	],
	(object) [
		'enabled'	=> TRUE,
		'from'		=> [2],
		'to'		=> -3,
		'label'		=> $wStates->notPayed,
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	],
	(object) [
		'enabled'	=> TRUE,
		'from'		=> [2],
		'to'		=> -2,
		'label'		=> $wStates->cancelled,
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	],
	(object) [
		'enabled'	=> FALSE,
		'from'		=> [0],
		'to'		=> -1,
		'label'		=> $wStates->aborted,
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	],
	(object) [
		'enabled'	=> FALSE,
		'from'		=> [2],
		'to'		=> 1,
		'label'		=> $wStates->notPayed,
		'class'		=> 'btn-danger',
		'icon'		=> 'remove',
	],
	(object) [
		'enabled'	=> FALSE,
		'from'		=> [ -6, -2],
		'to'		=> 2,
		'label'		=> $wStates->ordered,
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-right',
	],
	(object) [
		'enabled'	=> TRUE,
		'from'		=> [-3, 2],
		'to'		=> 3,
		'label'		=> $wStates->payed,
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-right',
	],
	(object) [
		'enabled'	=> FALSE,
		'from'		=> [-4, 3],
		'to'		=> 4,
		'label'		=> $wStates->partlyDelivered,
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-right',
	],
	(object) [
		'enabled'	=> TRUE,
		'from'		=> [3, 4],
		'to'		=> 5,
		'label'		=> $wStates->delivered,
		'class'		=> 'btn-warning',
		'icon'		=> 'arrow-right',
	],
	(object) [
		'enabled'	=> TRUE,
		'from'		=> [5],
		'to'		=> 6,
		'label'		=> $wStates->completed,
		'class'		=> 'btn-success',
		'icon'		=> 'ok',
	],
];

foreach( $states as $status ){
	if( $status->enabled ){
		if( in_array( $order->status, $status->from ) ){
			$buttons[]	= new LinkButton(
				$baseUrl.$status->to,
				$status->label,
				'btn-small '.$status->class,
				$status->icon
			);
		}
	}
}
$buttons[]	= HtmlTag::create( 'a', '<i class="icon-question-sign icon-white"></i>', [
	'class'		=> 'btn btn-info btn-small fancybox-auto',
	'href'		=> $env->getConfig()->get( 'path.images' ).'states.png',
	'target'	=> '_blank',
] );

$buttons	= new ButtonToolbar( [new ButtonGroup( $buttons )] );


function renderDataList( array $keys, object $labels, object $data ): string
{
	$list	= [];
	foreach( $keys as $key ){
		if( isset( $data->$key ) && '' !== trim( $data->$key ?? '' ) ){
			$list[]	= HtmlTag::create( 'dt', $labels->$key );
			$list[]	= HtmlTag::create( 'dd', $data->$key );
		}
	}
	if( $list )
		return HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );
	return '';
}

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, (string) $order->status );

$labelsCustomer	= (object) $env->getLanguage()->getWords( 'manage/shop' )['customer'];
$keysCustomer	= ['institution', 'firstname', 'surname', 'country', 'region', 'city', 'postcode', 'street', 'email', 'phone'];
$panels	= [];
$panels[]	= '
	<div class="span4">
		<div class="content-panel">
			<h4>'.$wEdit->heading.'</h4>
			<div class="content-panel-inner">
				<dl class="dl-horizontal">
					<dt>'.$wEdit->labelOrderId.'</dt><dd>'.$order->orderId.'</dd>
					<dt>'.$wEdit->labelCustomerId.'</dt><dd>'.$order->userId.'</dd>
					<dt>'.$wEdit->labelDate.'</dt><dd>'.date( "d.m.Y", $order->createdAt ).' <small><em>'.date( "H:i:s", $order->createdAt ).'</em></small><dd>
					<dt>'.$wEdit->labelPayment.'</dt><dd>'.$order->paymentMethod.'</dd>
					<dt>'.$wEdit->labelStatus.'</dt><dd>'.$words['states'][$order->status].'<dd>
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
			<h4>'.$wEdit->headingDelivery.'</h4>
			<div class="content-panel-inner">
				'.renderDataList( $keysCustomer, $labelsCustomer, $order->customer->addressDelivery ).'
			</div>
		</div>
	</div>';
if( $order->customer && $order->customer->addressBilling )
	$panels[]	= '
	<div class="span4">
		<div class="content-panel">
			<h4>'.$wEdit->headingBilling.'</h4>
			<div class="content-panel-inner">
				'.renderDataList( $keysCustomer, $labelsCustomer, $order->customer->addressBilling ).'
			</div>
		</div>
	</div>';

$w		= (object) $words['positions'];
$rows	= [];
foreach( $order->positions as $position ){
	$url	= './'.$position->bridge->data->backendUriPath.'edit/'.$position->articleId;
	if( substr_count( $position->bridge->data->backendUriPath, "%s" ) )
		$url	= './'.sprintf( $position->bridge->data->backendUriPath, (string)$position->articleId );
	$link	= HtmlTag::create( 'a', $position->article->title, ['href' => $url] );

	$cellBridge		= HtmlTag::create( 'td', $position->bridge->data->title, ['class' => 'cell-position-bridge'] );
	$cellTitle		= HtmlTag::create( 'td', $link, ['class' => 'cell-position-title'] );
	$cellQuantity	= HtmlTag::create( 'td', $position->quantity, ['class' => 'cell-position-quantity'] );
	$cellStatus		= HtmlTag::create( 'td', new ButtonGroup( [
		new LinkButton(
			'./manage/shop/order/setPositionStatus/'.$position->positionId.'/1',
			$w->buttonOrdered,
			'btn-small btn-warning',
			'arrow-right',
			$order->status < 1 || $position->status != 0
		),
		new LinkButton(
			'./manage/shop/order/setPositionStatus/'.$position->positionId.'/2',
			$w->buttonDelivered,
			'btn-small btn-success',
			'ok',
			$order->status < 1 || $position->status == 2
		),
	] ), ['class' => 'cell-position-actions'] );

	$rowColor		= $position->status == 1 ? 'warning' : ( $position->status == 2 ? 'success' : 'error' );
	$cells			= [$cellQuantity, $cellTitle, $cellBridge, $cellStatus];
	$attributes		= ['class' => $rowColor];
	$rows[]			= HtmlTag::create( 'tr', $cells, $attributes );
}

$tableHeads		= HtmlElements::TableHeads( [
	$w->head_quantity,
	$w->head_article,
	$w->head_bridge,
	$w->head_status,
] );
$tableColumns	= HtmlElements::ColumnGroup( ['60', '', '220', '180'] );
$tableHead		= HtmlTag::create( 'thead', $tableHeads );
$tableBody		= HtmlTag::create( 'tbody', $rows );
$tableArticles	= HtmlTag::create( 'table', $tableColumns.$tableHead.$tableBody, ['class' => 'table table-condensed table-hover table-striped'] );

$linkBack	= '<a href="./manage/shop/order">&laquo;&nbsp;'.$wEdit->linkBack.'</a>';
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
	<h3><span class="muted">'.$wEdit->heading.'</span> <span>#'.$order->orderId.'</span></h3>
	<div class="row-fluid panels">
		'.join( $panels ).'
	</div>
	<div class="content-panel">
		<h4>'.$wEdit->headingArticles.'</h4>
		<div class="content-panel-inner">
			'.$tableArticles.'
		</div>
	</div>
</div>';
