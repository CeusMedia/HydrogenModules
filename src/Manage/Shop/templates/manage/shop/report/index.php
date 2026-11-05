<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array<object> $ordersPerYear */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index'];

$dataYearsPieOrders	= [];
foreach( $ordersPerYear as $nr => $entry )
	$dataYearsPieOrders[]	= [(string)$entry->year, (int)$entry->orders];

$dataYearsPieTurnover	= [];
foreach( $ordersPerYear as $nr => $entry )
	$dataYearsPieTurnover[]	= [(string)$entry->year, ['v' => (int)$entry->turnover, 'f' => (int) $entry->turnover." €"]];


$sumOrders		= 0;
$sumTurnover	= 0;
$dataYearsTable	= [];
foreach( $ordersPerYear as $nr => $entry ){
	$sumOrders		+= (float)$entry->orders;
	$sumTurnover	+= (float)$entry->turnover;
	$dataYearsTable[(string)  $entry->year]	= [
		(string) $entry->year,
		(int) $entry->orders,
 		[
			'v'	=> (float) $entry->turnover,
			'f'	=> number_format( $entry->turnover, 0, ",", "." )." €"
		]
	];
}

$factor	= ( date( "L" ) ? 366 : 365)  / date( "z" );


$dataYearsTable["Summe"]	= [
	$w->labelSum,
	[
		'v'	=> (float) $sumOrders,
		'f'	=> number_format( $sumOrders, 0, ",", "." )
	],
	[
		'v'	=> (float) $sumTurnover,
		'f'	=> number_format( $sumTurnover, 0, ",", "." )." €"
	]
];

$dataYearsTable["Trend"]	= [
	$w->labelTrend,
	round( $dataYearsTable[$entry->year][1] * $factor ),
	[
		'v'	=> (float) $dataYearsTable[$entry->year][2]['v'] * $factor,
		'f'	=> number_format( $dataYearsTable[$entry->year][2]['v'] * $factor, 0, ",", "." )." €"
	]
];

$sum	= 0;
$dataYearsChartOrders	= [[$w->labelYear, $w->labelOrders, $w->labelTrend]];
foreach( $ordersPerYear as $nr => $entry ){
//	if( $entry->year == date( "Y" ) )
//		$entry->orders	*= $factor;
	$sum		+= $entry->orders;
	$dataYearsChartOrders[]	= [
		(string) $entry->year,
		(int) $entry->orders,
		(int) ( $sum / ( $nr + 1 ) )
	];
}

$sum	= 0;
$dataYearsChartTurnover	= [[$w->labelYear, $w->labelTurnover, $w->labelTrend]];
foreach( $ordersPerYear as $nr => $entry ){
//	if( $entry->year == date( "Y" ) )
//		$entry->turnover	*= $factor;
	$sum		+= (int) $entry->turnover;
	$dataYearsChartTurnover[]	= [
		(string) $entry->year,
		(int) $entry->turnover,
		(int) ( $sum / ( $nr + 1 ) )
	];
}

$selector	= '';
/*if( count( $bridges ) > 0 ){
	$optBridge	= HtmlElements::Options( $bridges, $bridgeId );
	$selector	= '
	<form action="./manage/shop/report" method="get" class="form-inline">
		<label for="input_bridgeId">Katalog</label>
		<select name="bridgeId" id="input_bridgeId" onchange="this.form.submit()">'.$optBridge.'</select>
	</form>';
}
*/
$tabs	= View_Manage_Shop::renderTabs( $env, 'report' );

return $tabs.$selector.'
<h3>'.$w->heading.'</h3>
<div class="row-fluid">
	<div class="span8">
		<div id="chart_years_chart_orders"></div>
		<div id="chart_years_chart_turnover"></div>
	</div>
	<div class="span4" data-style="border: 1px solid #BBBBBB;">
		<div id="chart_years_table"></div>
	</div>
</div>
<h4>'.$w->headingDistribution.'</h4>
<div class="row-fluid">
	<div class="span6">
		<div id="chart_years_pie_orders"></div>
	</div>
	<div class="span6">
		<div id="chart_years_pie_turnover"></div>
	</div>
</div>
<script>

	let dataYearsTable			= '.json_encode( array_values( $dataYearsTable ) ).';
	let dataYearsPieOrders		= '.json_encode( $dataYearsPieOrders ).';
	let dataYearsPieTurnover	= '.json_encode( $dataYearsPieTurnover ).';
	let dataYearsChartOrders	= '.json_encode( $dataYearsChartOrders ).';
	let dataYearsChartTurnover	= '.json_encode( $dataYearsChartTurnover ).';

	// Load the Visualization API and the piechart package.
	google.load("visualization", "1.0", {"packages":["corechart", "table"]});

	// Set a callback to run when the Google Visualization API is loaded.
	google.setOnLoadCallback(drawYearsTable);
	google.setOnLoadCallback(drawYearsChartOrders);
	google.setOnLoadCallback(drawYearsChartTurnover);
	google.setOnLoadCallback(drawYearsPieOrders);
	google.setOnLoadCallback(drawYearsPieTurnover);

	function drawYearsPieOrders() {
		let chart	= new google.visualization.PieChart(document.getElementById("chart_years_pie_orders"));
		let data	= new google.visualization.DataTable();
		data.addColumn("string", '.json_encode( $w->labelYear ).');
		data.addColumn("number", '.json_encode( $w->labelOrders ).');
		data.addRows(dataYearsPieOrders);
		let options = {
			title	: '.json_encode( $w->titlePieOrders ).',
			width	: "100%",
			height	: 300
		};
		chart.draw(data, options);
	}

	function drawYearsPieTurnover() {
		let chart	= new google.visualization.PieChart(document.getElementById("chart_years_pie_turnover"));
		let data	= new google.visualization.DataTable();
		data.addColumn("string", '.json_encode( $w->labelYear ).');
		data.addColumn("number", '.json_encode( $w->labelTurnover ).');
		data.addRows(dataYearsPieTurnover);
		let options = {
			title	: '.json_encode( $w->titlePieTurnover ).',
			width	: "100%",
			height	: 300
		};
		chart.draw(data, options);
	}

	function drawYearsTable() {
		let table = new google.visualization.Table(document.getElementById("chart_years_table"));
		let data = new google.visualization.DataTable();
		data.addColumn("string", '.json_encode( $w->labelYear ).');
		data.addColumn("number", '.json_encode( $w->labelOrders ).');
		data.addColumn("number", '.json_encode( $w->labelTurnoverYear ).');
		data.addRows(dataYearsTable);
		let options = {showRowNumber: false};
		table.draw(data, options);
	}

	function drawYearsChartOrders() {
		let chart = new google.visualization.AreaChart(document.getElementById("chart_years_chart_orders"));
		let data = google.visualization.arrayToDataTable(dataYearsChartOrders);
		let options = {
			title: '.json_encode( $w->titleChartOrders ).',
			width: "100%",
			height: "100%",
			vAxis: {title: '.json_encode( $w->labelOrders ).',  titleTextStyle: {color: "#333"}, minValue: 0}
		};
		chart.draw(data, options);
	}

	function drawYearsChartTurnover() {
		let chart = new google.visualization.AreaChart(document.getElementById("chart_years_chart_turnover"));
		let data = google.visualization.arrayToDataTable(dataYearsChartTurnover);
		let options = {
			title: '.json_encode( $w->titleChartTurnover ).',
			width: "100%",
			height: "100%",
			vAxis: {title: '.json_encode( $w->labelTurnoverYear ).',  titleTextStyle: {color: "#333"}, minValue: 0}
		};
		chart.draw(data, options);
	}
</script>
';
