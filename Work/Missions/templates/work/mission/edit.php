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
	$list	= array();
	foreach( $infos as $info )
		$list[]	= UI_HTML_Tag::create( 'dt', $info['label'] ).UI_HTML_Tag::create( 'dd', $info['value'] );
	$list	= UI_HTML_Tag::create( 'dl', join( $list ) );
$panelInfo	= '
<fieldset>
	<legend class="icon info">Informationen</legend>
	'.$list.'
</fieldset>
';
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
		<ul class="input">
			<li class="-column-left-80">
				<label for="input_content">'.$w->labelContent.'</label><br/>
				<input type="text" name="content" id="input_content" class="max" value="'.htmlentities( $mission->content, ENT_QUOTES, 'UTF-8' ).'"/>
			</li>
			<li>
				<div class="column-left-20">
					<label for="input_priority">'.$w->labelPriority.'</label><br/>
					<select name="priority" id="input_priority" class="max">'.$optPriority.'</select>
				</div>
				<div class="column-left-20">
					<label for="input_status">'.$w->labelStatus.'</label><br/>
					<select name="status" id="input_status" class="max">'.$optStatus.'</select>
				</div>
				<div class="column-left-40">
					<label for="input_projectId">'.$w->labelProjectId.'</label><br/>
					<select name="projectId" id="input_projectId" class="max">'.$optProject.'</select>
				</div>
				<div class="column-left-20">
					<label for="input_content">'.$w->labelWorker.'</label><br/>
					<select name="workerId" id="input_workerId" class="max">'.$optWorker.'</select>
				</div>
				<div class="column-clear"></div>
			</li>
			<li class="">
				<div class="column-left-20">
					<label for="input_type">'.$w->labelType.'</label><br/>
					<select name="type" id="input_type" class="max" onchange="showOptionals(this)">'.$optType.'</select>
				</div>
				<div class="column-left-20 optional type type-0">
					<label for="input_day">'.$w->labelDay.'</label><br/>
					<input type="text" name="day" id="input_day" value="'.$mission->dayStart.'" class="max" autocomplete="off"/>
				</div>
				<div class="column-left-20 optional type type-1">
					<label for="input_dayStart">'.$w->labelDayStart.'</label><br/>
					<input type="text" name="dayStart" id="input_dayStart" class="max" value="'.$mission->dayStart.'" autocomplete="off"/>
				</div>
				<div class="column-left-20 optional type type-1">
					<label for="dayEnd">'.$w->labelDayEnd.'</label><br/>
					<input type="text" name="dayEnd" id="input_dayEnd" class="max" value="'.$mission->dayEnd.'" autocomplete="off"/>
				</div>
				<div class="column-left-20 optional type type-1">
					<label for="input_timeStart">'.$w->labelTimeStart.'</label><br/>
					<input type="text" name="timeStart" id="input_timeStart" class="max" value="'.$mission->timeStart.'" autocomplete="off"/>
				</div>
				<div class="column-left-20 optional type type-1">
					<label for="input_timeEnd">'.$w->labelTimeEnd.'</label><br/>
					<input type="text" name="timeEnd" id="input_timeEnd" class="max" value="'.$mission->timeEnd.'" autocomplete="off"/>
				</div>
				<div class="column-clear"></div>
			</li>
			<li>
				<div class="column-left-40">
					<label for="input_location">'.$w->labelLocation.'</label><br/>
					<input type="text" name="location" id="input_location" class="max" value="'.htmlentities( $mission->location, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="column-left-60">
					<label for="input_reference">'.$w->labelReference.'</label><br/>
					<input type="text" name="reference" id="input_reference" class="max" value="'.htmlentities( $mission->reference, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="column-clear"></div>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/mission', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'edit', $w->buttonSave, 'button edit' ).'
		</div>
	</fieldset>	
</form>
';

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
<div class="column-left-75">
	'.$panelEdit.'
	'.$panelToIssue.'
	<fieldset>
		<legend>Status setzen</legend>
		'.$states.'
	</fieldset>
	<fieldset>
		<legend>Priorität ändern</legend>
		'.$priorities.'
	</fieldset>
</div>
<div class="column-right-25">
	'.$panelInfo.'
</div>
<div class="column-clear"></div>';
?>
