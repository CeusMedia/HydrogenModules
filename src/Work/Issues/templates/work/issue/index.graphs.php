<?php

use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array<string,array<string,string>> $words */
/** @var View_Work_Issue $view */

/**
 *	@see		ImageMapster
 *	@link		http://www.outsharked.com/imagemapster/
 */

require_once 'jpgraph/3.0.7/src/jpgraph.php';
require_once 'jpgraph/3.0.7/src/jpgraph_pie.php';
require_once 'jpgraph/3.0.7/src/jpgraph_pie3d.php';

/**
 * Todo: Upgrade to use amenadiel/jpgraph:^4 (https://github.com/HuasoFoundries/jpgraph)
 */

$data	= [
	'status'	=> [],
	'priority'	=> [],
	'type'		=> []
];
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

$helperGraph	= new View_Helper_Work_Issue_Graph( $env );
$helperGraph->setData( $data );
$helperGraph->setWords( $words );


$graphStatus	= $helperGraph->setType( 'status' )->render();
$graphPriority	= $helperGraph->setType( 'priority' )->render();
$graphType		= $helperGraph->setType( 'type' )->render();

$indicator	= new HtmlIndicator();
$ind1		= $indicator->build( 5, 10 );

#print_m( $env->getConfig()->getAll( 'module.work_issues.graph.all.' ) );
$width	= $env->getConfig()->get( 'module.work_issues.graph.all.width' );
#remark( $width + 20 );
#die;

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$words['graphs']['buttonAdd'], [
	'href'	=> './work/issue/add',
	'class'	=> 'btn btn-success'
] );

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
	<div class="content-panel">
		<h3>'.$words['graphs']['heading'].'</h3>
		<div class="content-panel-inner">
			<div class="row-fluid">
				<h4>'.$words['graphs']['headingByStatus'].'</h4>
				'.$graphStatus.'
			</div>
			<div class="row-fluid">
				<h4>'.$words['graphs']['headingByPriority'].'</h4>
				'.$graphPriority.'
			</div>
			<div class="row-fluid">
				<h4>'.$words['graphs']['headingByType'].'</h4>
				'.$graphType.'
			</div>
		</div>
	</div>
	<div class="content-panel">
		<h3>'.$words['graphs']['headingIssues'].'</h3>
		<div class="content-panel-inner">
			<div class="row-fluid">
				<table id="table-issues-done" class="issues table table-striped">
					<thead>
						<tr>
							<th colspan="3">'.$words['graphs']['headDone'].'</th>
						</tr>
					</thead>
					<tbody>
						<tr><td></td><td><em><small>'.$words['graphs']['noEntries'].'</small></em></td></tr>
					</tbody>
				</table>
				<table id="table-issues-open" class="issues table table-striped">
					<thead>
						<tr>
							<th colspan="3">'.$words['graphs']['headOpen'].'</th>
						</tr>
					</thead>
					<tbody>
						<tr><td></td><td><em><small>'.$words['graphs']['noEntries'].'</small></em></td></tr>
					</tbody>
				</table>
			</div>
			<div class="buttonbar">
				'.$buttonAdd.'
			</div>
		</div>
	</div>
</div>';
