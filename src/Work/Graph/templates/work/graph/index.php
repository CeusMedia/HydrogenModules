<?php

/** @var array<string,array<string,string>> $words */
$wg	= (object) $words['general'];
$wfa	= (object) $words['facts'];

/** @var object $graph */

$description	= strlen( trim( $graph->description ) ) ? trim( $graph->description )."\n" : '';

/* @todo investigate default values */
$facts	= [
	[$wfa->nodeShape, $graph->nodeShape, 'box'],
	[$wfa->nodeStyle, $graph->nodeStyle, NULL],
	[$wfa->nodeColor, $graph->nodeColor, NULL],
	[$wfa->nodeFillColor, $graph->nodeFillcolor, NULL],
	[$wfa->nodeWidth, $graph->nodeWidth, 0.8],
	[$wfa->nodeHeight, $graph->nodeHeight, 0.5],
	[$wfa->nodeFontSize, $graph->nodeFontsize, 14],
	[$wfa->nodeFontColor, $graph->nodeFontcolor, NULL],
	[$wfa->edgeArrowHead, $graph->edgeArrowhead, NULL],
	[$wfa->edgeArrowSize, $graph->edgeArrowsize, NULL],
	[$wfa->edgeColor, $graph->edgeColor, NULL],
	[$wfa->edgeFontSize, $graph->edgeFontsize, NULL],
	[$wfa->edgeFontColor, $graph->edgeFontcolor, NULL],
];

return '
<div class="row-fluid">
	<div class="span3">
		'.$view->loadTemplateFile( 'work/graph/panel/graphs.php' ).'
		'.$view->loadTemplateFile( 'work/graph/panel/nodes.php' ).'
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3>'.$wg->headingImage.'</h3>
			<div class="content-panel-inner">
				<a href="./work/graph/view/'.$graphId.'"><img src="./work/graph/view/'.$graphId.'"/></a>
			</div>
		</div>
	</div>
</div>
<hr/>

<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3><span class="muted">'.$wg->prefixGraph.'</span>'.$graph->title.'</h3>
			<div class="content-panel-inner">
				<p>
					'.nl2br( $description ).'
				</p>
				<p>
					'.$view->renderFacts( $facts ).'
				</p>
				<div class="buttonbar">
					<a href="#modalEditGraph" role="button" class="btn btn-primary btn-small" data-toggle="modal"><i class="icon-pencil icon-white"></i> '.$wg->buttonEdit.'</a>
					<div class="btn-group">
						<a href="#modalAddNode" role="button" class="btn btn-success btn-small" data-toggle="modal"><i class="icon-plus icon-white"></i> '.$wg->buttonAddNode.'</a>
						<a href="#modalAddEdge" role="button" class="btn btn-success btn-small" data-toggle="modal"><i class="icon-plus icon-white"></i> '.$wg->buttonAddEdge.'</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="span6">
		'.$view->loadTemplateFile( 'work/graph/panel/nodes.php' ).'
	</div>
</div>
'.$view->loadTemplateFile( 'work/graph/modal/editGraph.php' ).'
'.$view->loadTemplateFile( 'work/graph/modal/addNode.php' ).'
'.$view->loadTemplateFile( 'work/graph/modal/addEdge.php' ).'
';
