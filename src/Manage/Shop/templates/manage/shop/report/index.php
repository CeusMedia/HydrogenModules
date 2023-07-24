<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$dataYearsPieOrders	= [];
foreach( $ordersPerYear as $nr => $entry )
	$dataYearsPieOrders[]	= array( (string)$entry->year, (int)$entry->orders );

$dataYearsPieTurnover	= [];
foreach( $ordersPerYear as $nr => $entry )
	$dataYearsPieTurnover[]	= array( (string)$entry->year, array( 'v' => (int)$entry->turnover, 'f' => (int)$entry->turnover." €" ) );


$sumOrders		= 0;
$sumTurnover	= 0;
$dataYearsTable	= [];
foreach( $ordersPerYear as $nr => $entry ){
	$sumOrders		+= (float)$entry->orders;
	$sumTurnover	+= (float)$entry->turnover;
	$dataYearsTable[(string)  $entry->year]	= array(
		(string) $entry->year,
		(int) $entry->orders,
 		array(
			'v'	=> (float)$entry->turnover,
			'f'	=> number_format( $entry->turnover, 0, ",", "." )." €"
		)
	);
}

$factor	= ( date( "L" ) ? 366 : 365)  / date( "z" );


$dataYearsTable["Summe"]	= array(
	"Summe",
	array(
		'v'	=> (float)$sumOrders,
		'f'	=> number_format( $sumOrders, 0, ",", "." )
	),
	array(
		'v'	=> (float)$sumTurnover,
		'f'	=> number_format( $sumTurnover, 0, ",", "." )." €"
	)
);

$dataYearsTable["Trend"]	= array(
	'Trend',
	round( $dataYearsTable[$entry->year][1] * $factor ),
	array(
		'v'	=> (float) $dataYearsTable[$entry->year][2]['v'] * $factor,
		'f'	=> number_format( $dataYearsTable[$entry->year][2]['v'] * $factor, 0, ",", "." )." €"
	)
);

$sum	= 0;
$dataYearsChartOrders	= [["Jahr", "Bestellungen", "Trend"]];
foreach( $ordersPerYear as $nr => $entry ){
//	if( $entry->year == date( "Y" ) )
//		$entry->orders	*= $factor;
	$sum		+= $entry->orders;
	$dataYearsChartOrders[]	= array(
		(string)$entry->year,
		(int)$entry->orders,
		(int)( $sum / ( $nr + 1 ) )
	);
}

$sum	= 0;
$dataYearsChartTurnover	= [["Jahr", "Umsatz", "Trend"]];
foreach( $ordersPerYear as $nr => $entry ){
//	if( $entry->year == date( "Y" ) )
//		$entry->turnover	*= $factor;
	$sum		+= (int) $entry->turnover;
	$dataYearsChartTurnover[]	= array(
		(string) $entry->year,
		(int) $entry->turnover,
		(int) ( $sum / ( $nr + 1 ) )
	);
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
<h3>Bestellungen und Umsätze über die Jahre</h3>
<div class="row-fluid">
	<div class="span8">
		<div id="chart_years_chart_orders"></div>
		<div id="chart_years_chart_turnover"></div>
	</div>
	<div class="span4" data-style="border: 1px solid #BBBBBB;">
		<div id="chart_years_table"></div>
	</div>
</div>
<h4>Verteilung von Bestellungen und Umsätzen</h4>
<div class="row-fluid">
	<div class="span6">
		<div id="chart_years_pie_orders"></div>
	</div>
	<div class="span6">
		<div id="chart_years_pie_turnover"></div>
	</div>
</div>
<script>

	var dataYearsTable			= '.json_encode( array_values( $dataYearsTable ) ).';
	var dataYearsPieOrders		= '.json_encode( $dataYearsPieOrders ).';
	var dataYearsPieTurnover	= '.json_encode( $dataYearsPieTurnover ).';
	var dataYearsChartOrders	= '.json_encode( $dataYearsChartOrders ).';
	var dataYearsChartTurnover	= '.json_encode( $dataYearsChartTurnover ).';

	// Load the Visualization API and the piechart package.
	google.load("visualization", "1.0", {"packages":["corechart", "table"]});

	// Set a callback to run when the Google Visualization API is loaded.
	google.setOnLoadCallback(drawYearsTable);
	google.setOnLoadCallback(drawYearsChartOrders);
	google.setOnLoadCallback(drawYearsChartTurnover);
	google.setOnLoadCallback(drawYearsPieOrders);
	google.setOnLoadCallback(drawYearsPieTurnover);

	function drawYearsPieOrders() {
		var chart	= new google.visualization.PieChart(document.getElementById("chart_years_pie_orders"));
		var data	= new google.visualization.DataTable();
		data.addColumn("string", "Jahr");
		data.addColumn("number", "Bestellungen");
		data.addRows(dataYearsPieOrders);
		var options = {
			title	: "Bestellungen pro Jahr",
			width	: "100%",
			height	: 300
		};
		chart.draw(data, options);
	}

	function drawYearsPieTurnover() {
		var chart	= new google.visualization.PieChart(document.getElementById("chart_years_pie_turnover"));
		var data	= new google.visualization.DataTable();
		data.addColumn("string", "Jahr");
		data.addColumn("number", "Umsatz");
		data.addRows(dataYearsPieTurnover);
		var options = {
			title	: "Umsatz pro Jahr",
			width	: "100%",
			height	: 300
		};
		chart.draw(data, options);
	}

	function drawYearsTable() {
		var table = new google.visualization.Table(document.getElementById("chart_years_table"));
		var data = new google.visualization.DataTable();
		data.addColumn("string", "Jahr");
		data.addColumn("number", "Bestellungen");
		data.addColumn("number", "Jahresumsatz in €");
		data.addRows(dataYearsTable);
		var options = {showRowNumber: false};
		table.draw(data, options);
	}

	function drawYearsChartOrders() {
		var chart = new google.visualization.AreaChart(document.getElementById("chart_years_chart_orders"));
		var data = google.visualization.arrayToDataTable(dataYearsChartOrders);
		var options = {
			title: "Bestellungen pro Jahr mit Trend",
			width: "100%",
			height: "100%",
			vAxis: {title: "Bestellungen",  titleTextStyle: {color: "#333"}, minValue: 0}
		};
		chart.draw(data, options);
	}

	function drawYearsChartTurnover() {
		var chart = new google.visualization.AreaChart(document.getElementById("chart_years_chart_turnover"));
		var data = google.visualization.arrayToDataTable(dataYearsChartTurnover);
		var options = {
			title: "Jahresumsatz in € pro Jahr mit Trend",
			width: "100%",
			height: "100%",
			vAxis: {title: "Jahresumsatz in €",  titleTextStyle: {color: "#333"}, minValue: 0}
		};
		chart.draw(data, options);
	}
</script>
';
