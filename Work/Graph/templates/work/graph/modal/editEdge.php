<?php

$optGraph	= array();
foreach( $graphs as $graph )
	$optGraph[$graph->graphId]	= $graph->title;
$optGraph	= UI_HTML_Elements::Options( $optGraph );

$optNode	= array();
foreach( $nodes as $node )
	$optNode[$node->nodeId]	= $node->label ? $node->label : $node->ID;
$optFromNode	= UI_HTML_Elements::Options( $optNode, $edge->fromNodeId );
$optToNode		= UI_HTML_Elements::Options( $optNode, $edge->toNodeId );

$panelDetails	= '
<div class="content-panel">
	<h4>Edit this edge</h4>
	<div class="content-panel-inner">
<!--		<div class="row-fluid">
			<div class="span12">
				<label for="input_graphId">Graph</label>
				<select name="graphId" id="input_graphId" class="span12">'.$optGraph.'</select>
			</div>
		</div>-->
		<div class="row-fluid">
			<div class="span6">
				<label for="input_fromNodeId">To node</label>
				<select name="fromNodeId" id="input_fromNodeId" class="span12">'.$optFromNode.'</select>
			</div>
			<div class="span6">
				<label for="input_toNodeId">From node</label>
				<select name="toNodeId" id="input_toNodeId" class="span12">'.$optToNode.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_label">Label <small class="muted">(should be unique)</small></label>
				<input type="text" name="label" id="input_label" class="span12" value="'.htmlentities( $edge->label, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
	</div>
</div>';

$panelStyle	= '
<div class="content-panel">
	<h4>Edge style</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span3">
				<label for="input_color">Line color <small class="muted"></small></label>
				<input type="text" name="color" id="input_color" class="span12" value="'.htmlentities( $edge->color, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_fontcolor">Font color <small class="muted"></small></label>
				<input type="text" name="fontcolor" id="input_fontcolor" class="span12" value="'.htmlentities( $edge->fontcolor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_fontsize">Font size <small class="muted"></small></label>
				<input type="text" name="fontsize" id="input_fontsize" class="span12" value="'.htmlentities( $edge->fontsize, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_arrowhead">Arrow head <small class="muted"></small></label>
				<input type="text" name="arrowhead" id="input_arrowhead" class="span12" value="'.htmlentities( $edge->arrowhead, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_arrowsize">Arrow size <small class="muted"></small></label>
				<input type="text" name="arrowsize" id="input_arrowsize" class="span9" value="'.htmlentities( $edge->arrowsize, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
	</div>
</div>';

$nodeIndex	= array();
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ? $node->label : '<tt><em>'.$node->ID.'</em></tt>';

$label	= $nodeIndex[$edge->fromNodeId].'&nbsp;&rarr;&nbsp;'.$nodeIndex[$edge->toNodeId];
if( $edge->label )
	$label	= $edge->label.'&nbsp;<small class="muted">('.$label.')</small>';

return '
<form action="./work/graph/editEdge/'.( $nodeId ? $edgeId.'/'.$nodeId : $edgeId ).'" method="post">
	<div id="modalEditEdge" class="modal hide not-fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><span class="muted">Edge: </span>'.$label.'</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span12">
					'.$panelDetails.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.$panelStyle.'
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a class="btn" data-dismiss="modal">Close</a>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> Save</button>
		</div>
	</div>
</form>
';
