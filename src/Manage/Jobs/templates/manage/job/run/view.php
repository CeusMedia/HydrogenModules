<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\Time\Duration;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $wordsGeneral */
/** @var array $words */
/** @var Entity_Job_Definition $definition */
/** @var Entity_Job_Run $run */

$w	= (object) $words['view'];

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
$facts[$w->labelTitle]				= $run->title ?: $definition->identifier;
$facts[$w->labelRunId]			= $run->jobRunId;
$facts[$w->labelProcessId]		= $run->processId;
$facts[$w->labelType]				= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_TYPE )->render();
$facts[$w->labelStatus]			= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_STATUS )->render();
//$facts['Data']				= print_m( $run, NULL, NULL, TRUE );
$facts[$w->labelCreated]			= date( 'd.m.Y H:i:s', $run->createdAt );
if( $run->ranAt ){
	$duration	= '-';
	$facts[$w->labelStart]				= date( 'd.m.Y H:i:s', $run->ranAt );
	if( 0 !== $run->finishedAt ){
		$duration	= $run->finishedAt - $run->ranAt;
		$duration	= $duration ? Duration::render( $duration, ' ', TRUE ) : '-';
		$facts[$w->labelFinish]		= date( 'd.m.Y H:i:s', $run->finishedAt );
		$facts[$w->labelDuration]		= $duration.'&nbsp;';
	}
}
if( $run->arguments )
	$facts[$w->labelArguments]			= $run->arguments;
if( $run->reportMode ){
	$facts[$w->labelReportChannel]	= $runReportChannelLabels[$run->reportChannel];
	$reportReceivers	= [];
	if( $run->reportReceivers ){
		foreach( preg_split( '/\s*,\s*/', $run->reportReceivers ) as $receiver )
		$reportReceivers	= HtmlTag::create( 'li', $receiver );
		$reportReceivers	= HtmlTag::create( 'ul', $reportReceivers );
		$facts[$w->labelReportReceivers]	= $reportReceivers;
	}
}
if( in_array( $run->status, [Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS] ) ){
	if( !$run->archived ){
		$message			= json_decode( $run->message );
		$facts[$w->labelOutput]	= $message->type;
	}
}

$list	= [];
foreach( $facts as $factKey => $factValue ){
	$list[]	= HtmlTag::create( 'dt', $factKey );
	$list[]	= HtmlTag::create( 'dd', $factValue );
}

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'	=> './manage/job/run',
	'class'	=> 'btn btn-small',
] );

$buttonArchive	= HtmlTag::create( 'a', $iconArchive.'&nbsp;'.$w->buttonArchive, [
	'href'	=> './manage/job/run/archive/'.$run->jobRunId,
	'class'	=> 'btn btn-inverse',
] );

$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
	'href'	=> './manage/job/run/remove/'.$run->jobRunId,
	'class'	=> 'btn btn-danger',
] );


$panelFactsJob	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', $w->headingFactsRun ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] ),
		HtmlTag::create( 'div', join( ' ', [
			$buttonCancel,
			$buttonArchive,
			$buttonRemove,
		] ), ['class' => 'buttonbar'] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );

function formatNumber( int|float $number ): string
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
$facts[$w->labelIdentifier]	= HtmlTag::create( 'a', $definition->identifier, ['href' => './manage/job/definition/view/'.$definition->jobDefinitionId] );
$facts[$w->labelJobId]		= HtmlTag::create( 'a', $definition->jobDefinitionId, ['href' => './manage/job/definition/view/'.$definition->jobDefinitionId] );
$facts[$w->labelMode]			= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_MODE )->render();
$facts[$w->labelStatus]		= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_STATUS )->render();
$facts[$w->labelClassMethod]	= $definition->className.' :: '.$definition->methodName;
$facts[$w->labelRuns]			= HtmlTag::create( 'span', formatNumber( $definition->runs ), ['class' => 'badge'] );
$facts[$w->labelSuccess]		= HtmlTag::create( 'span', formatNumber( $definition->runs - $definition->fails ), ['class' => 'badge badge-success'] ).( $definition->runs ? ' <small class="muted">('.round( ( $definition->runs - $definition->fails ) / $definition->runs * 100 ).'%)</small>' : '' );
$facts[$w->labelFails]			= HtmlTag::create( 'span', formatNumber( $definition->fails ), ['class' => 'badge badge-important'] ).( $definition->runs ? ' <small class="muted">('.round( $definition->fails / $definition->runs * 100 ).'%)</small>' : '' );
$facts[$w->labelCreatedAt]	= date( 'd.m.Y H:i:s', $definition->createdAt );
if( $definition->modifiedAt )
	$facts[$w->labelModifiedAt]	= date( 'd.m.Y H:i:s', $definition->modifiedAt );
if( $definition->lastRunAt )
	$facts[$w->labelLastRunAt]	= date( 'd.m.Y H:i:s', $definition->lastRunAt );

$list	= [];
foreach( $facts as $factKey => $factValue ){
	$list[]	= HtmlTag::create( 'dt', $factKey );
	$list[]	= HtmlTag::create( 'dd', $factValue );
}
$panelFactsDefinition	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', $w->headingFactsDefinition ),
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
					<div>'.$w->labelError.': '.$message->message.'</div>
					<div>'.$w->labelFile.': '.$file.' - '.$w->labelLine.': '.$message->line.'</div>
					<xmp>'.View_Manage_Job::removeEnvPath( $env, $message->trace ).'</xmp>
				</div>';
				break;
			case 'result':
			case 'data':
				$output	= '<div>
					<div>'.$w->labelType.': '.ucfirst( $message->type ).'</div>
					<pre>'.print_m( $message->{$message->type}, NULL, NULL, TRUE ).'</pre>
				</div>';
				break;
		}

		if( 'output' === $message->type || '' !== trim( $message->output ?? '' ) ){
			$output	.= '<div>
					<div>'.$w->labelOutput.'</div>
					<pre>'.$message->output.'</pre>
				</div>';
		}

		$panelMessage	= HtmlTag::create( 'div', [
			HtmlTag::create( 'h4', $w->headingMessage ),
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

function removeEnvPath( WebEnvironment $env, string $string ): string
{
	return preg_replace( '@'.preg_quote( $env->uri, '@' ).'@', '', $string );
}
