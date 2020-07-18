<?php

$runStatusClasses		= array(
	Model_Job_Run::STATUS_TERMINATED	=> 'label label-important',
	Model_Job_Run::STATUS_FAILED		=> 'label label-important',
	Model_Job_Run::STATUS_ABORTED		=> 'label label-important',
	Model_Job_Run::STATUS_PREPARED		=> 'label',
	Model_Job_Run::STATUS_RUNNING		=> 'label label-warning',
	Model_Job_Run::STATUS_DONE			=> 'label label-info',
	Model_Job_Run::STATUS_SUCCESS		=> 'label label-success',
);
$runStatusIconClasses	= array(
	Model_Job_Run::STATUS_TERMINATED	=> 'fa fa-fw fa-times',
	Model_Job_Run::STATUS_FAILED		=> 'fa fa-fw fa-exclamation-triangle',
	Model_Job_Run::STATUS_ABORTED		=> 'fa fa-fw fa-ban',
	Model_Job_Run::STATUS_PREPARED		=> 'fa fa-fw fa-asterisk',
	Model_Job_Run::STATUS_RUNNING		=> 'fa fa-fw fa-cog fa-spin',
	Model_Job_Run::STATUS_DONE			=> 'fa fa-fw fa-check',
	Model_Job_Run::STATUS_SUCCESS		=> 'fa fa-fw fa-',
);
$runTypeClasses		= array(
	Model_Job_Run::TYPE_MANUALLY		=> 'label label-info',
	Model_Job_Run::TYPE_SCHEDULED		=> 'label label-success',
);
$runTypeIconClasses	= array(
	Model_Job_Run::TYPE_MANUALLY		=> 'fa fa-fw fa-hand-paper-o',
	Model_Job_Run::TYPE_SCHEDULED		=> 'fa fa-fw fa-clock-o',
);

$definitionStatusClasses		= array(
	Model_Job_Definition::STATUS_DISABLED		=> 'label label-inverse',
	Model_Job_Definition::STATUS_ENABLED		=> 'label label-success',
	Model_Job_Definition::STATUS_DEPRECATED		=> 'label label-warning',
);
$definitionStatusIconClasses	= array(
	Model_Job_Definition::STATUS_DISABLED		=> 'fa fa-fw fa-toggle-off',
	Model_Job_Definition::STATUS_ENABLED		=> 'fa fa-fw fa-toggle-on',
	Model_Job_Definition::STATUS_DEPRECATED		=> 'fa fa-fw fa-ban',
);
$definitionModeClasses		= array(
	Model_Job_Definition::MODE_UNDEFINED		=> 'not-label not-label-info',
	Model_Job_Definition::MODE_SINGLE			=> 'not-label not-label-info',
	Model_Job_Definition::MODE_MULTIPLE			=> 'not-label not-label-success',
	Model_Job_Definition::MODE_EXCLUSIVE		=> 'not-label not-label-success',
);
$definitionModeIconClasses	= array(
	Model_Job_Definition::MODE_UNDEFINED		=> 'fa fa-fw fa-exclamation-circle ',
	Model_Job_Definition::MODE_SINGLE			=> 'fa fa-fw fa-square',
	Model_Job_Definition::MODE_MULTIPLE			=> 'fa fa-fw fa-th-large',
	Model_Job_Definition::MODE_EXCLUSIVE		=> 'fa fa-fw fa-square-o',
);

$runStatusLabels		= $wordsGeneral['job-run-statuses'];
$runTypeLabels			= $wordsGeneral['job-run-types'];
$runReportChannelLabels	= $wordsGeneral['job-run-report-channels'];

$definitionStatusLabels	= $wordsGeneral['job-statuses'];
$definitionModeLabels	= $wordsGeneral['job-modes'];

$runStatusIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => $runStatusIconClasses[$run->status] ) );
$runStatusLabel		= $runStatusIcon.'&nbsp;'.$runStatusLabels[$run->status];
$runStatus			= UI_HTML_Tag::create( 'span', $runStatusLabel, array( 'class' => $runStatusClasses[$run->status] ) );

$runTypeIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => $runTypeIconClasses[$run->type] ) );
$runTypeLabel		= $runTypeIcon.'&nbsp;'.$runTypeLabels[$run->type];
$runType			= UI_HTML_Tag::create( 'span', $runTypeLabel, array( 'class' => $runTypeClasses[$run->type] ) );

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( $words['index']['timestampTemplate'] );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );


$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconArchive	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-archive' ) );


//  --  PANEL FACTS: JOB  -- //
$facts	= array();
$facts['Title']				= $run->title ? $run->title : $definition->identifier;
$facts['Run-ID']			= $run->jobRunId;
$facts['Type']				= $runType;
$facts['Status']			= $runStatus;
//$facts['Data']				= print_m( $run, NULL, NULL, TRUE );
$facts['Created']			= date( 'd.m.Y H:i:s', $run->createdAt );
if( $run->ranAt ){
	$duration	= '-';
	if( $run->finishedAt ){
		$duration	= $run->finishedAt - $run->ranAt;
		$duration	= Alg_Time_Duration::render( $duration, ' ', TRUE );
	}
	$facts['Start']				= date( 'd.m.Y H:i:s', $run->ranAt );
	if( $run->finishedAt ){
		$facts['Finish']			= $run->finishedAt ? date( 'd.m.Y H:i:s', $run->finishedAt ) : '-';
		$facts['Duration']			= $duration;
	}
}
if( $run->arguments )
	$facts['Arguments']			= $run->arguments;
if( $run->reportMode ){
	$facts['Report Channel']	= $runReportChannelLabels[$run->reportChannel];
	$reportReceivers	= array();
	if( $run->reportReceivers ){
		foreach( preg_split( '/\s*,\s*/', $run->reportReceivers ) as $receiver )
		$reportReceivers	= UI_HTML_Tag::create( 'li', $receiver );
		$reportReceivers	= UI_HTML_Tag::create( 'ul', $reportReceivers );
		$facts['Report Receivers']	= $reportReceivers;
	}
}
if( in_array( $run->status, array( Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS ) ) ){
	$message			= json_decode( $run->message );
	$facts['Output']	= $message->type;
}

$list	= array();
foreach( $facts as $factKey => $factValue ){
	$list[]	= UI_HTML_Tag::create( 'dt', $factKey );
	$list[]	= UI_HTML_Tag::create( 'dd', $factValue );
}

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zurück', array(
	'href'	=> './manage/job/run',
	'class'	=> 'btn btn-small',
) );

$buttonArchive	= UI_HTML_Tag::create( 'a', $iconArchive.'&nbsp;zurück', array(
	'href'	=> './manage/job/run/archive/'.$run->jobRunId,
	'class'	=> 'btn btn-inverse',
) );


$panelFactsJob	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h4', 'Job Definition' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) ),
		UI_HTML_Tag::create( 'div', join( ' ', array(
			$buttonCancel,
			$buttonArchive,
		) ), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );


//  --  PANEL FACTS: DEFINITION  -- //
$definitionStatusIcon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $definitionStatusIconClasses[$definition->status] ) );
$definitionStatusLabel	= $definitionStatusIcon.'&nbsp;'.$definitionStatusLabels[$definition->status];
$definitionStatus		= UI_HTML_Tag::create( 'span', $definitionStatusLabel, array( 'class' => $definitionStatusClasses[$definition->status] ) );

$definitionModeIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => $definitionModeIconClasses[$definition->mode] ) );
$definitionModeLabel	= $definitionModeIcon.'&nbsp;'.$definitionModeLabels[$definition->mode];
$definitionMode			= UI_HTML_Tag::create( 'span', $definitionModeLabel, array( 'class' => $definitionModeClasses[$definition->mode] ) );

$facts	= array();
$facts['Identifier']	= $definition->identifier;
$facts['Job-ID']		= $definition->jobDefinitionId;
$facts['Mode']			= $definitionMode;
$facts['Status']		= $definitionStatus;
$facts['Class Name']	= $definition->className;
$facts['Method']		= $definition->methodName;
$facts['Runs']			= $definition->runs;
$facts['Success']		= $definition->runs - $definition->fails.( $definition->runs ? ' <small class="muted">('.round( ( $definition->runs - $definition->fails ) / $definition->runs * 100 ).'%)</small>' : '' );
$facts['Fails']			= $definition->fails.( $definition->runs ? ' <small class="muted">('.round( $definition->fails / $definition->runs * 100 ).'%)</small>' : '' );
$facts['Method']		= $definition->methodName;
$facts['Method']		= $definition->methodName;
$facts['Created At']	= date( 'd.m.Y H:i:s', $definition->createdAt );
if( $definition->modifiedAt )
	$facts['Modified At']	= date( 'd.m.Y H:i:s', $definition->modifiedAt );
if( $definition->lastRunAt )
	$facts['Last Run At']	= date( 'd.m.Y H:i:s', $definition->lastRunAt );

$list	= array();
foreach( $facts as $factKey => $factValue ){
	$list[]	= UI_HTML_Tag::create( 'dt', $factKey );
	$list[]	= UI_HTML_Tag::create( 'dd', $factValue );
}
$panelFactsDefinition	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h4', 'Job Run' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );


$tabs	= View_Manage_Job::renderTabs( $env, 'definition' );

$panelMessage	= '';
if( in_array( $run->status, array( Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS ) ) ){
	$message	= json_decode( $run->message );
	$output		= '';
	switch( $message->type ){
		case 'throwable':
			$file	= View_Manage_Job::removeEnvPath( $env, $message->file );
			$output	= '<div>
				<div>Error: '.$message->message.'</div>
				<div>File: '.$file.' - Line: '.$message->line.'</div>
				<xmp>'.View_Manage_Job::removeEnvPath( $env, $message->trace ).'</xmp>
			</div>';
			break;
		case 'result':
		case 'data':
			$output	= '<div>
				<div>Type: '.ucfirst( $message->type ).'</div>
				<pre>'.print_m( $message->{$message->type}, NULL, NULL, TRUE ).'</pre>
			</div>';
			break;
	}
	$panelMessage	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h4', 'Job Run' ),
		UI_HTML_Tag::create( 'div', array(
			$output
		), array( 'class' => 'content-panel-inner' ) )
	), array( 'class' => 'content-panel' ) );
}


return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelFactsJob,
	), array( 'class' => 'span6' ) ),
	UI_HTML_Tag::create( 'div', array(
		$panelFactsDefinition
	), array( 'class' => 'span6' ) ),
), array( 'class' => 'row-fluid' ) ).
UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelMessage
	), array( 'class' => 'span12' ) ),
), array( 'class' => 'row-fluid' ) ).'<style>
.dl-horizontal dt {
	width: 120px;
	}
.dl-horizontal dd {
	margin-left: 140px;
	}
</style>';

function removeEnvPath( $env, $string ): string
{
	return preg_replace( '@'.preg_quote( $env->uri, '@' ).'@', '', $string );
}
