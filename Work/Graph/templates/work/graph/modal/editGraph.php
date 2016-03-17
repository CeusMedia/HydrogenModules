<?php

$optGraph	= array();
foreach( $graphs as $item )
	$optGraph[$item->graphId]	= $item->title;
$optGraph	= UI_HTML_Elements::Options( $optGraph );

$optType	= array( "static" => "static", "graph" => "graph", "digraph" => "digraph" );
$optRankdir	= array( "LR" => "LR", "RL" => "RL" );

$optType		= UI_HTML_Elements::Options( $optType, $graph->type );
$optRankdir		= UI_HTML_Elements::Options( $optRankdir, $graph->rankdir );

$panelDetails	= '
<div class="content-panel">
	<h4>Graph settings</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<label for="input_title">Title</label>
				<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $graph->title, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_type">Type</label>
				<select name="type" id="input_type" class="span12">'.$optType.'</select>
			</div>
			<div class="span2">
				<label for="input_rankdir">Rankdir</label>
				<select name="rankdir" id="input_rankdir" class="span9">'.$optRankdir.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_description">Description</label>
				<textarea name="description" id="input_description" class="span12" rows="4">'.htmlentities( $graph->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
			</div>
		</div>
	</div>
</div>';

$panelNode	= '
<div class="content-panel">
	<h4>Default node style settings</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<label for="input_nodeShape">Shape</label>
				<input type="text" name="nodeShape" id="input_nodeShape" class="span12" value="'.htmlentities( $graph->nodeShape, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span6">
				<label for="input_nodeStyle">Style <small class="muted">(try: filled)</small></label>
				<input type="text" name="nodeStyle" id="input_nodeStyle" class="span12" value="'.htmlentities( $graph->nodeStyle, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_nodeColor">Border color <small class="muted"></small></label>
				<input type="text" name="nodeColor" id="input_nodeColor" class="span12" value="'.htmlentities( $graph->nodeColor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_nodeFillcolor">Fill color <small class="muted"></small></label>
				<input type="text" name="nodeFillcolor" id="input_nodeFillcolor" class="span12" value="'.htmlentities( $graph->nodeFillcolor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_nodeWidth"><abbr title="default: 0.8">Width factor</abbr> <small class="muted"></small></label>
				<input type="text" name="nodeWidth" id="input_nodeWidth" class="span9" value="'.htmlentities( $graph->nodeWidth, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_nodeHeight"><abbr title="default: 0.5">Height factor</abbr> <small class="muted"></small></label>
				<input type="text" name="nodeHeight" id="input_nodeHeight" class="span9" value="'.htmlentities( $graph->nodeHeight, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_nodeFontcolor">Font color <small class="muted"></small></label>
				<input type="text" name="nodeFontcolor" id="input_nodeFontcolor" class="span12" value="'.htmlentities( $graph->nodeFontcolor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_nodeFontsize"><abbr title="default: 14">Font size</abbr> <small class="muted"></small></label>
				<input type="text" name="nodeFontsize" id="input_nodeFontsize" class="span9" value="'.htmlentities( $graph->nodeFontsize, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
		</div>
	</div>
</div>';

$panelEdge	= '
<div class="content-panel">
	<h4>Default edge style settings</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<label for="input_edgeArrowhead">Arrow head <small class="muted"></small></label>
				<input type="text" name="edgeArrowhead" id="input_edgeArrowhead" class="span12" value="'.htmlentities( $graph->edgeArrowhead, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_edgeArrowsize">Arrow size <small class="muted"></small></label>
				<input type="text" name="edgeArrowsize" id="input_edgeArrowsize" class="span9" value="'.htmlentities( $graph->edgeArrowsize, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_edgeColor">Line color <small class="muted"></small></label>
				<input type="text" name="edgeColor" id="input_edgeColor" class="span12" value="'.htmlentities( $graph->edgeColor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_edgeFontcolor">Font color <small class="muted"></small></label>
				<input type="text" name="edgeFontcolor" id="input_edgeFontcolor" class="span12" value="'.htmlentities( $graph->edgeFontcolor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_edgeFontsize">Font size <small class="muted"></small></label>
				<input type="text" name="edgeFontsize" id="input_edgeFontsize" class="span9" value="'.htmlentities( $graph->edgeFontsize, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
	</div>
</div>';

return '
<form action="./work/graph/editGraph/'.$graphId.'" method="post">
	<div id="modalEditGraph" class="modal hide not-fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><span class="muted">Graph: </span>'.$graph->title.'</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span12">
					'.$panelDetails.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.$panelNode.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.$panelEdge.'
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
