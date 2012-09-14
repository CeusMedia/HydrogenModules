<?php
/**
 *	@see		ImageMapster
 *	@link		http://www.outsharked.com/imagemapster/ 
 */

require_once 'jpgraph/3.0.7/src/jpgraph.php';		
require_once 'jpgraph/3.0.7/src/jpgraph_pie.php';		
require_once 'jpgraph/3.0.7/src/jpgraph_pie3d.php';		

$data	= array(
	'status'	=> array(),
	'priority'	=> array(),
	'type'		=> array()
);
$model	= new Model_Issue( $env );
foreach( $words['states'] as $key => $value ){
	$data['status'][]	= array(
		'count'		=> $numberStates[$key],
		'name'		=> utf8_decode( $value ),
		'status'	=> $key
	);
}
foreach( $words['priorities'] as $key => $value ){
	$data['priority'][]	= array(
		'count'		=> $numberPriorities[$key],
		'name'		=> utf8_decode( $value ),
		'priority'	=> $key
	);
}
foreach( $words['types'] as $key => $value ){
	$data['type'][]	= array(
		'count'		=> $numberTypes[$key],
		'name'		=> utf8_decode( $value ),
		'type'	=> $key
	);
}

$graphStatus	= $view->buildGraph( $data, $words, 'status' );
$graphPriority	= $view->buildGraph( $data, $words, 'priority' );
$graphType		= $view->buildGraph( $data, $words, 'type' );

$indicator	= new UI_HTML_Indicator();
$ind1		= $indicator->build( 5, 10 );

#print_m( $env->getConfig()->getAll( 'module.work_issues.graph.all.' ) );
$width	= $env->getConfig()->get( 'module.work_issues.graph.all.width' );
#remark( $width + 20 );
#die;


return '
<style>
#layout-content dl dt {
	width: 100px;
	}
#layout-content dl dd div.indicator {
	margin-top: 5px;
	}
</style>
<script src="/lib/cmScripts/jquery/mapster/1.2.6.min.js"></script>
<script>
$(document).ready(function(){
	Issues.loadLatest("#table-issues-open");
	Issues.loadLatestDone("#table-issues-done");
	if(typeof jQuery.mapster != "undefined"){
		$("img").mapster({
			fillOpacity: 0.25,
			fillColor: "000000",
			stroke: true,
			strokeColor: "000000",
			strokeOpacity: 0.5,
			singleSelect: true,
			clickNavigate: true,
			onClick: function(){
				console.log("click");
			}
		});
	}
});
</script>
<div class="not-column-left-60" style="float: left; width: '.( $width + 40 ).'px">
	<fieldset>
		<legend>Übersicht</legend>
<!--		<div class="column-left-50">
			<dl>
				<dt>Test</dt>
				<dd>'.$ind1.'</dd>
			</dl>
		</div>
		<div class="column-left-50">
			<dl>
				<dt>Test</dt>
				<dd>Test</dd>
			</dl>
		</div>
		<div class="column-clear"></div>
		<br/>
		<hr/>-->
		<div class="column-clear">
			<h4>Einträge nach Status</h4>
			'.$graphStatus.'
		</div>
		<div class="column-clear">
			<h4>Einträge nach Priorität</h4>
			'.$graphPriority.'
		</div>
		<div class="column-clear">
			<h4>Einträge nach Typ</h4>
			'.$graphType.'
		</div>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/issue/add', 'neuer Eintrag', 'button add' ).'
		</div>
	</fieldset>
</div>
<div class="not-column-left-40" style="margin-left: '.( $width + 70 ).'px">
	<fieldset>
		<legend>Notierte Probleme</legend>
		<table id="table-issues-done" class="issues">
			<thead>
				<tr>
					<th colspan="3">bearbeitet und abnahmenbereit oder geschlossen</th>
				</tr>
			</thead>
			<tbody>
				<tr><td></td><td><em><small>keine</small></em></td></tr>
			</tbody>
		</table>
		<table id="table-issues-open" class="issues">
			<thead>
				<tr>
					<th colspan="3">neu oder offen oder in Arbeit</th>
				</tr>
			</thead>
			<tbody>
				<tr><td></td><td><em><small>keine</small></em></td></tr>
			</tbody>
		</table>
	</fieldset>
</div>
<div class="column-clear"></div>';
?>