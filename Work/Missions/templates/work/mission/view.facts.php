<?php

$phraser    = new View_Helper_TimePhraser( $env );

$canEditProject	= $acl->hasRight( $userRoleId, 'manage_project', 'edit' );

function renderUserLabel( $user ){
	if( !$user )
		return "-";
	$iconUser	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-user' ) );
	$spanClass	= 'user role role'.$user->roleId;
	$fullname	= $user->firstname.' '.$user->surname;
	$username	= UI_HTML_Tag::create( 'abbr', $user->username, array( 'title' => $fullname ) );
	$label		= $iconUser.'&nbsp;'.$username;
	return UI_HTML_Tag::create( 'span', $label, array( 'class' => $spanClass ) );
}

$viewers	= array();
foreach( $missionUsers as $user )
	$viewers[]	= renderUserLabel( $user );
$viewers	= join( '<br/>', $viewers );

$w	= (object) $words['view'];

$priorities	= $words['priorities'];

$priority		= UI_HTML_Tag::create( 'span', $words['priorities'][(string) $mission->priority], array( 'class' => 'mission priority'.$mission->priority ) );
$status			= UI_HTML_Tag::create( 'span', $words['states'][(string) $mission->status], array( 'class' => 'mission status'.$mission->status ) );
$creator		= renderUserLabel( $mission->creator );
$worker			= renderUserLabel( $mission->worker );
$modifier		= renderUserLabel( $mission->modifier );

$project		= $mission->project->title;
if( $useProjects && $canEditProject )
	$project	= UI_HTML_Tag::create( 'a', $project, array( 'href' => './manage/project/edit/'.$mission->projectId ) );

$hoursProjected		= floor( $mission->minutesProjected / 60 );
$minutesProjected	= str_pad( $mission->minutesProjected - $hoursProjected * 60, 2, 0, STR_PAD_LEFT );

$hoursRequired		= floor( $mission->minutesRequired / 60 );
$minutesRequired	= str_pad( $mission->minutesRequired - $hoursRequired * 60, 2, 0, STR_PAD_LEFT );

$iconCancel			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'not-icon-arrow-left icon-list' ) );
$iconEdit			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil icon-white' ) );
$iconCopy			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus-sign not-icon-white' ) );

return '
<div class="row-fluid">
	<div class="span12">
		<h3><span class="muted">'.$words['types'][$mission->type].': </span> '.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'</h3>
		<div class="content-panel">
			<h4>'.$w->legend.'</h4>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div style="float: left; width: 50%">
						<dl class="not-dl-horizontal">
							<dt>'.$w->labelProjectId.'</dt>
							<dd>'.$project.'</dd>
							<dt>'.$w->labelType.'</dt>
							<dd>'.$words['types'][$mission->type].'</dd>
							<dt>'.$w->labelPriority.'</dt>
							<dd>'.$priority.'</dd>
							<dt>'.$w->labelStatus.'</dt>
							<dd>'.$status.'</dd>
							<dt>'.$w->labelDayStart.' / '.$w->labelTimeStart.'</dt>
							<dd>'.date( "d.m.Y", strtotime( $mission->dayStart ) ).' '.$mission->timeStart.'</dd>
							<dt>'.$w->labelDayEnd.' / '.$w->labelTimeEnd.'</dt>
							<dd>'.( $mission->dayEnd ? date( "d.m.Y", strtotime( $mission->dayEnd ) ) : "" ).' '.$mission->timeEnd.'</dd>
							<dt>'.$w->labelHours.'</dt>
							<dd>geplant: '.$hoursProjected.':'.$minutesProjected.'</dd>
							<dd>benötigt: '.$hoursRequired.':'.$minutesRequired.'</dd>
							<dt>'.$w->labelLocation.'</dt>
							<dd>'.htmlentities( $mission->location ? $mission->location : '-', ENT_QUOTES, 'UTF-8' ).'</dd>
							<dt>'.$w->labelReference.'</dt>
							<dd>'.htmlentities( $mission->reference ? $mission->reference : '-', ENT_QUOTES, 'UTF-8' ).'</dd>
						</dl>
					</div>
					<div style="float: left; width: 50%">
						<dl class="not-dl-horizontal">
							<dt>erstellt am</dt>
							<dd>'.date( "d.m.Y", $mission->createdAt ).'</dd>
							<dt>'.$w->labelCreator.'</dt>
							<dd>'.$creator.'</span></dd>
							<dt>'.$w->labelChanged.'</dt>
							<dd><span class="date">'.( $mission->modifiedAt ? date( 'd.m.Y H:i', $mission->modifiedAt ) : '-' ).'</span></dd>
							<dt>'.$w->labelModifier.'</dt>
							<dd>'.$modifier.'</dd>
							<dt>'.$w->labelWorker.'</dt>
							<dd>'.$worker.'</dd>
							<dt>'.$w->labelViewers.'</dt>
							<dd>'.$viewers.'</dd>
						</dl>
					</div>
				</div>
				<div class="buttonbar">
					'.UI_HTML_Elements::LinkButton( './work/mission', $iconCancel.' '.$w->buttonCancel, 'btn btn-small' ).'
					'.UI_HTML_Elements::LinkButton( './work/mission/edit/'.$mission->missionId, $iconEdit.' '.$w->buttonEdit, 'btn btn-primary' ).'
					&nbsp;&nbsp;|&nbsp;&nbsp;
					'.UI_HTML_Elements::LinkButton( './work/mission/add/'.$mission->missionId, $iconCopy.' '.$w->buttonCopy, 'btn not-btn-info not-btn-success btn-small btn-mini' ).'
				</div>
			</div>
		</div>
	</div>
</div>';
?>