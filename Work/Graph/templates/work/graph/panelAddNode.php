<?php

$optGraph	= array();
foreach( $graphs as $graph )
	$optGraph[$graph->graphId]	= $graph->title;
$optGraph	= UI_HTML_Elements::Options( $optGraph );

return '
<div class="content-panel">
	<h4>Add a node</h4>
	<div class="content-panel-inner">
		<form action="./work/graph/addNode/'.$graphId.'" method="post">
			<input type="hidden" name="graphId" value="'.$graphId.'"/>
<!--			<div class="row-fluid">
				<div class="span12">
					<label for="input_graphId">Graph</label>
					<select name="graphId" id="input_graphId" class="span12">'.$optGraph.'</select>
				</div>
			</div>-->
			<div class="row-fluid">
				<div class="span12">
					<label for="input_ID">ID <small class="muted">(must be unique)</small></label>
					<input type="text" name="ID" id="input_ID" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_label">Label <small class="muted">(should be unique)</small></label>
					<input type="text" name="label" id="input_label" class="span12"/>
				</div>
			</div>


			<div class="row-fluid">
				<div class="span12">
					<label for="input_shape">Shape</label>
					<input type="text" name="shape" id="input_shape" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_style">Default node style <small class="muted">(try: filled)</small></label>
					<input type="text" name="style" id="input_style" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_color">Border color <small class="muted"></small></label>
					<input type="text" name="color" id="input_color" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_fillcolor">Fill color <small class="muted"></small></label>
					<input type="text" name="fillcolor" id="input_fillcolor" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_width">Width factor <small class="muted">(default: 0.8, try: 0.6)</small></label>
					<input type="text" name="width" id="input_width" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_height">Height factor <small class="muted">(default: 0.5, try: 0)</small></label>
					<input type="text" name="height" id="input_height" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_fontsize">Font size <small class="muted">(default: 14, try: 12)</small></label>
					<input type="text" name="fontsize" id="input_fontsize" class="span12"/>
				</div>
			</div>


			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> save</button>
			</div>
		</form>
	</div>
</div>';
