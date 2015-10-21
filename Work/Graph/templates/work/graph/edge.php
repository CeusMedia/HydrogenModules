<?php

$panelTree		= $view->loadTemplateFile( 'work/graph/panelTree.php' );
$panelNodes		= $view->loadTemplateFile( 'work/graph/panelEdgeNodes.php' );

$modalEditGraph	= $view->loadTemplateFile( 'work/graph/modal/editGraph.php' );
$modalEditEdge	= $view->loadTemplateFile( 'work/graph/modal/editEdge.php' );
$modalAddNode	= $view->loadTemplateFile( 'work/graph/modal/addNode.php' );
$modalAddEdge	= $view->loadTemplateFile( 'work/graph/modal/addEdge.php' );


$nodeIndex	= array();
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ? $node->label : '<tt><em>'.$node->ID.'</em></tt>';

$label	= $nodeIndex[$edge->fromNodeId].'&nbsp;&rarr;&nbsp;'.$nodeIndex[$edge->toNodeId];
if( $edge->label )
	$label	= $edge->label.'&nbsp;<small class="muted">('.$label.')</small>';

$description	= strlen( trim( $graph->description ) ) ? trim( $graph->description )."\n\n" : '';

return '
<h3><span class="muted">Graph: </span>'.$graph->title.'</h3>
<p>
	'.nl2br( $description ).'
</p>
<div class="row-fluid">
	<div class="span3">
		'.$panelTree.'
	</div>
	<div class="span9">
		<div class="content-panel">
			<h4>Graph image</h4>
			<div class="content-panel-inner">
				<a href="./work/graph/view/'.$graphId.'"><img src="./work/graph/view/'.$graphId.'"/></a>
			</div>
		</div>
	</div>
</div>
<hr/>
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<h3><span class="muted">Edge: </span>'.$label.'</h3>
			<div class="content-panel-inner">
				<div class="buttonbar">
					<div class="btn-group">
						<a href="#modalEditGraph" role="button" class="btn btn-primary" data-toggle="modal"><i class="icon-pencil icon-white"></i> Edit graph</a>
						<a href="#modalEditEdge" role="button" class="btn btn-primary" data-toggle="modal"><i class="icon-pencil icon-white"></i> Edit edge</a>
						<a href="#modalAddNode" role="button" class="btn btn-success" data-toggle="modal"><i class="icon-plus icon-white"></i> Add node</a>
						<a href="#modalAddEdge" role="button" class="btn btn-success" data-toggle="modal"><i class="icon-plus icon-white"></i> Add edge</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="span4">
		'.$panelNodes.'
	</div>
</div>
'.$modalEditGraph.'
'.$modalEditEdge.'
'.$modalAddNode.'
'.$modalAddEdge.'
<style>
body.moduleWorkGraph .modal {
	width: 700px;
	margin-left: -350px;
}
</style>
';
