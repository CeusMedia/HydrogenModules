<?php

/** @var array<string,array<string,string>> $words */
$wg	= (object) $words['general'];
$wfa	= (object) $words['facts'];

/** @var View_Work_Graph $view */
/** @var object $graph */
/** @var object $node */


$label	= $node->ID;
if( $node->label )
	$label	= $node->label.'&nbsp;<small class="muted">('.$label.')</small>';

$description	= strlen( trim( $node->description ) ) ? trim( $node->description )."\n" : '';

$facts	= [
	[$wfa->id, $node->ID, NULL],
	[$wfa->label, $node->label, NULL],
	[$wfa->shape, $node->shape, $graph->nodeShape],
	[$wfa->style, $node->style, $graph->nodeStyle],
	[$wfa->color, $node->color, $graph->nodeColor],
	[$wfa->fillColor, $node->fillcolor, $graph->nodeFillcolor],
	[$wfa->width, $node->width, $graph->nodeWidth],
	[$wfa->height, $node->height, $graph->nodeHeight],
	[$wfa->fontSize, $node->fontsize, $graph->nodeFontsize],
	[$wfa->fontColor, $node->fontcolor, $graph->nodeFontcolor],
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
			<h3><span class="muted">'.$wg->prefixNode.'</span>'.$label.'</h3>
			<div class="content-panel-inner">
				<p>
					'.nl2br( $description ).'
				</p>
				<p>
					'.$view->renderFacts( $facts ).'
				</p>
				<div class="buttonbar">
					<a href="./work/graph" role="button" class="btn btn-small"><i class="icon-arrow-left"></i> '.$wg->buttonBackToGraph.'</a>
					<div class="btn-group">
						<a href="#modalEditNode" role="button" class="btn btn-primary btn-small" data-toggle="modal"><i class="icon-pencil icon-white"></i> '.$wg->buttonEdit.'</a>
						<a href="./work/graph/removeEdge/'.$node->nodeId.'" role="button" class="btn btn-inverse btn-small"><i class="icon-trash icon-white"></i> '.$wg->buttonRemove.'</a>
					</div>
					<div class="btn-group">
						<a href="#modalAddNode" role="button" class="btn btn-success btn-small" data-toggle="modal"><i class="icon-plus icon-white"></i> '.$wg->buttonAddNode.'</a>
						<a href="#modalAddEdge" role="button" class="btn btn-success btn-small" data-toggle="modal"><i class="icon-plus icon-white"></i> '.$wg->buttonAddEdge.'</a>
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
