<?php

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$runList	= UI_HTML_Tag::create( 'div', 'Keine Ausführungen gefunden.', array( 'class' => 'alert alert-info' ) );

if( $runs ){
	$rows	= array();
	foreach( $runs as $item ){
		$helperAttribute->setObject( $item );
		$output		= '<em class="muted">none</em>';
		if( $item->status != Model_Job_Run::STATUS_PREPARED && $item->message ){
			$message	= json_decode( $item->message );
			$output		= $message->type ?? '<em class="muted">unknonwn</em>';
		}

		$title	= $item->title ? $item->title : $definition->identifier;
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="muted">'.$item->jobRunId.'</small>' ),
			UI_HTML_Tag::create( 'td', '<a href="./manage/job/run/view/'.$item->jobRunId.'">'.$title.'</a>' ),
			UI_HTML_Tag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_STATUS )->render() ),
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

$helperAttribute->setObject( $definition );
$list	= array();
$facts	= array();
$facts['Identifier']	= $definition->identifier;
$facts['Job-ID']		= $definition->jobDefinitionId;
$facts['Mode']			= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_MODE )->render();
$facts['Status']		= $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_STATUS )->render();
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
	UI_HTML_Tag::create( 'h3', '<span class="muted">Job:</span> '.$definition->identifier ),
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
