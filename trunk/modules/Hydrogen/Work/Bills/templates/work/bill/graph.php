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
	'userId'	=> $userId,
	'date'		=> '<'.date( 'Ymd' ),
	'status'	=> 0
);
$sum	= 0;
$openBills	= $model->getAll( $conditions );

foreach( $openBills as $bill ){
	if( $bill->date < date( 'Ymd' ) )
		$sum	+= $bill->type ? -1 * $bill->price : $bill->price;
}

$listOpenPast	= $view->renderTable( $model->getAll( array(
	'userId'	=> $userId,
	'date'		=> '<='.date( 'Ymd' ),
	'status'	=> 0
) ), './work/bill/graph', FALSE );
$listOpenFuture	= $view->renderTable( $model->getAll( array(
	'userId'	=> $userId,
	'date'		=> '>'.date( 'Ymd' ),
	'status'	=> 0
) ), './work/bill/graph', FALSE );



$conditions	= array(
	'userId'	=> $userId,
//	'date'		=> '>'.$yearStart.$monthStart.'00',
//	'date'		=> '<'.$yearEnd.$monthEnd.'32',
);
$orders		= array( 'date' => 'ASC' );
$bills		= $model->getAll( $conditions, $orders );

$dataGraph	= array( array( "Tag", "Stand" ) );
$balance	= $sum;
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

$tabs	= View_Work_Bill::renderTabs( $env, 'graph' );

return '
<!--<h2>'.$words['graph']['heading'].'</h2>-->
'.$tabs.'
<div class="row-fluid">
	<div class="span4">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4>Berechnung</h4>
				<table class="table table-stiped">
					<colgroup>
						<col width="70%"/>
						<col width="30%"/>
					</colgroup>
					<tbody>
						<tr>
							<th colspan="2">Vergangenheit</th>
						</tr>
						<tr>
							<td>offen: Vergangenheit</td>
							<td style="text-align: right">'.$view->renderPrice( $sum,  $sum < 0, '&nbsp;&euro;' ).'</td>
						</tr>
						<tr>
							<td>Endstand</td>
							<td style="text-align: right"><b>'.$view->renderPrice( $balance,  $balance < 0, '&nbsp;&euro;' ).'</b></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="span8">
		<div id="chart_balance"></div>
	</div>
</div>
<hr/>
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4><span class="muted">offene Rechnungen: </span>Vergangenheit</h4>
				'.$listOpenPast.'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4><span class="muted">offene Rechnungen: </span>Zukunft</h4>
				'.$listOpenFuture.'
			</div>
		</div>
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
