<?php

use CeusMedia\Common\Alg\Time\Duration;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $wordsGeneral */
/** @var array $words */
/** @var object $definition */
/** @var object $run */

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( $words['index']['timestampTemplate'] );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconArchive	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-archive'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$runReportChannelLabels	= $wordsGeneral['job-run-report-channels'];

//  --  PANEL FACTS: JOB  -- //
$helperAttribute->setObject( $run );
$facts	= [];
$facts['Title']				= $run->title ?: $definition->identifier;
$facts['Run ID']			= $run->jobRunId;
$facts['Process ID']		= $run->processId;
$facts['Type']				= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_TYPE )->render();
$facts['Status']			= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_STATUS )->render();
//$facts['Data']				= print_m( $run, NULL, NULL, TRUE );
$facts['Created']			= date( 'd.m.Y H:i:s', $run->createdAt );
if( $run->ranAt ){
	$duration	= '-';
	if( $run->finishedAt ){
		$duration	= $run->finishedAt - $run->ranAt;
		$duration	= $duration ? Duration::render( $duration, ' ', TRUE ) : '-';
	}
	$facts['Start']				= date( 'd.m.Y H:i:s', $run->ranAt );
	if( $run->finishedAt ){
		$facts['Finish']			= $run->finishedAt ? date( 'd.m.Y H:i:s', $run->finishedAt ) : '-';
		$facts['Duration']			= $duration.'&nbsp;';
	}
}
if( $run->arguments )
	$facts['Arguments']			= $run->arguments;
if( $run->reportMode ){
	$facts['Report Channel']	= $runReportChannelLabels[$run->reportChannel];
	$reportReceivers	= [];
	if( $run->reportReceivers ){
		foreach( preg_split( '/\s*,\s*/', $run->reportReceivers ) as $receiver )
		$reportReceivers	= HtmlTag::create( 'li', $receiver );
		$reportReceivers	= HtmlTag::create( 'ul', $reportReceivers );
		$facts['Report Receivers']	= $reportReceivers;
	}
}
if( in_array( $run->status, [Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS] ) ){
	if( !$run->archived ){
		$message			= json_decode( $run->message );
		$facts['Output']	= $message->type;
	}
}

$list	= [];
foreach( $facts as $factKey => $factValue ){
	$list[]	= HtmlTag::create( 'dt', $factKey );
	$list[]	= HtmlTag::create( 'dd', $factValue );
}

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', [
	'href'	=> './manage/job/run',
	'class'	=> 'btn btn-small',
] );

$buttonArchive	= HtmlTag::create( 'a', $iconArchive.'&nbsp;archivieren', [
	'href'	=> './manage/job/run/archive/'.$run->jobRunId,
	'class'	=> 'btn btn-inverse',
] );

$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', [
	'href'	=> './manage/job/run/remove/'.$run->jobRunId,
	'class'	=> 'btn btn-danger',
] );


$panelFactsJob	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', 'Job Run Facts' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] ),
		HtmlTag::create( 'div', join( ' ', [
			$buttonCancel,
			$buttonArchive,
			$buttonRemove,
		] ), ['class' => 'buttonbar'] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );

function formatNumber( $number ): string
{
	$units  = ['', 'K', 'M', 'G', 'P', 'E'];
	$unit   = 0;
	while( $number >= 1000 ){
		$unit++;
		$number = round( $number / 1000, 1);
	}
	return $number.$units[$unit];
}

//  --  PANEL FACTS: DEFINITION  -- //
$helperAttribute->setObject( $definition );
$facts	= [];
$facts['Identifier']	= HtmlTag::create( 'a', $definition->identifier, ['href' => './manage/job/definition/view/'.$definition->jobDefinitionId] );
$facts['Job-ID']		= HtmlTag::create( 'a', $definition->jobDefinitionId, ['href' => './manage/job/definition/view/'.$definition->jobDefinitionId] );
$facts['Mode']			= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_MODE )->render();
$facts['Status']		= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_STATUS )->render();
$facts['Class Method']	= $definition->className.' :: '.$definition->methodName;
$facts['Runs']			= HtmlTag::create( 'span', formatNumber( $definition->runs ), ['class' => 'badge'] );
$facts['Success']		= HtmlTag::create( 'span', formatNumber( $definition->runs - $definition->fails ), ['class' => 'badge badge-success'] ).( $definition->runs ? ' <small class="muted">('.round( ( $definition->runs - $definition->fails ) / $definition->runs * 100 ).'%)</small>' : '' );
$facts['Fails']			= HtmlTag::create( 'span', formatNumber( $definition->fails ), ['class' => 'badge badge-important'] ).( $definition->runs ? ' <small class="muted">('.round( $definition->fails / $definition->runs * 100 ).'%)</small>' : '' );
$facts['Created At']	= date( 'd.m.Y H:i:s', $definition->createdAt );
if( $definition->modifiedAt )
	$facts['Modified At']	= date( 'd.m.Y H:i:s', $definition->modifiedAt );
if( $definition->lastRunAt )
	$facts['Last Run At']	= date( 'd.m.Y H:i:s', $definition->lastRunAt );

$list	= [];
foreach( $facts as $factKey => $factValue ){
	$list[]	= HtmlTag::create( 'dt', $factKey );
	$list[]	= HtmlTag::create( 'dd', $factValue );
}
$panelFactsDefinition	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', 'Job Definition Facts' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] ),
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );


$tabs	= View_Manage_Job::renderTabs( $env, 'run' );

$panelMessage	= '';
if( in_array( $run->status, [Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS] ) ){
	if( !$run->archived ){
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
		$panelMessage	= HtmlTag::create( 'div', [
			HtmlTag::create( 'h4', 'Job Run' ),
			HtmlTag::create( 'div', [
				$output
			], ['class' => 'content-panel-inner'] )
		], ['class' => 'content-panel'] );
	}
}


return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		$panelFactsJob,
	], ['class' => 'span6'] ),
	HtmlTag::create( 'div', [
		$panelFactsDefinition
	], ['class' => 'span6'] ),
], ['class' => 'row-fluid'] ).
HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		$panelMessage
	], ['class' => 'span12'] ),
], ['class' => 'row-fluid'] ).'<style>
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
