<?php

$w	= (object) $words['add'];

$optType		= UI_HTML_Elements::Options( $words['types'], $mission->type );
#$optPriority	= UI_HTML_Elements::Options( $words['priorities'], $mission->priority );
#$optStatus		= UI_HTML_Elements::Options( $words['states'], $mission->status );

$optPriority	= array();
foreach( $words['priorities'] as $key => $value )
	$optPriority[]	= UI_HTML_Elements::Option( (string) $key, $value, $mission->priority == $key, NULL, 'mission priority'.$key );
$optPriority	= join( $optPriority );

$optStatus	= array();
foreach( $words['states'] as $key => $value )
	$optStatus[]	= UI_HTML_Elements::Option( (string) $key, $value, $mission->status == $key, NULL, 'mission status'.$key );
$optStatus	= join( $optStatus );

$optWorker	= array();
foreach( $users as $user )
	$optWorker[$user->userId]	= $user->username;
$optWorker		= UI_HTML_Elements::Options( $optWorker, $userId );

if( $useProjects ){
	$optProject	= array();
	foreach( $userProjects as $projectId => $project )
		$optProject[$projectId]	= $project->title;
	$optProject	= UI_HTML_Elements::Options( $optProject );
}

$hoursProjected		= floor( $mission->minutesProjected / 60 );
$minutesProjected	= str_pad( $mission->minutesProjected - $hoursProjected * 60, 2, "0", STR_PAD_LEFT );

$panelAdd	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		<form action="./work/mission/add" method="post">
			<div class="row-fluid">
				<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
				<input type="text" name="title" id="input_title" class="span12 -max" value="'.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'" required/>
			</div>
			<div class="row-fluid">
				<div class="span3 -column-left-20">
					<label for="input_priority">'.$w->labelPriority.'</label>
					<select name="priority" id="input_priority" class="span12 -max">'.$optPriority.'</select>
				</div>
				<div class="span3 -column-left-20">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12 -max">'.$optStatus.'</select>
				</div>
				<div class="span4 -column-left-40">
					<label for="input_projectId">'.$w->labelProjectId.'</label>
					<select name="projectId" id="input_projectId" class="span12 -max">'.$optProject.'</select>
				</div>
				<div class="span2 -column-left-20">
					<label for="input_workerId">'.$w->labelWorker.'</label>
					<select name="workerId" id="input_workerId" class="span12 -max">'.$optWorker.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span2 -column-left-20">
					<label for="input_type">'.$w->labelType.'</label>
					<select name="type" id="input_type" class="span12 -max" onchange="WorkMissionEditor.fallbackFormShowOptionals(this)">'.$optType.'</select>
				</div>
				<div class="span3 -column-left-20 optional type type-0">
					<label for="input_dayWork">'.$w->labelDayWork.'</label>
					<input type="text" name="dayWork" id="input_dayWork" class="span12 -max" value="'.$mission->dayStart.'" autocomplete="off"/>
				</div>
				<div class="span3 -column-left-20 optional type type-0">
					<label for="input_dayDue">'.$w->labelDayDue.'</label>
					<input type="text" name="dayDue" id="input_dayDue" class="span12 -max" value="'.$mission->dayEnd.'" autocomplete="off"/>
				</div>
				<div class="span3 -column-left-20 optional type type-1">
					<label for="input_dayStart">'.$w->labelDayStart.'</label>
					<input type="text" name="dayStart" id="input_dayStart" class="span12 -max" value="'.$mission->dayStart.'" autocomplete="off"/>
				</div>
				<div class="span2 -column-left-20 optional type type-1">
					<label for="input_timeStart">'.$w->labelTimeStart.'</label>
					<input type="text" name="timeStart" id="input_timeStart" class="span12 -max" value="'.$mission->timeStart.'" autocomplete="off"/>
				</div>
				<div class="span3 -column-left-20 optional type type-1">
					<label for="dayEnd">'.$w->labelDayEnd.'</label>
					<input type="text" name="dayEnd" id="input_dayEnd" class="span12 -max" value="'.$mission->dayEnd.'" autocomplete="off"/>
				</div>
				<div class="span2 -column-left-20 optional type type-1">
					<label for="input_timeEnd">'.$w->labelTimeEnd.'</label>
					<input type="text" name="timeEnd" id="input_timeEnd" class="span12 -max" value="'.$mission->timeEnd.'" autocomplete="off"/>
				</div>
				<div class="span2 -column-left-20 optional type type-0">
					<label for="input_minutesProjected">'.$w->labelMinutesProjected.'</label>
					<input type="text" name="minutesProjected" id="input_minutesProjected" class="span10 -xs numeric" value="'.$hoursProjected.':'.$minutesProjected.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5 -column-left-40">
					<label for="input_location">'.$w->labelLocation.'</label>
					<input type="text" name="location" id="input_location" class="span12 -max" value="'.htmlentities( $mission->location, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7 -column-left-40">
					<label for="input_reference">'.$w->labelReference.'</label>
					<input type="text" name="reference" id="input_reference" class="span12 -max" value="'.htmlentities( $mission->reference, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
<!--					<div class="content-panel content-panel-form">
						<div class="content-panel-inner">
							<h3>Editor</h3>
-->							<textarea id="input_content" name="content" rows="4" class="span12 -max -cmGrowText -cmClearInput">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
							<p>
								<span class="muted">Du kannst hier den <a href="http://de.wikipedia.org/wiki/Markdown" target="_blank">Markdown-Syntax</a> benutzen.</span>
							</p>
<!--						</div>
					</div>
-->				</div>
				<div class="span6">
<!--					<div class="content-panel content-panel-form">
						<div class="content-panel-inner">
							<h3>Beschreibung / Inhalt</h3>
-->							<div id="content-editor">
							<div id="descriptionAsMarkdown"></div>
<!--						</div>
					</div>
-->				</div>

<!--				<label for="input_content">'.$w->labelContent.'</label>
				<textarea id="input_content" name="content" class="span12 -max CodeMirror-auto" rows="7">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
-->			</div>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './work/mission', '<i class="icon-arrow-left"></i> '.$w->buttonCancel, 'btn' ).'
				<button type="submit" name="add" class="btn btn-success"><i class="icon-ok-circle icon-white"></i> '.$w->buttonSave.'</button>
	<!--			'.UI_HTML_Elements::LinkButton( './work/mission', $w->buttonCancel, 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'add', $w->buttonSave, 'button add' ).'-->
			</div>
		</form>
	</div>
</div>';

$panelInfo	= $view->loadContentFile( 'html/work/mission/add.info.html' );

return '
<div class="row-fluid">
	<div class="span9">
		'.$panelAdd.'
	</div>
	<div class="span3">
		'.$panelInfo.'
	</div>
</div>
<script src="javascripts/bindWithDelay.js"></script>
<script>
//var missionDay = '.( $day > 0 ? '+'.$day : $day ).';
//$(document).ready(function(){$("#input_title").focus()});
$("body").addClass("uses-bootstrap");
$(document).ready(function(){
	WorkMissionEditor.init(0);
//	$("#input_title").focus();
});
</script>';
?>