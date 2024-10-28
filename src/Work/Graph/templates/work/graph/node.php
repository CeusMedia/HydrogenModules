<?php

/** @var View_Work_Graph $view */
/** @var object $graph */
/** @var object $node */


$label	= $node->ID;
if( $node->label )
	$label	= $node->label.'&nbsp;<small class="muted">('.$label.')</small>';

$description	= strlen( trim( $node->description ) ) ? trim( $node->description )."\n" : '';

$facts	= [
	['ID', $node->ID, NULL],
	['Label', $node->label, NULL],
	['Shape', $node->shape, $graph->nodeShape],
	['Style', $node->style, $graph->nodeStyle],
	['Color', $node->color, $graph->nodeColor],
	['Fill Color', $node->fillcolor, $graph->nodeFillcolor],
	['Width', $node->width, $graph->nodeWidth],
	['Height', $node->height, $graph->nodeHeight],
	['Font Size', $node->fontsize, $graph->nodeFontsize],
	['Font Color', $node->fontcolor, $graph->nodeFontcolor],
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
			<h3><span class="muted">Node: </span>'.$label.'</h3>
			<div class="content-panel-inner">
				<p>
					'.nl2br( $description ).'
				</p>
				<p>
					'.$view->renderFacts( $facts ).'
				</p>
				<div class="buttonbar">
					<a href="./work/graph" role="button" class="btn btn-small"><i class="icon-arrow-left"></i> Graph</a>
					<div class="btn-group">
						<a href="#modalEditNode" role="button" class="btn btn-primary btn-small" data-toggle="modal"><i class="icon-pencil icon-white"></i> Edit</a>
						<a href="./work/graph/removeEdge/'.$node->nodeId.'" role="button" class="btn btn-inverse btn-small"><i class="icon-trash icon-white"></i> Remove</a>
					</div>
					<div class="btn-group">
						<a href="#modalAddNode" role="button" class="btn btn-success btn-small" data-toggle="modal"><i class="icon-plus icon-white"></i> Node</a>
						<a href="#modalAddEdge" role="button" class="btn btn-success btn-small" data-toggle="modal"><i class="icon-plus icon-white"></i> Edge</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="span6">
		'.$view->loadTemplateFile( 'work/graph/panel/nodeEdges.php' ).'
	</div>
</div>
'.$view->loadTemplateFile( 'work/graph/modal/editGraph.php' ).'
'.$view->loadTemplateFile( 'work/graph/modal/editNode.php' ).'
'.$view->loadTemplateFile( 'work/graph/modal/addNode.php' ).'
'.$view->loadTemplateFile( 'work/graph/modal/addEdge.php' ).'
';
