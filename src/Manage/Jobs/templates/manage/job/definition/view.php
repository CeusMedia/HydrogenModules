<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Job_Definition $view */
/** @var array<object> $runs */
/** @var object $definition */
/** @var ?object $definitionCode */
/** @var array $words */

$w	= (object) $words['view'];

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$runList	= HtmlTag::create( 'div', $w->noRuns, ['class' => 'alert alert-info'] );

if( $runs ){
	$rows	= [];
	foreach( $runs as $item ){
		$helperAttribute->setObject( $item );
		$output		= '<em class="muted">'.$w->outputNone.'</em>';
		if( $item->status != Model_Job_Run::STATUS_PREPARED && $item->message ){
			$message	= json_decode( $item->message );
			$output		= $message->type ?? '<em class="muted">'.$w->outputUnknown.'</em>';
		}

		$title	= $item->title ?: $definition->identifier;
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', '<small class="muted">'.$item->jobRunId.'</small>' ),
			HtmlTag::create( 'td', '<a href="./manage/job/run/view/'.$item->jobRunId.'">'.$title.'</a>' ),
			HtmlTag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_STATUS )->render() ),
			HtmlTag::create( 'td', $output ),
			HtmlTag::create( 'td', date( 'd.m.Y H:i:s', $item->createdAt ) ),
			HtmlTag::create( 'td', $item->ranAt ? date( 'd.m.Y H:i:s', $item->ranAt ) : '-' ),
			HtmlTag::create( 'td', $item->finishedAt ? date( 'd.m.Y H:i:s', $item->finishedAt ) : '-' ),
		] );
	}
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->tableHeadRunId, $w->tableHeadJobId, $w->tableHeadStatus, $w->tableHeadCreatedAt, $w->tableHeadRanAt, $w->tableHeadFinishedAt] ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$runList	= HtmlTag::create( 'table', [$tbody], ['class' => 'table table-striped table-condensed'] );
}

$tabs	= View_Manage_Job::renderTabs( $env, 'definition' );

$helperAttribute->setObject( $definition );
$list	= [];
$facts	= [];
$facts[$w->labelIdentifier]	= $definition->identifier;
$facts[$w->labelJobId]		= $definition->jobDefinitionId;
$facts[$w->labelMode]			= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_MODE )->render();
$facts[$w->labelStatus]		= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_STATUS )->render();
$facts[$w->labelClassName]	= $definition->className;
$facts[$w->labelMethod]		= $definition->methodName;
$facts[$w->labelRuns]			= $definition->runs;
$facts[$w->labelSuccess]		= $definition->runs - $definition->fails.( $definition->runs ? ' <small class="muted">('.round( ( $definition->runs - $definition->fails ) / $definition->runs * 100 ).'%)</small>' : '' );
$facts[$w->labelFails]			= $definition->fails.( $definition->runs ? ' <small class="muted">('.round( $definition->fails / $definition->runs * 100 ).'%)</small>' : '' );
$facts[$w->labelMethod]		= $definition->methodName;
$facts[$w->labelMethod]		= $definition->methodName;
$facts[$w->labelCreatedAt]	= date( 'Y-m-d H:i:s', $definition->createdAt );
if( $definition->modifiedAt )
	$facts[$w->labelModifiedAt]	= date( 'Y-m-d H:i:s', $definition->modifiedAt );
if( $definition->lastRunAt )
	$facts[$w->labelLastRunAt]	= date( 'Y-m-d H:i:s', $definition->lastRunAt );

foreach( $facts as $factKey => $factValue ){
	$list[]	= HtmlTag::create( 'dt', $factKey );
	$list[]	= HtmlTag::create( 'dd', $factValue );
}
$list	= HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );

$sourceCode	= HtmlTag::create( 'div', $w->noClass, ['class' => 'alert alert-info'] );
if( NULL !== $definitionCode ){
	$sourceCode	= HtmlTag::create( 'xmp', join( PHP_EOL, $definitionCode ) );
}

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', '<span class="muted">'.$w->heading.':</span> '.$definition->identifier ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'h4', $w->headingFacts ),
		$list,
//		HtmlTag::create( 'div', print_m( $definition, NULL, NULL, TRUE ) ),
		HtmlTag::create( 'h4', $w->headingRuns ),
		$runList,
		HtmlTag::create( 'h4', $w->headingCode ),
		$sourceCode,
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );

function removeEnvPath( $env, $string ): string
{
	return preg_replace( '@'.preg_quote( $env->uri, '@' ).'@', '', $string );
}
