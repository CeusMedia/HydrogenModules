<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array<string,array<string,string>> $words */
/** @var array<object> $ordersTotal */
/** @var array<object> $ordersNotPayed */
/** @var array<object> $ordersNotDelivered */
/** @var array<object> $ordersNotFinished */

$w	= (object) $words['index'];

$panelOpenOrders	= '
<div class="content-panel">
	<h3>'.$w->headingOpen.'</h3>
	<div class="content-panel-inner">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="./manage/shop/order/filter?status[]=2">'.sprintf( $w->linkNotPayed, number_format( count( $ordersNotPayed ), 0, ',', '.' ) ).'</a></li>
			<li><a href="./manage/shop/order/filter?status[]=3&status[]=4">'.sprintf( $w->linkNotDelivered, number_format( count( $ordersNotDelivered ), 0, ',', '.' ) ).'</a></li>
			<li><a href="./manage/shop/order/filter?status[]=2&status[]=3&status[]=4&status[]=5">'.sprintf( $w->linkNotFinished, number_format( count( $ordersNotFinished ), 0, ',', '.' ) ).'</a></li>
		</ul>
	</div>
</div>';

$totalPrice	= 0;
$totalTaxed	= 0;
foreach( $ordersTotal as $order ){
	$totalPrice		+= $order->price;
	$totalTaxed		+= $order->priceTaxed;
}

$panelTotal	= '
<div class="content-panel">
	<h3>'.$w->headingTotal.'</h3>
	<div class="content-panel-inner">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="./manage/shop/order/filter?status[]=2&status[]=3&status[]=4&status[]=5&status[]=6">'.sprintf( $w->linkOrders, number_format( count( $ordersTotal ), 0, ',', '.' ) ).'</a></li>
			<li><a>'.sprintf( $w->priceNet, number_format( $totalPrice, 2, ',', '.' ) ).'</a></li>
			<li><a>'.sprintf( $w->priceGross, number_format( $totalTaxed, 2, ',', '.' ) ).'</a></li>
		</ul>
	</div>
</div>';

$panelEmpty	= '
<div class="content-panel">
	<h3>'.$w->headingEmpty.'</h3>
	<div class="content-panel-inner">
	</div>
</div>';

$tabs	= View_Manage_Shop::renderTabs( $env, '' );

return $tabs.'
<div class="row-fluid">
	<div class="span4">
		'.$panelTotal.'
	</div>
	<div class="span4">
		'.$panelOpenOrders.'
	</div>
	<div class="span4">
		<!--'.$panelEmpty.'-->
	</div>
</div>';
/*
return $tabs.'
<div class="row-fluid">
	<div class="span4">
		'.$panelOpenOrders.'
		'.$panelTotal.'
		<!--'.$panelEmpty.'-->
	</div>
	<div class="span8" style="height: 500px">
		<div id="map1" data-zoom="2" data-longitude="12.3936349" data-latitude="51.3417825"></div>
	</div>
</div>
<script>
var markers	= '.json_encode( $markers ).';
$(document).ready(function(){
	var map = loadMap("map1");
	for(var i=0; i<markers.length; i++){
		addMarker(map, markers[i].lat, markers[i].lon);
	}
});

</script>
<style>
.UI_Map {
	height: 100%;
	}

</style>

';
*/