<?php

$model	= new Model_Bill( $this->env );
$year	= date( 'Y' );
$month	= date( 'm' );
$yearStart	= $year;
$monthStart	= $month - 1;
$yearEnd	= $year;
$monthEnd	= $month + 2;
if( $monthStart < 1 ){
	$yearStart	= $year - 1;
	$monthStart = 12;
}
if( $monthEnd > 12 ){
	$yearEnd	= $year + 1;
	$monthEnd	-= 12;
}

$conditions	= array(
//	'userId'	=> $this->env->getSession()->get( 'userId' ),
//	'date'		=> '>'.$yearStart.$monthStart.'00',
//	'date'		=> '<'.$yearEnd.$monthEnd.'32',
);
$orders		= array( 'date' => 'ASC' );
$bills		= $model->getAll( $conditions, $orders );

$dataGraph	= array( array( "Tag", "Stand" ) );
$balance	= 0;
$year		= date( 'Y' );
$month		= date( 'm' );
$day		= date( 'd' );
for( $i=0; $i<60; $i++ ){
	$time 	= time() + $i * 24 * 60 * 60;
	$date	= date( 'Ymd', $time );
	foreach( $bills as $bill )
		if( $bill->date == $date )
			$bill->type ? $balance -= $bill->price : $balance += $bill->price;
	$dataGraph[]	= array( date( 'j.n.', $time ), $balance );
}

$optType	= array( '' => '- alle -' ) + $words['types'];
$optType	= UI_HTML_Elements::Options( $optType, $env->getSession()->get( 'filter_work_bill_type' ) );

$optStatus	= array( '' => '- alle -' ) +$words['states'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, $env->getSession()->get( 'filter_work_bill_status' ) );

return '
<h2>'.$words['graph']['heading'].'</h2>
<div class="row-fluid">
	<div class="span12">
		<div id="chart_balance"></div>
	</div>
</div>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script>
var dataGraph = '.json_encode( $dataGraph ).';

// Load the Visualization API and the piechart package.
google.load("visualization", "1.0", {"packages":["corechart"]});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(function () {
	var chart = new google.visualization.AreaChart(document.getElementById("chart_balance"));
	var data = google.visualization.arrayToDataTable(dataGraph);
	var options = {
		title: "Prognose",
		width: "100%",
		height: "100%",
		vAxis: {title: "Kontostand",  titleTextStyle: {color: "#333"}, minValue: 0}
	};
	chart.draw(data, options);
});
</script>
';
