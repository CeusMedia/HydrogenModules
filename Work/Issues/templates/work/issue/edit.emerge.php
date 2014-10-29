<?php
$optType		= $view->renderOptions( $words['types'], 'type', $issue->type, 'issue-type type-%1$d');
$optSeverity	= $view->renderOptions( $words['severities'], 'severity', $issue->severity, 'issue-severity severity-%1$d');
$optPriority	= $view->renderOptions( $words['priorities'], 'priority', $issue->priority, 'issue-priority priority-%1$d');
$optStatus		= $view->renderOptions( $words['states'], 'status', $issue->status, 'issue-status status-%1$d');

return '
	<script>
function getColor(ratio,opacity){
	opacity	= typeof opacity == "undefined" ? 1 : opacity;
	ratio	= Math.max(0,Math.min(1,ratio));									//  keep ratio between 0 and 1
	var colorR	= ( 1 - ratio ) > 0.5 ? 255 : Math.round( ( 1 - ratio ) * 2 * 255 );
	var colorG	= ratio > 0.5 ? 255 : Math.round( ratio * 2 * 255 );
	return "rgba(" + colorR + "," + colorG + ",0,"+opacity+")";
}

function updateSlider(value, opacity){
	$("#progress-slider.ui-slider").css("background", getColor(value/100, opacity));
	$("#progress").val(value);
	$("#progress-view").html(value + "%");
}

$(document).ready(function(){
	var value = parseInt($("#progress").hide().val());
	$("#progress-slider").slider({
		value: value,
		min: 0,
		max: 100,
		step: 10,
		slide: function(event, ui){
			updateSlider(ui.value, 0.5);
		}
	}).fadeIn("slow");
	updateSlider(value, 0.5);
});

</script>
<div class="content-panel">
	<h3>Eintrag bearbeiten</h3>
	<div class="content-panel-inner">
		<form action="./work/issue/emerge/'.$issue->issueId.'" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="type">'.$words['edit']['labelType'].'</label>
					'.UI_HTML_Elements::Select( 'type', $optType, 'max' ).'
				</div>
<!--			<div class="span3">
					<label for="severity">'.$words['edit']['labelSeverity'].'</label>
					'.UI_HTML_Elements::Select( 'severity', $optSeverity, 'span12 -max' ).'
				</div>
-->				<div class="span3">
					<label for="priority">'.$words['edit']['labelPriority'].'</label>
					'.UI_HTML_Elements::Select( 'priority', $optPriority, 'span12 -max' ).'
				</div>
				<div class="span3">
					<label for="status">'.$words['edit']['labelStatus'].'</label>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'span12 -max' ).'
				</div>
				<div class="span3">
					<label for="progress">'.$words['edit']['labelProgress'].': <span id="progress-view"></span></label>
					'.UI_HTML_Elements::Input( 'progress', (int) $issue->progress, 's numeric' ).'
					<div id="progress-slider" style="display: none; margin-top: 1em"></div>
				</div>
			</div>
			<div class="row-fluid">
				<label for="content">'.$words['edit']['labelContent'].'</label>
				'.UI_HTML_Tag::create( 'textarea', '', array( 'name' => 'note', 'class' => 'span12 -max CodeMirror', 'rows' => 8 ) ).'
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-info"><i class="icon-ok icon-white"></i> aktualisieren</button>
			</div>
		</form>
	</div>
</div>';
?>
