<?php

$panelOpenOrders	= '
<div class="content-panel">
	<h3>Offen</h3>
	<div class="content-panel-inner">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="./manage/shop/order/filter?status[]=2">'.number_format( count( $ordersNotPayed ), 0, ',', '.' ).' nicht bezahlt</a></li>
			<li><a href="./manage/shop/order/filter?status[]=3&status[]=4">'.number_format( count( $ordersNotDelievered ), 0, ',', '.' ).' nicht zugestellt</a></li>
			<li><a href="./manage/shop/order/filter?status[]=2&status[]=3&status[]=4&status[]=5">'.number_format( count( $ordersNotFinished ), 0, ',', '.' ).' nicht abgeschlossen</a></li>
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
	<h3>Gesamt</h3>
	<div class="content-panel-inner">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="./manage/shop/order/filter?status[]=2&status[]=3&status[]=4&status[]=5&status[]=6">'.number_format( count( $ordersTotal ), 0, ',', '.' ).' Bestellungen</a></li>
			<li><a>'.number_format( $totalPrice, 2, ',', '.' ).' € netto</a></li>
			<li><a>'.number_format( $totalTaxed, 2, ',', '.' ).' € brutto</a></li>
		</ul>
	</div>
</div>';

$panelEmpty	= '
<div class="content-panel">
	<h3>Leeres Panel</h3>
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
?>
