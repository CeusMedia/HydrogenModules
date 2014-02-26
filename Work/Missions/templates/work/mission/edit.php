<?php

$infos	= array();
if( isset( $mission->owner ) )
	$infos[]	= array(
		'label'	=> 'Erstellt von',
		'value'	=> '<span class="user role role'.$mission->owner->roleId.'">'.$mission->owner->username.'</span>'
	);
$infos[]	= array(
	'label'	=> 'aktueller Zustand',
	'value'	=> '<span class="mission-status status'.$mission->status.'">'.$words['states'][$mission->status].'</span>'
);
$infos[]	= array(
	'label'	=> 'Priorität',
	'value'	=> '<span class="mission-priority priority'.$mission->priority.'">'.$words['priorities'][$mission->priority].'</span>'
);
$infos[]	= array(
	'label'	=> 'Missionstyp',
	'value'	=> '<span class="mission-type type'.$mission->type.'">'.$words['types'][$mission->type].'</span>'
);
/*$infos[]	= array(
	'label'	=> '',
	'value'	=> ''
);
*/
if( isset( $mission->worker ) )
	$infos[]	= array(
		'label'	=> 'Zugewiesen an',
		'value'	=> '<span class="user role role'.$mission->worker->roleId.'">'.$mission->worker->username.'</span>'
	);

if( isset( $mission->modifiedAt ) )
	$infos[]	= array(
		'label'	=> 'Zuletzt geändert',
		'value'	=> '<span class="date">'.date( 'Y-m-d H:i', $mission->modifiedAt ).'</span>'
	);
if( count( $missionUsers ) > 1 ){
	$list	= array();
	foreach( $missionUsers as $user )
		$list[]	= UI_HTML_Tag::create( 'span', $user->username, array( 'class' => 'user role role'.$user->roleId ) );
	$infos[]	= array(
		'label'	=> 'Sichtbar für',
		'value'	=> join( '<br/>', $list )
	);
}
/*
if( isset( $mission->owner ) )
	$infos[]	= array(
		'label'	=> '',
		'value'	=> ''
	);
if( isset( $mission->owner ) )
	$infos[]	= array(
		'label'	=> '',
		'value'	=> ''
	);
*/

$panelInfo	= '';
if( count( $infos ) ){
	$list		= array();
	foreach( $infos as $info )
		$list[]	= UI_HTML_Tag::create( 'dt', $info['label'] ).UI_HTML_Tag::create( 'dd', $info['value'] );
	$list		= UI_HTML_Tag::create( 'dl', join( $list ) );
	$legend		= UI_HTML_Tag::create( 'legend', "Informationen", array( 'class' => "icon info" ) );
	$fieldset	= UI_HTML_Tag::create( 'fieldset', $legend.$list );
	$panelInfo	= $fieldset;
}

$panelClose	= '';
if( $mission->status > 0 ){
	$panelClose	= '
<form action="./work/mission/close/'.$mission->missionId.'" method="post">
	<fieldset>
		<legend class="icon mission-close">Abschliessen</legend>
		<div class="row-fluid">
			<div class="span7">
				<label for="input_hoursRequired2">Arbeitsstunden</label>
			</div>
			<div class="span5 input-append">
				<input type="text" name="hoursRequired" id="input_hoursRequired2" class="span8 -xs numeric" value="'.$mission->hoursRequired.'"/>
				<span class="add-on">h</span>
			</div>
		</div>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'close', 'speichern', 'button save' ).'
		</div>
	</fieldset>
</form>';
}


$w	= (object) $words['edit'];

$priorities		= $words['priorities'];
unset( $priorities[0] );

$optType		= UI_HTML_Elements::Options( $words['types'], $mission->type );

$optPriority	= array();
foreach( $priorities as $key => $value )
	$optPriority[]	= UI_HTML_Elements::Option( (string) $key, $value, $mission->priority == $key, NULL, 'mission priority'.$key );
$optPriority	= join( $optPriority, $mission->priority );

$optStatus	= array();
foreach( $words['states'] as $key => $value )
	$optStatus[]	= UI_HTML_Elements::Option( (string) $key, $value, $mission->status == $key, NULL, 'mission status'.$key );
$optStatus	= join( $optStatus, $mission->status );

$optWorker	= array();
foreach( $users as $user )
	$optWorker[$user->userId]	= $user->username;
$optWorker		= UI_HTML_Elements::Options( $optWorker, $mission->workerId );

if( $useProjects ){
	$optProject	= array();
	foreach( $userProjects as $projectId => $project )
		$optProject[$projectId]	= $project->title;
	$optProject	= UI_HTML_Elements::Options( $optProject, $mission->projectId );
}

$panelToIssue	= '';
if( $useIssues ){
#	print_m( $wordsIssue['types'] );
#	die;
	$panelToIssue	= '
<form action="./work/mission/convert/'.$mission->missionId.'/issue" method="post">
	<fieldset>
		<legend>Zu "Problem" konvertieren</legend>
		<ul class="input">
			<li class="column-left-40">
				<label for="input_title">Titel des Problems</label><br/>
				<input type="text" name="title" id="input_title" class="max"/>
			</li>
			<li class="column-left-20">
				<label for="input_status">Status</label><br/>
				<select type="text" name="status" id="input_status" class="max"></select>
			</li>
		</ul>
	</fieldset>
</form>';
}


$panelEdit	= '
<form action="./work/mission/edit/'.$mission->missionId.'" method="post">
	<fieldset>
		<legend class="icon edit">'.$w->legend.'</legend>
		<div class="row-fluid">
			<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
			<input type="text" name="title" id="input_title" class="span12 -max" value="'.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'" required/>
		</div>
		<div class="row-fluid">
			<div class="span3 -column-left-20">
				<label for="input_priority">'.$w->labelPriority.'</label>
				<select name="priority" id="input_priority" class="span12">'.$optPriority.'</select>
			</div>
			<div class="span3 -column-left-20">
				<label for="input_status">'.$w->labelStatus.'</label>
				<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
			</div>
			<div class="span4 -column-left-40">
				<label for="input_projectId">'.$w->labelProjectId.'</label>
				<select name="projectId" id="input_projectId" class="span12">'.$optProject.'</select>
			</div>
			<div class="span2 -column-left-20">
				<label for="input_workerId">'.$w->labelWorker.'</label>
				<select name="workerId" id="input_workerId" class="span12">'.$optWorker.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span2 -column-left-20">
				<label for="input_type">'.$w->labelType.'</label>
				<select name="type" id="input_type" class="span12 -max" onchange="showOptionals(this)">'.$optType.'</select>
			</div>
			<div class="span3 -column-left-20 optional type type-0">
				<label for="input_dayWork">'.$w->labelDayWork.'</label>
				<input type="text" name="dayWork" id="input_dayWork" value="'.$mission->dayStart.'" class="span12 -max" autocomplete="off"/>
			</div>
			<div class="span3 -column-left-20 optional type type-0">
				<label for="input_dayEnd">'.$w->labelDayDue.'</label>
				<input type="text" name="dayDue" id="input_dayDue" class="span12 -max cmClearInput" value="'.$mission->dayEnd.'" autocomplete="off"/>
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
			<div class="span2 -column-left-10 optional type type-0">
				<label for="input_hoursProjected">'.$w->labelHoursProjected.'</label>
				<div class="input-append">
					<input type="text" name="hoursProjected" id="input_hoursProjected" class="span3 numeric" value="'.$mission->hoursProjected.'"/>
					<span class="add-on">h</span>
				</div>
			</div>
			<div class="span2 -column-left-10 optional type type-0">
				<label for="input_hoursRequired">'.$w->labelHoursRequired.'</label>
				<div class="input-append">
					<input type="text" name="hoursRequired" id="input_hoursRequired" class="span3 numeric" value="'.$mission->hoursRequired.'"/>
					<span class="add-on">h</span>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span5 -column-left-40">
				<label for="input_location">'.$w->labelLocation.'</label>
				<input type="text" name="location" id="input_location" class="span12 -max cmClearInput" value="'.htmlentities( $mission->location, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span7 -column-left-40">
				<label for="input_reference">'.$w->labelReference.'</label>
				<input type="text" name="reference" id="input_reference" class="span12 -max cmClearInput" value="'.htmlentities( $mission->reference, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/mission', '<i class="icon-arrow-left"></i> '.$w->buttonCancel, 'btn' ).'
			'.UI_HTML_Elements::Button( 'edit', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-success' ).'
<!--			'.UI_HTML_Elements::LinkButton( './work/mission', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'edit', $w->buttonSave, 'button edit' ).'-->
		</div>
	</fieldset>
</form>
';

$panelContent	= '
<form>
	<div class="row-fluid">
		<div class="span6">
			<h3>Beschreibung / Inhalt</h3>
			<div id="content-editor">
				<div id="descriptionAsMarkdown"></div>
			</div>
		</div>
		<div class="span6">
			<h3>Editor</h3>
			<textarea id="input_content" name="content" rows="4" class="span12 -max -cmGrowText -cmClearInput">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
			<p>
				<span class="muted">Du kannst hier den <a href="http://de.wikipedia.org/wiki/Markdown" target="_blank">Markdown-Syntax</a> benutzen.</span>
			</p>
		</div>
	</div>
</form>';

/*
<!--	<fieldset>
		<legend>Beschreibung / Mitschrift</legend>
		<div class="row-fluid">
			<div class="span12">
-->				<div class="tabbable">
					<ul class="nav nav-tabs">
						<li class="active"><a href="#tab1" data-toggle="tab">Ansicht</a></li>
						<li><a href="#tab2" data-toggle="tab">Editor</a></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="tab1">
							<div id="content-editor">
								<div id="descriptionAsMarkdown"></div>
							</div>
						</div>
						<div class="tab-pane" id="tab2">
							<div id="mirror-container">
<!--							<label for="input_content">'.$w->labelContent.'</label>-->
								<textarea id="input_content" name="content" rows="4" class="span12 -max -cmGrowText -cmClearInput">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
								<p>
									<span class="muted">Du kannst hier den <a href="http://de.wikipedia.org/wiki/Markdown" target="_blank">Markdown-Syntax</a> benutzen.</span>
								</p>
							</div>
						</div>
					</div>
				</div>
<!--			</div>
		</div>
	</fieldset>
--></form>
*/


//  --  STATES  --  //
$states	= $words['states'];
unset( $states[0] );
foreach( $states as $status => $label )
	$states[$status]	= UI_HTML_Tag::create( 'button', $label, array(
		'type'		=> 'button',
		'onclick'	=> 'document.location.href=\'./work/mission/setStatus/'.$mission->missionId.'/'.urlencode( $status ).'\';',
		'disabled'	=> $mission->status == $status ? 'disabled' : NULL,
		'class'		=> 'button',
	) );
$states	= join( $states );

//  --  PRIORITIES  --  //
$priorities		= $words['priorities'];
unset( $priorities[0] );
foreach( $priorities as $priority => $label )
	$priorities[$priority]	= UI_HTML_Tag::create( 'button', $label, array(
		'type'		=> 'button',
		'onclick'	=> 'document.location.href=\'./work/mission/setPriority/'.$mission->missionId.'/'.$priority.'\';',
		'disabled'	=> $mission->priority == $priority ? 'disabled' : NULL,
		'class'		=> 'button',
	) );
$priorities	= join( $priorities );

return '
<style>
.tabbable .nav{
	margin-bottom: 1px;
}
</style>
<script src="javascripts/Markdown.Converter.js"></script>
<script src="javascripts/bindWithDelay.js"></script>
<script>
var missionId = '.$mission->missionId.';
$("body").addClass("uses-bootstrap");
$(document).ready(function(){
	WorkMissionEditor.init();
});
</script>
<style>
input.changed,
select.changed,
textarea.changed {
	background-color: #FFFFDF;
	}
.CodeMirror {
	border: 1px solid rgb(204, 204, 204);
	background-color: white;
	border-radius: 4px;
	z-index: 0;
	}
#descriptionAsMarkdown {
	padding: 0.5em 1em;
	}
#descriptionAsMarkdown h1,
#descriptionAsMarkdown h2,
#descriptionAsMarkdown h3,
#descriptionAsMarkdown h4,
#descriptionAsMarkdown h5 {
	line-height: 1.5em;
	padding: 0px;
	}
#descriptionAsMarkdown h1 {
	font-size: 2em;
	}
#descriptionAsMarkdown h2 {
	font-size: 1.6em;
	}
#descriptionAsMarkdown h3 {
	font-size: 1.4em;
	}
#descriptionAsMarkdown h4 {
	font-size: 1.2em;
	}
#descriptionAsMarkdown h5 {
	font-size: 1.1em;
	}
#descriptionAsMarkdown del {
	color: #777;
	}
</style>
<div class="row-fluid">
	<div class="span9">
		'.$panelEdit.'
		'.$panelToIssue.'
	<!--	<fieldset>
			<legend class="icon edit">Status setzen</legend>
			'.$states.'
		</fieldset>
		<fieldset>
			<legend class="icon edit">Priorität ändern</legend>
			'.$priorities.'
		</fieldset>-->
	</div>
	<div class="span3">
		'.$panelInfo.'
		'.$panelClose.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelContent.'
	</div>
</div>
';
?>
