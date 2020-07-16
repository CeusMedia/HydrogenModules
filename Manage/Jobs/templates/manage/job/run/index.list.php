<?php

//print_m( $wordsGeneral );die;

$statusLabels	= $wordsGeneral['job-run-statuses'];
$typeLabels		= $wordsGeneral['job-run-types'];

$iconHand		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-hand-paper-o' ) );
$iconSchedule	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clock' ) );

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( $words['index']['timestampTemplate'] );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );
//$helperTime->setMode( View_Helper_TimePhraser::MODE_HINT );

$table		= UI_HTML_Tag::create( 'div', 'Keine Ausführungen gefunden.', array( 'class' => 'alert alert-warning' ) );
if( $runs ){
	$rows	= array();
	foreach( $runs as $item ){
		$definition	= $definitions[$item->jobDefinitionId];
		$output		= '';
		if( in_array( $item->status, array( Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS ) ) ){
			$message	= json_decode( $item->message );
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
		}

		switch( (int) $item->status ){
			case Model_Job_Run::STATUS_TERMINATED:
				$status	= UI_HTML_Tag::create( 'span', '<i class="fa fa-fw fa-times"></i>&nbsp;'.$statusLabels[$item->status], array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_FAILED:
				$status	= UI_HTML_Tag::create( 'span', '<i class="fa fa-fw fa-exclamation-triangle"></i>&nbsp;'.$statusLabels[$item->status], array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_ABORTED:
				$status	= UI_HTML_Tag::create( 'span', '<i class="fa fa-fw fa-ban"></i>&nbsp;'.$statusLabels[$item->status], array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_PREPARED:
				$status	= UI_HTML_Tag::create( 'span', '<i class="fa fa-fw fa-asterisk"></i>&nbsp;'.$statusLabels[$item->status], array( 'class' => 'badge' ) );
				break;
			case Model_Job_Run::STATUS_RUNNING:
				$status	= UI_HTML_Tag::create( 'span', '<i class="fa fa-fw fa-cog fa-spin"></i>&nbsp;'.$statusLabels[$item->status], array( 'class' => '' ) );
				break;
			case Model_Job_Run::STATUS_DONE:
				$status	= UI_HTML_Tag::create( 'span', '<i class="fa fa-fw fa-check"></i>&nbsp;'.$statusLabels[$item->status], array( 'class' => 'badge badge-info' ) );
				break;
			case Model_Job_Run::STATUS_SUCCESS:
				$status	= UI_HTML_Tag::create( 'span', '<i class="fa fa-fw fa-"></i>&nbsp;'.$statusLabels[$item->status], array( 'class' => 'badge badge-success' ) );
				break;
		}
		switch( (int) $item->type ){
			case Model_Job_Run::TYPE_MANUALLY:
				$type	= UI_HTML_Tag::create( 'span', $iconHand.'&nbsp;'.$typeLabels[$item->type], array( 'class' => 'not-badge' ) );
				break;
			case Model_Job_Run::TYPE_SCHEDULED:
				$type	= UI_HTML_Tag::create( 'span', $iconSchedule.'&nbsp;'.$typeLabels[$item->type], array( 'class' => 'not-badge' ) );
				break;
		}
		$title	= $definition->identifier;
		if( $item->title )
			$title	= UI_HTML_Tag::create( 'abbr', $item->title, array( 'title' => $title ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="muted">'.$item->processId.'</small>' ),
			UI_HTML_Tag::create( 'td', '<a href="./manage/job/definition/view/'.$definition->jobDefinitionId.'">'.$title.'</a>' ),
			UI_HTML_Tag::create( 'td', $status ),
			UI_HTML_Tag::create( 'td', $type ),
			UI_HTML_Tag::create( 'td', $output ),
			UI_HTML_Tag::create( 'td', $item->ranAt ? $helperTime->setTimestamp( $item->ranAt )->render() : '-' ),
			UI_HTML_Tag::create( 'td', $item->finishedAt ? $helperTime->setTimestamp( $item->finishedAt )->render() : '-' ),
		) );
	}
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		$words['index']['tableHeadId'],
		$words['index']['tableHeadJobId'],
		$words['index']['tableHeadStatus'],
		$words['index']['tableHeadType'],
		$words['index']['tableHeadStatus'],
		$words['index']['tableHeadRanAt'],
		$words['index']['tableHeadFinishedId'],
	) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-striped table-condensed' ) );

	/*  --  PAGINATION  --  */
	$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/job/run', $page, ceil( $total / $filterLimit ) );
	$table		.= UI_HTML_Tag::create( 'div', $pagination, array( 'class' => 'buttunbar' ) );
}
$panelList	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $words['index']['heading'] ),
	UI_HTML_Tag::create( 'div', array(
		$table,
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

return $panelList;
