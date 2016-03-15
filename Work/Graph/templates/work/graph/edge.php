<?php

$nodeIndex	= array();
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ? $node->label : '<tt><em>'.$node->ID.'</em></tt>';

$label	= $nodeIndex[$edge->fromNodeId].'&nbsp;&rarr;&nbsp;'.$nodeIndex[$edge->toNodeId];
if( $edge->label )
	$label	= $edge->label.'&nbsp;<small class="muted">('.$label.')</small>';

$description	= strlen( trim( $graph->description ) ) ? trim( $graph->description )."\n\n" : '';

$factsEdge		= array(
	array( 'Source Node', UI_HTML_Tag::create( 'a', $nodeIndex[$edge->fromNodeId], array( 'href' => './work/graph/node/'.$edge->fromNodeId ) ), NULL ),
	array( 'TargetNode', UI_HTML_Tag::create( 'a', $nodeIndex[$edge->toNodeId], array( 'href' => './work/graph/node/'.$edge->toNodeId ) ), NULL ),
	array( 'Title', $edge->label, NULL ),
	array( 'Arrow Head', $edge->arrowhead, NULL ),
	array( 'Arrow Size', $edge->arrowsize, NULL ),
	array( 'Color', $edge->color, NULL ),
	array( 'Font Color', $edge->fontcolor, NULL ),
	array( 'Font Size', $edge->fontsize, NULL ),
);

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
			<h3><span class="muted">Edge: </span>'.$label.'</h3>
			<div class="content-panel-inner">
				<p>
					'.$view->renderFacts( $factsEdge ).'
				</p>
				<div class="buttonbar">
					<div class="btn-group">
						<a href="./work/graph" role="button" class="btn btn-small"><i class="icon-arrow-left"></i> Graph</a>
						<button type="button" onclick="document.location.href = \'./work/graph/node/'.$nodeId.'\';" role="button" class="btn btn-small" '.( $nodeId ? '' : 'disabled="disabled"').'><i class="icon-arrow-left"></i> Node</button>
					</div>
					<div class="btn-group">
						<a href="#modalEditEdge" role="button" class="btn btn-primary btn-small" data-toggle="modal"><i class="icon-pencil icon-white"></i> Edit</a>
						<a href="./work/graph/removeEdge/'.$edge->edgeId.'" role="button" class="btn btn-inverse btn-small"><i class="icon-trash icon-white"></i> Remove</a>
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
		'.$view->loadTemplateFile( 'work/graph/panel/edgeNodes.php' ).'
	</div>
</div>
'.$view->loadTemplateFile( 'work/graph/modal/editGraph.php' ).'
'.$view->loadTemplateFile( 'work/graph/modal/editEdge.php' ).'
'.$view->loadTemplateFile( 'work/graph/modal/addNode.php' ).'
'.$view->loadTemplateFile( 'work/graph/modal/addEdge.php' ).'
<style>
body.moduleWorkGraph .modal {
	width: 700px;
	margin-left: -350px;
}
</style>
';
