<?php

$viewers	= array();
foreach( $missionUsers as $user )
	$viewers[]	= UI_HTML_Tag::create( 'span', $user->username, array( 'class' => 'user role role'.$user->roleId ) );
$viewers	= join( '<br/>', $viewers );

$w	= (object) $words['view'];

$priorities	= $words['priorities'];
$optWorker	= array();
foreach( $users as $user )
	$optWorker[$user->userId]	= $user;

$worker			= $optWorker[$mission->workerId];
$priority		= UI_HTML_Tag::create( 'span', $words['priorities'][(string) $mission->priority], array( 'class' => 'mission priority'.$mission->priority ) );
$status			= UI_HTML_Tag::create( 'span', $words['states'][(string) $mission->status], array( 'class' => 'mission status'.$mission->status ) );
$worker			= UI_HTML_Tag::create( 'span', $worker->username, array( 'class' => 'user role role'.$worker->roleId ) );

$project		= '';
if( $useProjects )
	$project	= $userProjects[$mission->projectId];

$hoursProjected		= floor( $mission->minutesProjected / 60 );
$minutesProjected	= $mission->minutesProjected - $hoursProjected * 60;

$hoursRequired		= floor( $mission->minutesRequired / 60 );
$minutesRequired	= $mission->minutesRequired - $hoursRequired * 60;

$content			= View_Helper_Markdown::transformStatic( $env, $mission->content );

$panelFacts	= '
<div class="row-fluid">
	<div class="span12">
		<h3><span class="muted">'.$words['types'][$mission->type].': </span> '.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'</h3>
		<div class="content-panel">
			<h4>'.$w->legend.'</h4>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span4">
						<dl>
							<dt>'.$w->labelType.'</dt>
							<dd>'.$words['types'][$mission->type].'</dd>
							<dt>'.$w->labelPriority.'</dt>
							<dd>'.$priority.'</dd>
							<dt>'.$w->labelStatus.'</dt>
							<dd>'.$status.'</dd>
						</dl>
						<hr style="margin: 10px 0">
						<dl>
							<dt>'.$w->labelDayStart.' / '.$w->labelTimeStart.'</dt>
							<dd>'.date( "d.m.Y", strtotime( $mission->dayStart ) ).' '.$mission->timeStart.'</dd>
							<dt>'.$w->labelDayEnd.' / '.$w->labelTimeEnd.'</dt>
							<dd>'.( $mission->dayEnd ? date( "d.m.Y", strtotime( $mission->dayEnd ) ) : "" ).' '.$mission->timeEnd.'</dd>
							<dt>'.$w->labelChanged.'</dt>
							<dd><span class="date">'.date( 'd.m.Y H:i', $mission->modifiedAt ).'</span></dd>
							<dt>'.$w->labelHours.'</dt>
							<dd>geplant: '.$hoursProjected.':'.$minutesProjected.' / benötigt: '.$hoursRequired.':'.$minutesRequired.'</dd>
						</dl>
					</div>
					<div class="span4">
						<dl>
							<dt>'.$w->labelOwner.'</dt>
							<dd><span class="user role role'.$mission->owner->roleId.'">'.$mission->owner->username.'</span></dd>
							<dt>'.$w->labelWorker.'</dt>
							<dd>'.$worker.'</dd>
						</dl>
						<hr style="margin: 10px 0">
						<dl>
							<dt>'.$w->labelViewers.'</dt>
							<dd>'.$viewers.'</span></dd>
						</dl>
					</div>
					<div class="span4">
						<dl>
							<dt>'.$w->labelProjectId.'</dt>
							<dd>'.$project->title.'</dd>
							<dt>'.$w->labelLocation.'</dt>
							<dd>'.htmlentities( $mission->location ? $mission->location : '-', ENT_QUOTES, 'UTF-8' ).'</dd>
							<dt>'.$w->labelReference.'</dt>
							<dd>'.htmlentities( $mission->reference ? $mission->reference : '-', ENT_QUOTES, 'UTF-8' ).'</dd>
						</dl>
					</div>
				</div>
				<div class="buttonbar">
					'.UI_HTML_Elements::LinkButton( './work/mission', '<i class="icon-arrow-left"></i> '.$w->buttonCancel, 'btn' ).'
				</div>
			</div>
		</div>
		<hr/>
		<div class="content-panel">
			<h4>'.$w->legend.'</h4>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						<div id="not-descriptionAsMarkdown">'.$content.'</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';

return '
		'.$panelFacts.'
<script src="javascripts/Markdown.Converter.js"></script>
<script src="javascripts/bindWithDelay.js"></script>
<script>
var missionId = '.$mission->missionId.';
$("body").addClass("uses-bootstrap");
$(document).ready(function(){
	var markdown = $("#descriptionAsMarkdown");
	var converter = new Markdown.Converter();
	var textarea = $("#input_content");
	markdown.html(converter.makeHtml(markdown.html()));
});
</script>
<style>
input.changed,
select.changed,
textarea.changed {
	background-color: #FFFFDF;
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
';
?>