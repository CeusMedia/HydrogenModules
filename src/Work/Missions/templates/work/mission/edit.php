<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */
/** @var object $mission */
/** @var array $missionUsers */
/** @var array<object> $users */
/** @var array<object> $userProjects */
/** @var string $format */

$panelInfo		= $view->loadTemplateFile( 'work/mission/edit.info.php' );
$panelClose		= $view->loadTemplateFile( 'work/mission/edit.close.php' );
$panelIssue		= $view->loadTemplateFile( 'work/mission/edit.issue.php' );
$panelContent	= $view->loadTemplateFile( 'work/mission/edit.content.php' );
$panelTime		= $view->loadTemplateFile( 'work/mission/edit.time.php' );
$panelDocuments	= $view->loadTemplateFile( 'work/mission/edit.documents.php' );

$w	= (object) $words['edit'];

$priorities		= $words['priorities'];
unset( $priorities[0] );

$optType		= HtmlElements::Options( $words['types'], $mission->type );

$optPriority	= [];
foreach( $priorities as $key => $value )
	$optPriority[]	= HtmlElements::Option( (string) $key, $value, $mission->priority == $key, NULL, 'mission priority'.$key );
$optPriority	= join( $optPriority/*, $mission->priority*/ );

$optStatus		= [];
$wordsStatus	= $words['states'];
unset( $wordsStatus[-3] );
unset( $wordsStatus[-2] );
unset( $wordsStatus[4] );
foreach( $wordsStatus as $key => $value )
	$optStatus[]	= HtmlElements::Option( (string) $key, $value, $mission->status == $key, NULL, 'mission status'.$key );
$optStatus	= join( $optStatus/*, $mission->status*/ );

$optWorker	= [];
foreach( $users as $user )
	$optWorker[$user->userId]	= $user->username;
$optWorker		= HtmlElements::Options( $optWorker, $mission->workerId );

$optProject	= [];
foreach( $userProjects as $projectId => $project )
	$optProject[$projectId]	= $project->title;
$optProject	= HtmlElements::Options( $optProject, $mission->projectId );

$timeProjected		= View_Work_Mission::formatSeconds( $mission->minutesProjected * 60 );


$hoursProjected		= floor( $mission->minutesProjected / 60 );
$minutesProjected	= str_pad( $mission->minutesProjected - $hoursProjected * 60, 2, "0", STR_PAD_LEFT );

$hoursRequired		= floor( $mission->minutesRequired / 60 );
$minutesRequired	= str_pad( $mission->minutesRequired - $hoursRequired * 60, 2, "0", STR_PAD_LEFT );

$iconList	= HtmlTag::create( 'i', '', ['class' => 'not-icon-arrow-left icon-list'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open icon-white'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
$iconCopy	= HtmlTag::create( 'i', '', ['class' => 'icon-plus-sign not-icon-white'] );

$checkInform	= '';
if( count( $missionUsers ) > 1 ){
	$checkInform	= HtmlTag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'inform',
		'value'		=> 1,
		'checked'	=> 'checked',
	) );
	$checkInform	= HtmlTag::create( 'label', $checkInform.'&nbsp;'.$w->labelInform, ['class' => 'checkbox'] );
}

$buttonCancel	= HtmlElements::LinkButton( './work/mission', $iconList.' '.$w->buttonList, 'btn btn-small' );
$buttonView		= HtmlElements::LinkButton( './work/mission/view/'.$mission->missionId, $iconView.' '.$w->buttonView, 'btn btn-small btn-info' );
$buttonSave		= HtmlElements::Button( 'edit', $iconSave.' '.$w->buttonSave, 'btn btn-primary' );
$buttonCopy		= HtmlElements::LinkButton( './work/mission/add/'.$mission->missionId, $iconCopy.' '.$w->buttonCopy, 'btn btn-small btn-mini' );

$fieldContent	= '';
if( strtoupper( $format ) === "HTML" ){
	$fieldContent	= '
<div class="row-fluid">
	<div class="span12">
		<label for="input_content">'.$w->labelContent.'</label>
		<div id="work-missions-loader" style=""><em class="muted">... lade Inhalte ...</em></div>
		<textarea id="input_content" name="content" rows="14" class="span12 TinyMCE" data-tinymce-mode="minimal" style="visibility: hidden">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<a href="./work/mission/convertContent/'.$mission->missionId.'/markdown" class="btn btn-inverse btn-small">Konvertieren zu Markdown</a>
	</div>
</div>';
}

$panelEdit	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		<form action="./work/mission/edit/'.$mission->missionId.'" method="post" class="form-changes-auto">
			<input type="hidden" name="format" value="'.htmlentities( $mission->format, ENT_QUOTES, 'UTF-8' ).'"/>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12 -max" value="'.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
				<div class="span3 -column-left-20">
					<label for="input_priority">'.$w->labelPriority.'</label>
					<select name="priority" id="input_priority" class="span12">'.$optPriority.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3 -column-left-20">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
				<div class="span6 -column-left-40">
					<label for="input_projectId">'.$w->labelProjectId.'</label>
					<select name="projectId" id="input_projectId" class="span12">'.$optProject.'</select>
				</div>
				<div class="span3 -column-left-20">
					<label for="input_workerId" class="mandatory required">'.$w->labelWorker.'</label>
					<select name="workerId" id="input_workerId" class="span12" required="required">'.$optWorker.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span2 -column-left-20">
					<label for="input_type">'.$w->labelType.'</label>
					<select name="type" id="input_type" class="span12 -max has-optionals">'.$optType.'</select>
				</div>
				<div class="span3 -column-left-20 optional type type-0" style="display: none">
					<label for="input_dayWork">'.$w->labelDayWork.'</label>
					<input type="date" name="dayWork" id="input_dayWork" value="'.$mission->dayStart.'" class="span12 -max" autocomplete="off"/>
				</div>
				<div class="span3 -column-left-20 optional type type-0" style="display: none">
					<label for="input_dayEnd">'.$w->labelDayDue.'</label>
					<input type="date" name="dayDue" id="input_dayDue" class="span12 -max cmClearInput" value="'.$mission->dayEnd.'" autocomplete="off"/>
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
<!--				<div class="span2 -column-left-10 optional type type-0" style="display: none">
					<label for="input_hoursProjected">'.$w->labelMinutesProjected.'</label>
					<input type="text" name="minutesProjected" id="input_minutesProjected" class="span12 -numeric" value="'.$hoursProjected.':'.$minutesProjected.'"/>
				</div>-->
				<div class="span2 -column-left-10 optional type type-0" style="display: none">
					<label for="input_timeProjected">'.$w->labelTimeProjected.'</label>
					<input type="text" name="timeProjected" id="input_timeProjected" class="span12 -numeric" value="'.$timeProjected.'"/>
				</div>
<!--				<div class="span2 -column-left-10 optional type type-0" style="display: none">
					<label for="input_minutesRequired">'.$w->labelMinutesRequired.'</label>
					<input type="text" name="minutesRequired" id="input_minutesRequired" class="span12 -numeric" value="'.$hoursRequired.':'.$minutesRequired.'"/>
				</div>-->
			</div>
<!--			<div class="row-fluid">
				<div class="span5 -column-left-40">
					<label for="input_location">'.$w->labelLocation.'</label>
					<input type="text" name="location" id="input_location" class="span12 -max cmClearInput" value="'.htmlentities( $mission->location, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7 -column-left-40">
					<label for="input_reference">'.$w->labelReference.'</label>
					<input type="text" name="reference" id="input_reference" class="span12 -max cmClearInput" value="'.htmlentities( $mission->reference, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>-->
			'.$fieldContent.'
			<div class="buttonbar">
				'.$checkInform.'
				'.$buttonCancel.'
				'.$buttonView.'
<!--				'.HtmlTag::create( 'div', array(
					), ['class' => 'btn-group'] ).'-->
				'.$buttonSave.'
	<!--			&nbsp;|&nbsp;
				'.HtmlElements::LinkButton( './work/mission/setStatus/-2', '<i class="icon-remove icon-white"></i> '.$w->buttonCancel, 'btn btn-small btn-danger' ).'
				'.HtmlElements::LinkButton( './work/mission/setStatus/-3', '<i class="icon-trash icon-white"></i> '.$w->buttonRemove, 'btn btn-small btn-inverse' ).'
	-->
				&nbsp;&nbsp;|&nbsp;&nbsp;
				'.$buttonCopy.'
			</div>
		</form>
	</div>
</div>';

//  --  STATES  --  //
$states	= $words['states'];
unset( $states[0] );
foreach( $states as $status => $label )
	$states[$status]	= HtmlTag::create( 'button', $label, array(
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
	$priorities[$priority]	= HtmlTag::create( 'button', $label, array(
		'type'		=> 'button',
		'onclick'	=> 'document.location.href=\'./work/mission/setPriority/'.$mission->missionId.'/'.$priority.'\';',
		'disabled'	=> $mission->priority == $priority ? 'disabled' : NULL,
		'class'		=> 'button',
	) );
$priorities	= join( $priorities );

$baseUrl	= $env->url;
if( $env->getModules()->has( 'Resource_Frontend' ) )
	$baseUrl	= Logic_Frontend::getInstance( $env )->getUri();

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
		'.$panelContent.'
		'.$panelDocuments.'
	</div>
	<div class="span4">
		'.$panelInfo.'
		'.$panelTime.'
		'.$panelClose.'
		'.$panelIssue.'
	</div>
</div>
<script>
$(document).ready(function(){
	WorkMissionsEditor.mission = '.json_encode( $mission ).';
	WorkMissionsEditor.init('.(int) $mission->missionId.');
	WorkMissionsEditor.urlEnv = "'.$env->url.'";
	WorkMissionsEditor.urlFrontend = "'.$baseUrl.'";
});
</script>
';
?>
