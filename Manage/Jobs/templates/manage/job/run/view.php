<?php

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( $words['index']['timestampTemplate'] );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconArchive	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-archive' ) );

$runReportChannelLabels	= $wordsGeneral['job-run-report-channels'];

//  --  PANEL FACTS: JOB  -- //
$helperAttribute->setObject( $run );
$facts	= array();
$facts['Title']				= $run->title ? $run->title : $definition->identifier;
$facts['Run-ID']			= $run->jobRunId;
$facts['Type']				= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_TYPE )->render();
$facts['Status']			= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_STATUS )->render();
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
	UI_HTML_Tag::create( 'h4', 'Job Run Facts' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) ),
		UI_HTML_Tag::create( 'div', join( ' ', array(
			$buttonCancel,
			$buttonArchive,
		) ), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );


//  --  PANEL FACTS: DEFINITION  -- //
$helperAttribute->setObject( $definition );
$facts	= array();
$facts['Identifier']	= UI_HTML_Tag::create( 'a', $definition->identifier, array( 'href' => './manage/job/definition/view/'.$definition->jobDefinitionId ) );
$facts['Job-ID']		= UI_HTML_Tag::create( 'a', $definition->jobDefinitionId, array( 'href' => './manage/job/definition/view/'.$definition->jobDefinitionId ) );
$facts['Mode']			= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_MODE )->render();
$facts['Status']		= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_STATUS )->render();
$facts['Class Method']	= $definition->className.' :: '.$definition->methodName;
$facts['Runs']			= UI_HTML_Tag::create( 'span', $definition->runs, array( 'class' => 'badge' ) );
$facts['Success']		= UI_HTML_Tag::create( 'span', $definition->runs - $definition->fails, array( 'class' => 'badge badge-success' ) ).( $definition->runs ? ' <small class="muted">('.round( ( $definition->runs - $definition->fails ) / $definition->runs * 100 ).'%)</small>' : '' );
$facts['Fails']			= UI_HTML_Tag::create( 'span', $definition->fails, array( 'class' => 'badge badge-important' ) ).( $definition->runs ? ' <small class="muted">('.round( $definition->fails / $definition->runs * 100 ).'%)</small>' : '' );
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
	UI_HTML_Tag::create( 'h4', 'Job Definition Facts' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );


$tabs	= View_Manage_Job::renderTabs( $env, 'run' );

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
