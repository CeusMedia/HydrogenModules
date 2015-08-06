<?php

$panelOpenOrders	= '
		<div class="content-panel">
			<h3>Offene Bestellungen</h3>
			<div class="content-panel-inner">
				<ul class="nav nav-pills nav-stacked">
					<li><a href="./manage/shop/order/filter?status[]=2">'.count( $ordersNotPayed ).' nicht bezahlt</a></li>
					<li><a href="./manage/shop/order/filter?status[]=3&status[]=4">'.count( $ordersNotDelievered ).' nicht zugestellt</a></li>
					<li><a href="./manage/shop/order/filter?status[]=2&status[]=3&status[]=4&status[]=5">'.count( $ordersNotFinished ).' nicht abgeschlossen</a></li>
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
					<li><a href="./manage/shop/order/filter?status[]=2&status[]=3&status[]=4&status[]=5&status[]=6">'.count( $ordersTotal ).' Bestellungen</a></li>
					<li><a>'.$totalPrice.'€ netto</a></li>
					<li><a>'.$totalTaxed.'€ brutto</a></li>
				</ul>
			</div>
		</div>';


$panelEmpty	= '
		<div class="content-panel">
			<h3>Leeres Panel</h3>
			<div class="content-panel-inner">
			</div>
		</div>';


$geocoder	= new Net_API_Google_Maps_Geocoder( "" );
$geocoder->setCachePath( 'contents/cache/' );
$markers	= array();
foreach( $customers as $customer ){
	$tags		= $geocoder->getGeoTags( $customer->address.', '.$customer->city.', '.$customer->country );
	$markers[]	= array( 'lon' => $tags['longitude'], 'lat' => $tags['latitude'] );
//	$markers[]	= array( 'lat' => "51.3417825", 'lon' => "12.3936349" );
}

$tabs	= View_Manage_Shop::renderTabs( $env, '' );
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
