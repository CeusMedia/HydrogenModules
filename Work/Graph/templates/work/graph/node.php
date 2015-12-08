<?php

$panelTree		= $view->loadTemplateFile( 'work/graph/panelTree.php' );
$panelEdges		= $view->loadTemplateFile( 'work/graph/panelNodeEdges.php' );

$modalEditGraph	= $view->loadTemplateFile( 'work/graph/modal/editGraph.php' );
$modalEditNode	= $view->loadTemplateFile( 'work/graph/modal/editNode.php' );
$modalAddNode	= $view->loadTemplateFile( 'work/graph/modal/addNode.php' );
$modalAddEdge	= $view->loadTemplateFile( 'work/graph/modal/addEdge.php' );

$label	= $node->ID;
if( $node->label )
	$label	= $node->label.'&nbsp;<small class="muted">('.$label.')</small>';

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
			<h3><span class="muted">Node: </span>'.$label.'</h3>
			<div class="content-panel-inner">
				<div class="buttonbar">
					<div class="btn-group">
						<a href="#modalEditGraph" role="button" class="btn btn-primary" data-toggle="modal"><i class="icon-pencil icon-white"></i> Edit graph</a>
						<a href="#modalEditNode" role="button" class="btn btn-primary" data-toggle="modal"><i class="icon-pencil icon-white"></i> Edit node</a>
						<a href="#modalAddNode" role="button" class="btn btn-success" data-toggle="modal"><i class="icon-plus icon-white"></i> Add node</a>
						<a href="#modalAddEdge" role="button" class="btn btn-success" data-toggle="modal"><i class="icon-plus icon-white"></i> Add edge</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="span4">
		'.$panelEdges.'
	</div>
</div>
'.$modalEditGraph.'
'.$modalEditNode.'
'.$modalAddNode.'
'.$modalAddEdge.'
';