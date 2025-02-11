<?php

/** @var object $graph */

$description	= strlen( trim( $graph->description ) ) ? trim( $graph->description )."\n" : '';

/* @todo investigate default values */
$facts	= [
	['Node Shape', $graph->nodeShape, 'box'],
	['Node Style', $graph->nodeStyle, NULL],
	['Node Color', $graph->nodeColor, NULL],
	['Node Fill Color', $graph->nodeFillcolor, NULL],
	['Node Width', $graph->nodeWidth, 0.8],
	['Node Height', $graph->nodeHeight, 0.5],
	['Node Font Size', $graph->nodeFontsize, 14],
	['Node Font Color', $graph->nodeFontcolor, NULL],
	['Edge Arrow Head', $graph->edgeArrowhead, NULL],
	['Edge Arrow Size', $graph->edgeArrowsize, NULL],
	['Edge Color', $graph->edgeColor, NULL],
	['Edge Font Size', $graph->edgeFontsize, NULL],
	['Edge Font Color', $graph->edgeFontcolor, NULL],
];

return '
<div class="row-fluid">
	<div class="span3">
		'.$view->loadTemplateFile( 'work/graph/panel/graphs.php' ).'
		'.$view->loadTemplateFile( 'work/graph/panel/nodes.php' ).'
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3>Graph image</h3>
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
			<h3><span class="muted">Graph: </span>'.$graph->title.'</h3>
			<div class="content-panel-inner">
				<p>
					'.nl2br( $description ).'
				</p>
				<p>
					'.$view->renderFacts( $facts ).'
				</p>
				<div class="buttonbar">
					<a href="#modalEditGraph" role="button" class="btn btn-primary btn-small" data-toggle="modal"><i class="icon-pencil icon-white"></i> Edit</a>
					<div class="btn-group">
						<a href="#modalAddNode" role="button" class="btn btn-success btn-small" data-toggle="modal"><i class="icon-plus icon-white"></i> Node</a>
						<a href="#modalAddEdge" role="button" class="btn btn-success btn-small" data-toggle="modal"><i class="icon-plus icon-white"></i> Edge</a>
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
