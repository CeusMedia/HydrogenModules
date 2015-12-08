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

return '
<div class="content-panel">
	<h4>Edit this edge</h4>
	<div class="content-panel-inner">
		<form action="./work/graph/editEdge/'.( $nodeId ? $edgeId.'/'.$nodeId : $edgeId ).'" method="post">
<!--			<div class="row-fluid">
				<div class="span12">
					<label for="input_graphId">Graph</label>
					<select name="graphId" id="input_graphId" class="span12">'.$optGraph.'</select>
				</div>
			</div>-->
			<div class="row-fluid">
				<div class="span12">
					<label for="input_fromNodeId">From node</label>
					<select name="fromNodeId" id="input_fromNodeId" class="span12">'.$optFromNode.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
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
			<div class="row-fluid">
				<div class="span12">
					<label for="input_arrowhead">Arrow head <small class="muted"></small></label>
					<input type="text" name="arrowhead" id="input_arrowhead" class="span12" value="'.htmlentities( $edge->arrowhead, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_arrowsize">Arrow size <small class="muted"></small></label>
					<input type="text" name="arrowsize" id="input_arrowsize" class="span12" value="'.htmlentities( $edge->arrowsize, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_color">Line color <small class="muted"></small></label>
					<input type="text" name="color" id="input_color" class="span12" value="'.htmlentities( $edge->color, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_fontcolor">Font color <small class="muted"></small></label>
					<input type="text" name="fontcolor" id="input_fontcolor" class="span12" value="'.htmlentities( $edge->fontcolor, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_fontsize">Font size <small class="muted"></small></label>
					<input type="text" name="fontsize" id="input_fontsize" class="span12" value="'.htmlentities( $edge->fontsize, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> save</button>
			</div>
		</form>
	</div>
</div>';