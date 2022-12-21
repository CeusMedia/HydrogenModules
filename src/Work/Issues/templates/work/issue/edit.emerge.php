<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$optType		= $view->renderOptions( $words['types'], 'type', $issue->type, 'issue-type type-%1$d');
$optSeverity	= $view->renderOptions( $words['severities'], 'severity', $issue->severity, 'issue-severity severity-%1$d');
$optPriority	= $view->renderOptions( $words['priorities'], 'priority', $issue->priority, 'issue-priority priority-%1$d');
$optStatus		= $view->renderOptions( $words['states'], 'status', $issue->status, 'issue-status status-%1$d');

//  --  PROJECT RELATION  --  //
$optProject = ['_selected' => $issue->projectId];
if( !empty( $projects ) )
	foreach( $projects as $project )
		$optProject[$project->projectId]	= $project->title;

//  --  USER RELATIONS  --  //
/*
$modelUser	= new Model_User( $env );
$conditions	= ['status' => '>0'];
$orders		= ['username' => 'ASC'];
$users		= $modelUser->getAll( $conditions, $orders, [100] );
*/

$optReporter	= [];
$optManager	= ['' => '-'];
$optWorker	= ['' => '-'];

foreach( $users as $user ){
	$optReporter[$user->userId]	= $user->username;
	$optManager[$user->userId]	= $user->username;
	$optWorker[$user->userId]	= $user->username;
}
$optReporter	= HtmlElements::Options( $optReporter, $issue->reporterId );
$optManager		= HtmlElements::Options( $optManager, $issue->managerId );
$optWorker		= HtmlElements::Options( $optWorker, $issue->managerId );

/*
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-success btn-small btn-mini',
) );
*/

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
		<form action="./work/issue/emerge/'.$issue->issueId.'" method="post" class="cmFormChange-auto">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_projectId">'.$words['edit']['labelProject'].'</label>
					'.HtmlElements::Select( 'projectId', $optProject, 'span12 -max' ).'
				</div>
				<div class="span4">
					<label for="input_reporterId">'.$words['edit']['labelReporter'].'</label>
					<select id="input_reporterId" name="reporterId" class="span12">'.$optReporter.'</select>
				</div>
				<div class="span4">
					<label for="input_managerId">'.$words['edit']['labelManager'].'</label>
					<select id="input_managerId" name="managerId" class="span12">'.$optManager.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="type">'.$words['edit']['labelType'].'</label>
					'.HtmlElements::Select( 'type', $optType, 'span12 -max' ).'
				</div>
				<div class="span4">
					<label for="severity">'.$words['edit']['labelSeverity'].'</label>
					'.HtmlElements::Select( 'severity', $optSeverity, 'span12 -max' ).'
				</div>
				<div class="span4">
					<label for="priority">'.$words['edit']['labelPriority'].'</label>
					'.HtmlElements::Select( 'priority', $optPriority, 'span12 -max' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="status">'.$words['edit']['labelStatus'].'</label>
					'.HtmlElements::Select( 'status', $optStatus, 'span12 -max' ).'
				</div>
				<div class="span3">
					<label for="progress">'.$words['edit']['labelProgress'].': <span id="progress-view"></span></label>
					'.HtmlElements::Input( 'progress', (int) $issue->progress, 's numeric' ).'
					<div id="progress-slider" style="display: none; margin-top: 1em"></div>
				</div>
				<div class="span1 muted">
					<label for="input_time"><abbr title="Aufwand in Stunden und Minuten">Zeit</abbr></label>
					<input type="text" name="time" id="input_time" class="span12 -numeric" value="0:00">
				</div>
<!--				<div class="4">
					<label for="input_workerId">'.$words['edit']['labelChanger'].'</label>
					<select id="input_workderId" name="workerId" class="span12">'.$optWorker.'</select>
				</div>-->
			</div>
			<div class="row-fluid">
				<div class="span6 muted">
					<strike>
					<label class="checkbox">
						<input type="checkbox" name="inform" id="input_inform" value="1" checked="checked"/>
						Beteiligte per E-Mail informieren
					</label>
				</strike>
				</div>
			</div>
			<div class="row-fluid">
				<label for="content">'.$words['edit']['labelContent'].'</label>
				'.HtmlTag::create( 'textarea', '', ['name' => 'note', 'class' => 'span12 -max CodeMirror-auto', 'rows' => 8] ).'
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-info"><i class="icon-ok icon-white"></i> aktualisieren</button>
			</div>
		</form>
	</div>
</div>';
?>
<?php

return '
<div class="content-panel">
	<h3>Zuweisung</h3>
	<div class="content-panel-inner">
		<form action="./work/issue/edit/'.$issue->issueId.'" method="post">
			<div class="buttonbar">
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';
?>
