<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\View;

/** @var View $view */
/** @var array $words */
/** @var Entity_Mission $mission */
/** @var object[] $users */
/** @var object[] $userProjects */

$w	= (object) $words['add'];

$optType		= HtmlElements::Options( $words['types'], $mission->type );
#$optPriority	= HtmlElements::Options( $words['priorities'], $mission->priority );
#$optStatus		= HtmlElements::Options( $words['states'], $mission->status );

$optPriority	= [];
foreach( $words['priorities'] as $key => $value )
	$optPriority[]	= HtmlElements::Option( (string) $key, $value, $mission->priority == $key, FALSE, 'mission priority'.$key );
$optPriority	= join( $optPriority );

$optStatus	= [];
foreach( $words['states'] as $key => $value )
	if( $key >= 0 && $key <= 3 )
		$optStatus[]	= HtmlElements::Option( (string) $key, $value, $mission->status == $key, FALSE, 'mission status'.$key );
$optStatus	= join( $optStatus );

$optWorker	= [];
foreach( $users as $user )
	$optWorker[$user->userId]	= $user->username;
$optWorker		= HtmlElements::Options( $optWorker, $userId );

$optProject	= [];
foreach( $userProjects as $projectId => $project )
	$optProject[$projectId]	= $project->title;
$optProject	= HtmlElements::Options( $optProject, $mission->projectId );

$hoursProjected		= floor( $mission->minutesProjected / 60 );
$minutesProjected	= str_pad( $mission->minutesProjected - $hoursProjected * 60, 2, "0", STR_PAD_LEFT );
$timeProjected		= View_Work_Mission::formatSeconds( $mission->minutesProjected * 60 );

$fieldContent	= '';
if( strtoupper( $format ) == "HTML" ){
	$fieldContent	= '
	<div class="row-fluid">
		<div class="span12">
			<label for="input_content">'.$w->labelContent.'</label>
			<textarea id="input_content" name="content" rows="14" class="span12 TinyMCE-minimal" style="visibility: hidden">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
		</div>
	</div>';
}

$panelAdd	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span9">
				<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
				<input type="text" name="title" id="input_title" class="span12 -max" value="'.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'" required/>
			</div>
			<div class="span3 -column-left-20">
				<label for="input_priority">'.$w->labelPriority.'</label>
				<select name="priority" id="input_priority" class="span12 -max">'.$optPriority.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3 -column-left-20">
				<label for="input_status">'.$w->labelStatus.'</label>
				<select name="status" id="input_status" class="span12 -max">'.$optStatus.'</select>
			</div>
			<div class="span6 -column-left-40">
				<label for="input_projectId">'.$w->labelProjectId.'</label>
				<select name="projectId" id="input_projectId" class="span12 -max">'.$optProject.'</select>
			</div>
			<div class="span3 -column-left-20">
				<label for="input_workerId" class="mandatory required">'.$w->labelWorker.'</label>
				<select name="workerId" id="input_workerId" class="span12 -max" required="required">'.$optWorker.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2 -column-left-20">
				<label for="input_type">'.$w->labelType.'</label>
				<select name="type" id="input_type" class="span12 -max has-optionals">'.$optType.'</select>
			</div>
			<div class="span3 -column-left-20 optional type type-0" style="display: none">
				<label for="input_dayWork">'.$w->labelDayWork.'</label>
				<input type="date" name="dayWork" id="input_dayWork" class="span12 -max" value="'.$mission->dayStart.'" autocomplete="off"/>
			</div>
			<div class="span3 -column-left-20 optional type type-0" style="display: none">
				<label for="input_dayDue">'.$w->labelDayDue.'</label>
				<input type="date" name="dayDue" id="input_dayDue" class="span12 -max" value="'.$mission->dayEnd.'" autocomplete="off"/>
			</div>
			<div class="span3 -column-left-20 optional type type-1" style="display: none">
				<label for="input_dayStart">'.$w->labelDayStart.'</label>
				<input type="date" name="dayStart" id="input_dayStart" class="span12 -max" value="'.$mission->dayStart.'" autocomplete="off"/>
			</div>
			<div class="span2 -column-left-20 optional type type-1" style="display: none">
				<label for="input_timeStart">'.$w->labelTimeStart.'</label>
				<input type="time" name="timeStart" id="input_timeStart" class="span12 -max" value="'.$mission->timeStart.'" autocomplete="off"/>
			</div>
			<div class="span3 -column-left-20 optional type type-1" style="display: none">
				<label for="dayEnd">'.$w->labelDayEnd.'</label>
				<input type="date" name="dayEnd" id="input_dayEnd" class="span12 -max" value="'.$mission->dayEnd.'" autocomplete="off"/>
			</div>
			<div class="span2 -column-left-20 optional type type-1" style="display: none">
				<label for="input_timeEnd">'.$w->labelTimeEnd.'</label>
				<input type="time" name="timeEnd" id="input_timeEnd" class="span12 -max" value="'.$mission->timeEnd.'" autocomplete="off"/>
			</div>
<!--			<div class="span2 -column-left-20 optional type type-0" style="display: none">
				<label for="input_minutesProjected">'.$w->labelMinutesProjected.'</label>
				<input type="text" name="minutesProjected" id="input_minutesProjected" class="span10 -xs numeric" value="'.$hoursProjected.':'.$minutesProjected.'"/>
			</div>-->
			<div class="span2 -column-left-20 optional type type-0" style="display: none">
				<label for="input_timeProjected">'.$w->labelTimeProjected.'</label>
				<input type="text" name="timeProjected" id="input_timeProjected" class="span10 -xs numeric" value="'.$timeProjected.'"/>
			</div>
		</div>
<!--		<div class="row-fluid">
			<div class="span5 -column-left-40">
				<label for="input_location">'.$w->labelLocation.'</label>
				<input type="text" name="location" id="input_location" class="span12 -max" value="'.htmlentities( $mission->location, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span7 -column-left-40">
				<label for="input_reference">'.$w->labelReference.'</label>
				<input type="text" name="reference" id="input_reference" class="span12 -max" value="'.htmlentities( $mission->reference, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>-->
		'.$fieldContent.'
		<div class="buttonbar">
			'.HtmlElements::LinkButton( './work/mission', '<i class="icon-arrow-left"></i> '.$w->buttonCancel, 'btn' ).'
			<button type="submit" name="add" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> '.$w->buttonSave.'</button>
<!--			'.HtmlElements::LinkButton( './work/mission', $w->buttonCancel, 'button cancel' ).'
			'.HtmlElements::Button( 'add', $w->buttonSave, 'button add' ).'-->
		</div>
	</div>
</div>
';

$panelContent	= '';
if( strtoupper( $format ) === "MARKDOWN" ){

	$panelContentSplitted	= '
	<div class="row-fluid">
		<div class="span6">
			<div class="content-panel content-panel-form">
				<div class="content-panel-inner">
					<h3>Inhalt</h3>
					<textarea id="input_content" name="content" rows="4" class="span12 -max -cmGrowText -cmClearInput">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
					<p>
						<span class="muted">Du kannst hier den <a href="https://de.wikipedia.org/wiki/Markdown" target="_blank">Markdown-Syntax</a> benutzen.</span>
					</p>
				</div>
			</div>
		</div>
		<div class="span6">
			<div class="content-panel content-panel-form">
				<div class="content-panel-inner" style="min-height: 220px">
					<h3>Ansicht</h3>
					<div id="content-editor">
						<div id="mission-content-html"></div>
					</div>
				</div>
			</div>
		</div>
	</div>';

	$panelContentTabbed	= '
	<div class="content-panel">
		<h4>Inhalt / Beschreibung</h4>
		<div class="content-panel-inner">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab1" data-toggle="tab">Editor</a></li>
				<li><a href="#tab2" data-toggle="tab">Ansicht</a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="tab1">
					<div id="mirror-container">
						<textarea id="input_content" name="content" rows="22" class="span12 -max -cmGrowText -cmClearInput">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
						<p>
							<span class="muted">Du kannst hier den <a href="https://de.wikipedia.org/wiki/Markdown" target="_blank">Markdown-Syntax</a> benutzen.</span>
						</p>
					</div>
				</div>
				<div class="tab-pane" id="tab2">
					<div id="content-editor">
						<div id="mission-content-html"></div>
					</div>
				</div>
			</div>
		</div>
	</div>';

	$panelContent	= $panelContentTabbed;
}

$panelInfo	= $view->loadContentFile( 'html/work/mission/add.info.html' );

if( strlen( trim( $panelInfo ) ) ){
	$content	= '
		<div class="span9">
			'.$panelAdd.'
		</div>
		<div class="span3">
			'.$panelInfo.'
		</div>';
}
else {
	$content	= '
		<div class="span12">
			'.$panelAdd.'
		</div>';

}
return '
<form action="./work/mission/add" method="post" class="form-changes-auto">
	<input type="hidden" name="format" value="'.htmlentities( $mission->format, ENT_QUOTES, 'UTF-8' ).'"/>
	<div class="row-fluid">
		'.$content.'
	</div>
	'.$panelContent.'
</form>
<script>
//var missionDay = '.( $day > 0 ? '+'.$day : $day ).';
//$(document).ready(function(){$("#input_title").focus()});
$("body").addClass("uses-bootstrap");
$(document).ready(function(){
	WorkMissionsEditor.contentFormat = "'.$format.'";
	WorkMissionsEditor.init(0);
//	$("#input_type").trigger("change");
//	$("#input_title").focus();
});
</script>';
