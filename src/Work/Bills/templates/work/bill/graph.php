<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var \CeusMedia\HydrogenFramework\Environment $env */
/** @var View_Work_Bill $view */
/** @var array $words */
/** @var string $userId */


$model	= new Model_Bill( $this->env );

$daysFuture	= 60;
$dateFuture	= date( 'Ymd', time() + $daysFuture * 24 * 60 * 60 );


/*  --  OPEN BILLS: PAST  --  */
$conditions	= [
	'userId'	=> $userId,
	'date'		=> '<'.date( 'Ymd' ),
	'status'	=> 0
];
$sum	= 0;
$openBills	= $model->getAll( $conditions );
foreach( $openBills as $nr => $bill )
	$sum	+= $bill->type ? -1 * $bill->price : $bill->price;
$listOpenPast	= $view->renderTable( $openBills, './work/bill/graph', FALSE );


/*  --  OPEN BILLS: FUTURE  --  */
$openFutureBills	= $model->getAll( [
	'userId'	=> $userId,
	'date'		=> '>'.date( 'Ymd' ),
	'status'	=> 0
], ['date' => 'ASC'] );

foreach( $openFutureBills as $nr => $bill )
	if( $bill->date > $dateFuture )
		unset( $openFutureBills[$nr] );
$listOpenFuture	= $view->renderTable( $openFutureBills, './work/bill/graph', FALSE );


/*  --  GRAPH DATA  --  */
$conditions	= [
	'userId'	=> $userId,
];
$orders		= ['date' => 'ASC'];
$bills		= $model->getAll( $conditions, $orders );

$dataGraph	= [["Tag", "Stand"]];
$balance	= $sum;
$year		= date( 'Y' );
$month		= date( 'm' );
$day		= date( 'd' );
for( $i=0; $i<$daysFuture; $i++ ){
	$time 	= time() + $i * 24 * 60 * 60;
	$date	= date( 'Ymd', $time );
	foreach( $bills as $bill )
		if( $bill->date == $date )
			$bill->type ? $balance -= $bill->price : $balance += $bill->price;
	$dataGraph[]	= [date( 'j.n.', $time ), $balance];
}


/*  --  FILTERS  --  */
$optType	= ['' => '- alle -'] + $words['types'];
$optType	= HtmlElements::Options( $optType, $env->getSession()->get( 'filter_work_bill_type' ) );

$optStatus	= ['' => '- alle -'] +$words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $env->getSession()->get( 'filter_work_bill_status' ) );

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
							<td>Endstand <small class="muted">('.date( 'j.n', strtotime( $dateFuture ) ).')</small></td>
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
let dataGraph = '.json_encode( $dataGraph ).';

// Load the Visualization API and the piechart package.
google.load("visualization", "1.0", {"packages":["corechart"]});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(function () {
	let chart = new google.visualization.AreaChart(document.getElementById("chart_balance"));
	let data = google.visualization.arrayToDataTable(dataGraph);
	let options = {
		title: "Prognose",
		width: "100%",
		height: "100%",
		vAxis: {title: "Kontostand",  titleTextStyle: {color: "#333"}, minValue: 0}
	};
	chart.draw(data, options);
});
</script>
';
