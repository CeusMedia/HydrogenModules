<?php

$optGraph	= array();
foreach( $graphs as $graph )
	$optGraph[$graph->graphId]	= $graph->title;
$optGraph	= UI_HTML_Elements::Options( $optGraph );

$optType	= array( "static" => "static", "graph" => "graph", "digraph" => "digraph" );
$optRankdir	= array( "LR" => "LR", "RL" => "RL" );

$optType		= UI_HTML_Elements::Options( $optType, $graph->type );
$optRankdir		= UI_HTML_Elements::Options( $optRankdir, $graph->rankdir );

return '
<div class="content-panel">
	<h4>Edit graph</h4>
	<div class="content-panel-inner">
		<form action="./work/graph/editGraph/'.$graphId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Title</label>
					<input type="text" name="" id="input_title" class="span12" value="'.htmlentities( $graph->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_type">Type</label>
					<select name="type" id="input_type" class="span12">'.$optType.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_rankdir">Rankdir</label>
					<select name="rankdir" id="input_rankdir" class="span12">'.$optRankdir.'</select>
				</div>
			</div>
			<h5>Default node style settings</h5>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_nodeShape">Default node shape</label>
					<input type="text" name="nodeShape" id="input_nodeShape" class="span12" value="'.htmlentities( $graph->nodeShape, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_nodeStyle">Default node style <small class="muted">(try: filled)</small></label>
					<input type="text" name="nodeStyle" id="input_nodeStyle" class="span12" value="'.htmlentities( $graph->nodeStyle, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_nodeColor">Default node color <small class="muted"></small></label>
					<input type="text" name="nodeColor" id="input_nodeColor" class="span12" value="'.htmlentities( $graph->nodeColor, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_nodeFillcolor">Default node fill color <small class="muted"></small></label>
					<input type="text" name="nodeFillcolor" id="input_nodeFillcolor" class="span12" value="'.htmlentities( $graph->nodeFillcolor, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_nodeWidth">Default node width factor <small class="muted">(default: 0.8, try: 0.6)</small></label>
					<input type="text" name="nodeWidth" id="input_nodeWidth" class="span12" value="'.htmlentities( $graph->nodeWidth, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_nodeHeight">Default node height factor <small class="muted">(default: 0.5, try: 0)</small></label>
					<input type="text" name="nodeHeight" id="input_nodeHeight" class="span12" value="'.htmlentities( $graph->nodeHeight, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_nodeFontsize">Default node font size <small class="muted">(default: 14, try: 12)</small></label>
					<input type="text" name="nodeFontsize" id="input_nodeFontsize" class="span12" value="'.htmlentities( $graph->nodeFontsize, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<h5>Default edge style settings</h5>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_edgeArrowhead">Default edge arrow head <small class="muted"></small></label>
					<input type="text" name="edgeArrowhead" id="input_edgeArrowhead" class="span12" value="'.htmlentities( $graph->edgeArrowhead, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_edgeArrowsize">Default edge arrow size <small class="muted"></small></label>
					<input type="text" name="edgeArrowsize" id="input_edgeArrowsize" class="span12" value="'.htmlentities( $graph->edgeArrowsize, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_edgeColor">Default edge line color <small class="muted"></small></label>
					<input type="text" name="edgeColor" id="input_edgeColor" class="span12" value="'.htmlentities( $graph->edgeColor, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_edgeFontcolor">Default edge font color <small class="muted"></small></label>
					<input type="text" name="edgeFontcolor" id="input_edgeFontcolor" class="span12" value="'.htmlentities( $graph->edgeFontcolor, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_edgeFontsize">Default edge font size <small class="muted"></small></label>
					<input type="text" name="edgeFontsize" id="input_edgeFontsize" class="span12" value="'.htmlentities( $graph->edgeFontsize, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>

			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> save</button>
			</div>
		</form>
	</div>
</div>';
