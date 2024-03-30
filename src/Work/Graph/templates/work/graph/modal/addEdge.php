<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$nodeId	= isset( $nodeId ) ? $nodeId : NULL;

$optGraph	= [];
foreach( $graphs as $graph )
	$optGraph[$graph->graphId]	= $graph->title;
$optGraph	= HtmlElements::Options( $optGraph );

$optNode	= [];
foreach( $nodes as $node )
	$optNode[$node->nodeId]	= $node->label ?: $node->ID;
$optFromNode	= HtmlElements::Options( $optNode, $nodeId );
$optToNode		= HtmlElements::Options( $optNode, $nodeId );

$panelDetails	= '
<div class="content-panel">
	<h4>Add an edge</h4>
	<div class="content-panel-inner">
		<input type="hidden" name="graphId" value="'.$graphId.'"/>
<!--		<div class="row-fluid">
			<div class="span12">
				<label for="input_graphId">Graph</label>
				<select name="graphId" id="input_graphId" class="span12">'.$optGraph.'</select>
			</div>
		</div>-->
		<div class="row-fluid">
			<div class="span6">
				<label for="input_fromNodeId">From node</label>
				<select name="fromNodeId" id="input_fromNodeId" class="span12">'.$optFromNode.'</select>
			</div>
			<div class="span6">
				<label for="input_toNodeId">to node</label>
				<select name="toNodeId" id="input_toNodeId" class="span12">'.$optToNode.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_label">Label <small class="muted">(should be unique)</small></label>
				<input type="text" name="label" id="input_label" class="span12"/>
			</div>
		</div>
	</div>
</div>';

$panelStyle	= '
<div class="content-panel">
	<h4>Edge style</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<label for="input_arrowhead">Arrow head <small class="muted"></small></label>
				<input type="text" name="arrowhead" id="input_arrowhead" class="span12"/>
			</div>
			<div class="span3">
				<label for="input_arrowsize">Arrow size <small class="muted"></small></label>
				<input type="text" name="arrowsize" id="input_arrowsize" class="span9"/>
			</div>
			<div class="span3">
				<label for="input_color">Line color <small class="muted"></small></label>
				<input type="text" name="color" id="input_color" class="span12"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_fontcolor">Font color <small class="muted"></small></label>
				<input type="text" name="fontcolor" id="input_fontcolor" class="span12"/>
			</div>
			<div class="span3">
				<label for="input_fontsize">Font size <small class="muted"></small></label>
				<input type="text" name="fontsize" id="input_fontsize" class="span9"/>
			</div>
		</div>
	</div>
</div>';

return '
<form action="./work/graph/addEdge/'.( $nodeId ? $graphId.'/'.$nodeId : $graphId ).'" method="post">
	<div id="modalAddEdge" class="modal hide not-fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Add a new edge</h3>
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
