<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $newsletter */

$model		= new Model_Newsletter_Reader_Letter( $env );
$lettersSent	= $model->count( ['newsletterId' => $newsletter->newsletterId, 'status' => '>= 1'] );
$lettersOpen	= $model->count( ['newsletterId' => $newsletter->newsletterId, 'status' => '>= 2'] );

if( !$lettersSent )
	return HtmlTag::create( 'div', 'Noch nicht versendet.', ['class' => 'alert alert-info'] );

$start	= strtotime( date( "Y-m-d", (int) $newsletter->sentAt )." 00:00:00" );
$end	= strtotime( date( "Y-m-d", time() )." 23:59:59" ) + 1;
$end	= min( $end, $start + 14 * 86400 );
$days	= round( ( $end - $start ) / 86400 );

if( $days > 1 ){
	$list	= [[
		'Monat',
		'Geöffnet',
	//	'Geklickt',
		'Versendet'
	]];
	for( $i=-1; $i<=$days; $i++ ){
		$timestamp	= $start + $i * 86400;
		$list[]	= [
			date( "j.n.", $timestamp ),
			countOpened( $model, $newsletter->newsletterId, $timestamp, 86400 ),
	//		(int) 0,
			countSent( $model, $newsletter->newsletterId, $timestamp, 86400 ),
		];
	}
}
else{
	$dura	= 3600 / 2;
	$start	= strtotime( date( "Y-m-d H:00:00", (int) $newsletter->sentAt ) );
	$end	= strtotime( date( "Y-m-d H:59:59", time() ) );
	$hours	= ceil( ( $end - $start ) / $dura );

	$list	= [[
		'Stunde',
		'Geöffnet',
	//	'Geklickt',
		'Versendet'
	]];
	$hour	= (int) date( "H", $start );
	for( $i=-1; $i<=$hours; $i++ ){
		$timestamp	= $start + $hour + $i * $dura;
		$list[]	= [
			date( "H:i", $timestamp ),
			countOpened( $model, $newsletter->newsletterId, $timestamp, $dura ),
			//		(int) 0,
			countSent( $model, $newsletter->newsletterId, $timestamp, $dura ),
		];
	}
}

return '
<div class="content-panel">
	<h3>'.$words->statistics['heading'].'</h3>
	<div class="content-panel-inner">
		<ul>
			<li>versendet vor <b>'.View_Helper_TimePhraser::convertStatic( $env, $newsletter->sentAt, TRUE ).'</b><!-- <small class="muted">(am '.date( 'd.m.Y', $start ).')</small>--></li>
			<li>gesendet an <b>'.$lettersSent.' Abonnenten</b></li>
			<li>geöffnet von <b>'.$lettersOpen.' Abonnenten</b>, Rate: <b>'.round( $lettersOpen / $lettersSent * 100 ).'%</b></li>
			<!--<li><b>0 mal</b> angeklickt, Rate: <b>'.round( 0 / $lettersSent * 100 ).'%</b></li>-->
		</ul>
		<br/>
		<h4>'.$words->statistics['graph'].'</h4>
		<div id="chart_div" style="width: 100%; height: 300px;"></div>
	</div>
</div>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		let data = google.visualization.arrayToDataTable('.json_encode( $list ).');
		let options = {};
		let chart = new google.visualization.LineChart(document.getElementById("chart_div"));
		chart.draw(data, options);
	}
</script>';

function countOpened( $model, $newsletterId, $timestamp, $duration, $status = '>= 2' ): int
{
	return $model->count( [
		'status'		=> $status,
		'newsletterId'	=> $newsletterId,
		'openedAt'		=> sprintf( '>< %s & %s', $timestamp, $timestamp + $duration )
	] );
}

function countSent( $model, $newsletterId, $timestamp, $duration, $status = '>= 1' ): int
{
	return $model->count( [
		'status'		=> $status,
		'newsletterId'	=> $newsletterId,
		'sentAt'		=> sprintf( '>< %s & %s', $timestamp, $timestamp + $duration )
	] );
}
