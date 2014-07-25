<?php

$panelInfo		= $view->loadTemplateFile( 'work/mission/edit.info.php' );
$panelClose		= $view->loadTemplateFile( 'work/mission/edit.close.php' );
$panelIssue		= $view->loadTemplateFile( 'work/mission/edit.issue.php' );
$panelContent	= $view->loadTemplateFile( 'work/mission/edit.content.php' );

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

$hoursProjected		= floor( $mission->minutesProjected / 60 );
$minutesProjected	= str_pad( $mission->minutesProjected - $hoursProjected * 60, 2, "0", STR_PAD_LEFT );

$hoursRequired		= floor( $mission->minutesRequired / 60 );
$minutesRequired	= str_pad( $mission->minutesRequired - $hoursRequired * 60, 2, "0", STR_PAD_LEFT );

$panelEdit	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		<form action="./work/mission/edit/'.$mission->missionId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12 -max" value="'.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
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
					<select name="type" id="input_type" class="span12 -max" onchange="WorkMissions.fallbackFormShowOptionals(this)">'.$optType.'</select>
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
					<label for="input_hoursProjected">'.$w->labelMinutesProjected.'</label>
					<input type="text" name="minutesProjected" id="input_minutesProjected" class="span12 -numeric" value="'.$hoursProjected.':'.$minutesProjected.'"/>
				</div>
				<div class="span2 -column-left-10 optional type type-0">
					<label for="input_minutesRequired">'.$w->labelMinutesRequired.'</label>
					<input type="text" name="minutesRequired" id="input_minutesRequired" class="span12 -numeric" value="'.$hoursRequired.':'.$minutesRequired.'"/>
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
				'.UI_HTML_Elements::LinkButton( './work/mission', '<i class="not-icon-arrow-left icon-list"></i> '.$w->buttonList, 'btn' ).'
				'.UI_HTML_Elements::LinkButton( './work/mission/view/'.$mission->missionId, '<i class="icon-eye-open icon-white"></i> '.$w->buttonView, 'btn btn-info' ).'
				'.UI_HTML_Elements::Button( 'edit', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-success' ).'
	<!--			&nbsp;|&nbsp;
				'.UI_HTML_Elements::LinkButton( './work/mission/setStatus/-2', '<i class="icon-remove icon-white"></i> '.$w->buttonCancel, 'btn btn-small btn-danger' ).'
				'.UI_HTML_Elements::LinkButton( './work/mission/setStatus/-3', '<i class="icon-trash icon-white"></i> '.$w->buttonRemove, 'btn btn-small btn-inverse' ).'
	<!--			'.UI_HTML_Elements::LinkButton( './work/mission', $w->buttonCancel, 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'edit', $w->buttonSave, 'button edit' ).'-->
			</div>
		</form>
	</div>
</div>';

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
<div class="row-fluid">
	<div class="span8">
		'.$panelEdit.'
<!--		<fieldset>
			<legend class="icon edit">Status setzen</legend>
			'.$states.'
		</fieldset>
		<fieldset>
			<legend class="icon edit">Priorität ändern</legend>
			'.$priorities.'
		</fieldset>-->
	</div>
	<div class="span4">
		'.$panelInfo.'
		'.$panelClose.'
		'.$panelIssue.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelContent.'
	</div>
</div>';
?>
