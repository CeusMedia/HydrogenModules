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
	foreach( $userProjects as $relation )
		if( $projects[$relation->projectId]->status >= 0 )
			$optProject[$relation->projectId]	= $projects[$relation->projectId]->title;
	$optProject	= UI_HTML_Elements::Options( $optProject );
}

$panelAdd	= '
<form action="./work/mission/add" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
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
					<label for="input_workerId">'.$w->labelWorker.'</label><br/>
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
					<input type="text" name="day" id="input_day" class="max" value="'.$mission->dayStart.'" autocomplete="off"/>
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
			'.UI_HTML_Elements::Button( 'add', $w->buttonSave, 'button add' ).'
		</div>
	</fieldset>	
</form>
';

$panelInfo	= $view->loadContentFile( 'html/work/mission/add.info.html' );

return '
<script>
var missionDay = '.( $day > 0 ? '+'.$day : $day ).';
$(document).ready(function(){$("#input_content").focus()});
</script>
<div class="column-right-30">
	'.$panelInfo.'
</div>
<div class="column-left-70">
	'.$panelAdd.'
</div>
<div class="column-clear"></div>
';

?>

