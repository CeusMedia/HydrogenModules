<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var array<string,array<string,string>> $words */
$wg	= (object) $words['general'];
$wf	= (object) $words['fields'];
$wm	= (object) $words['modal.addNode'];

$optGraph	= [];
foreach( $graphs as $graph )
	$optGraph[$graph->graphId]	= $graph->title;
$optGraph	= HtmlElements::Options( $optGraph );

$panelDetails	= '
<div class="content-panel">
	<h4>'.$wm->headingDetails.'</h4>
	<div class="content-panel-inner">
		<input type="hidden" name="graphId" value="'.$graphId.'"/>
<!--		<div class="row-fluid">
			<div class="span12">
				<label for="input_graphId">Graph</label>
				<select name="graphId" id="input_graphId" class="span12">'.$optGraph.'</select>
			</div>
		</div>-->
		<div class="row-fluid">
			<div class="span4">
				<label for="input_ID">'.$wf->labelId.' <small class="muted">'.$wf->hintMustBeUnique.'</small></label>
				<input type="text" name="ID" id="input_ID" class="span12"/>
			</div>
			<div class="span8">
				<label for="input_label">'.$wf->labelLabel.' <small class="muted">'.$wf->hintShouldBeUnique.'</small></label>
				<input type="text" name="label" id="input_label" class="span12"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_description">'.$wf->labelDescription.'</label>
				<textarea name="description" id="input_description" class="span12" rows="4"></textarea>
			</div>
		</div>
	</div>
</div>';

$panelStyle	= '
<div class="content-panel">
	<h4>'.$wm->headingStyle.'</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<label for="input_shape">'.$wf->labelShape.'</label>
				<input type="text" name="shape" id="input_shape" class="span12"/>
			</div>
			<div class="span6">
				<label for="input_style">'.$wf->labelStyle.' <small class="muted"></small></label>
				<input type="text" name="style" id="input_style" class="span12"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_color">'.$wf->labelBorderColor.' <small class="muted"></small></label>
				<input type="text" name="color" id="input_color" class="span12"/>
			</div>
			<div class="span3">
				<label for="input_fillcolor">'.$wf->labelFillColor.' <small class="muted"></small></label>
				<input type="text" name="fillcolor" id="input_fillcolor" class="span12"/>
			</div>
			<div class="span3">
				<label for="input_width"><abbr title="'.$wf->titleWidthFactor.'">'.$wf->labelWidthFactor.'</abbr> <small class="muted"></small></label>
				<input type="text" name="width" id="input_width" class="span9"/>
			</div>
			<div class="span3">
				<label for="input_height"><abbr title="'.$wf->titleHeightFactor.'">'.$wf->labelHeightFactor.'</abbr>  <small class="muted"></small></label>
				<input type="text" name="height" id="input_height" class="span9"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_fontcolor">'.$wf->labelFontColor.' <small class="muted"></small></label>
				<input type="text" name="fontcolor" id="input_fontcolor" class="span12"/>
			</div>
			<div class="span3">
				<label for="input_fontsize"><abbr title="'.$wf->titleFontSize.'">'.$wf->labelFontSize.'</abbr> <small class="muted"></small></label>
				<input type="text" name="fontsize" id="input_fontsize" class="span9"/>
			</div>
		</div>
	</div>
</div>';

return '
<form action="./work/graph/addNode/'.$graphId.'" method="post">
	<div id="modalAddNode" class="modal hide not-fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>'.$wm->heading.'</h3>
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
			<a class="btn" data-dismiss="modal">'.$wg->buttonClose.'</a>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$wg->buttonSave.'</button>
		</div>
	</div>
</form>
';
