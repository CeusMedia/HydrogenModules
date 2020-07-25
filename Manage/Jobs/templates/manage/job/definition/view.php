<?php

$runList	= UI_HTML_Tag::create( 'div', 'Keine Ausführungen gefunden.', array( 'class' => 'alert alert-info' ) );

if( $runs ){
	$rows	= array();
	foreach( $runs as $item ){
		$output		= '';
		if( $item->status != Model_Job_Run::STATUS_PREPARED && $item->message ){
			$message	= json_decode( $item->message );
			$output		= $message->type;
		}

		switch( (int) $item->status ){
			case Model_Job_Run::STATUS_TERMINATED:
				$status	= UI_HTML_Tag::create( 'span', 'verhindert', array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_FAILED:
				$status	= UI_HTML_Tag::create( 'span', 'gescheitert', array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_ABORTED:
				$status	= UI_HTML_Tag::create( 'span', 'abgebrochen', array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_PREPARED:
				$status	= UI_HTML_Tag::create( 'span', 'vorbereitet', array( 'class' => 'badge' ) );
				break;
			case Model_Job_Run::STATUS_RUNNING:
				$status	= UI_HTML_Tag::create( 'span', 'läuft', array( 'class' => 'badge badge-warning' ) );
				break;
			case Model_Job_Run::STATUS_DONE:
				$status	= UI_HTML_Tag::create( 'span', 'erledigt', array( 'class' => 'badge badge-info' ) );
				break;
			case Model_Job_Run::STATUS_WORKLOAD:
				$status	= UI_HTML_Tag::create( 'span', 'erledigt', array( 'class' => 'badge badge-success' ) );
				break;
		}
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="muted">'.$item->processId.'</small>' ),
			UI_HTML_Tag::create( 'td', '<a href="./manage/job/definition/view/'.$definition->jobDefinitionId.'">'.$definition->identifier.'</a>' ),
			UI_HTML_Tag::create( 'td', $status ),
			UI_HTML_Tag::create( 'td', $output ),
			UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i:s', $item->createdAt ) ),
			UI_HTML_Tag::create( 'td', $item->ranAt ? date( 'd.m.Y H:i:s', $item->ranAt ) : '-' ),
			UI_HTML_Tag::create( 'td', $item->finishedAt ? date( 'd.m.Y H:i:s', $item->finishedAt ) : '-' ),
		) );
	}
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Run-ID', 'Job-ID', 'Zustand', 'vorbereitet', 'gestartet', 'beendet' ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$runList	= UI_HTML_Tag::create( 'table', array( $tbody ), array( 'class' => 'table table-striped table-condensed' ) );
}

$tabs	= View_Manage_Job::renderTabs( $env, 'definition' );

$list	= array();
$facts	= array();
$facts['Identifier']	= $definition->identifier;
$facts['Job-ID']		= $definition->jobDefinitionId;
$facts['Mode']			= $wordsGeneral['job-modes'][$definition->mode];
$facts['Status']		= $wordsGeneral['job-statuses'][$definition->status];
$facts['Class Name']	= $definition->className;
$facts['Method']		= $definition->methodName;
$facts['Runs']			= $definition->runs;
$facts['Success']		= $definition->runs - $definition->fails.( $definition->runs ? ' <small class="muted">('.round( ( $definition->runs - $definition->fails ) / $definition->runs * 100 ).'%)</small>' : '' );
$facts['Fails']			= $definition->fails.( $definition->runs ? ' <small class="muted">('.round( $definition->fails / $definition->runs * 100 ).'%)</small>' : '' );
$facts['Method']		= $definition->methodName;
$facts['Method']		= $definition->methodName;
$facts['Created At']	= date( 'Y-m-d H:i:s', $definition->createdAt );
if( $definition->modifiedAt )
	$facts['Modified At']	= date( 'Y-m-d H:i:s', $definition->modifiedAt );
if( $definition->lastRunAt )
	$facts['Last Run At']	= date( 'Y-m-d H:i:s', $definition->lastRunAt );

foreach( $facts as $factKey => $factValue ){
	$list[]	= UI_HTML_Tag::create( 'dt', $factKey );
	$list[]	= UI_HTML_Tag::create( 'dd', $factValue );
}
$list	= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h4', 'Facts' ),
		$list,
//		UI_HTML_Tag::create( 'div', print_m( $definition, NULL, NULL, TRUE ) ),
		UI_HTML_Tag::create( 'h4', 'Run List' ),
		$runList,
		UI_HTML_Tag::create( 'h4', 'Code' ),
		UI_HTML_Tag::create( 'xmp', join( PHP_EOL, $definitionCode ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

function removeEnvPath( $env, $string ): string
{
	return preg_replace( '@'.preg_quote( $env->uri, '@' ).'@', '', $string );
}
