<?php

$optGraph	= array();
foreach( $graphs as $graph )
	$optGraph[$graph->graphId]	= $graph->title;
$optGraph	= UI_HTML_Elements::Options( $optGraph );

$panelDetails	= '
<div class="content-panel">
	<h4>Edit this node</h4>
	<div class="content-panel-inner">
<!--		<input type="hidden" name="graphId" value="'.$graphId.'"/>-->
<!--		<div class="row-fluid">
			<div class="span12">
				<label for="input_graphId">Graph</label>
				<select name="graphId" id="input_graphId" class="span12">'.$optGraph.'</select>
			</div>
		</div>-->
		<div class="row-fluid">
			<div class="span4">
				<label for="input_ID">ID <small class="muted">(must be unique)</small></label>
				<input type="text" name="ID" id="input_ID" class="span12" value="'.htmlentities( $node->ID, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span8">
				<label for="input_label">Label <small class="muted">(should be unique)</small></label>
				<input type="text" name="label" id="input_label" class="span12" value="'.htmlentities( $node->label, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_description">Description</label>
				<textarea name="description" id="input_description" class="span12" rows="4">'.htmlentities( $node->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
			</div>
		</div>
	</div>
</div>';

$panelStyle	= '
<div class="content-panel">
	<h4>Node style</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<label for="input_shape">Shape</label>
				<input type="text" name="shape" id="input_shape" class="span12" value="'.htmlentities( $node->shape, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span6">
				<label for="input_style">Default node style <small class="muted">(try: filled)</small></label>
				<input type="text" name="style" id="input_style" class="span12" value="'.htmlentities( $node->style, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_color">Border color <small class="muted"></small></label>
				<input type="text" name="color" id="input_color" class="span12" value="'.htmlentities( $node->color, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_fillcolor">Fill color <small class="muted"></small></label>
				<input type="text" name="fillcolor" id="input_fillcolor" class="span12" value="'.htmlentities( $node->fillcolor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_width"><abbr title="default: 0.8">Width factor</abbr> <small class="muted"></small></label>
				<input type="text" name="width" id="input_width" class="span9" value="'.htmlentities( $node->width, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_height"><abbr title="default: 0.5">Height factor</abbr> <small class="muted"></small></label>
				<input type="text" name="height" id="input_height" class="span9" value="'.htmlentities( $node->height, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_fontcolor">Font color <small class="muted"></small></label>
				<input type="text" name="fontcolor" id="input_fontcolor" class="span12" value="'.htmlentities( $node->fontcolor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_fontsize"><abbr title="default: 14">Font size</abbr> <small class="muted"></small></label>
				<input type="text" name="fontsize" id="input_fontsize" class="span9" value="'.htmlentities( $node->fontsize, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
		</div>
	</div>
</div>';

$label	= $node->ID;
if( $node->label )
	$label	= $node->label.'&nbsp;<small class="muted">('.$label.')</small>';

return '
<form action="./work/graph/editNode/'.$nodeId.'" method="post">
	<div id="modalEditNode" class="modal hide not-fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><span class="muted">Node: </span>'.$label.'</h3>
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
