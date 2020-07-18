<?php

$statusClasses		= array(
	Model_Job_Run::STATUS_TERMINATED	=> 'label label-important',
	Model_Job_Run::STATUS_FAILED		=> 'label label-important',
	Model_Job_Run::STATUS_ABORTED		=> 'label label-important',
	Model_Job_Run::STATUS_PREPARED		=> 'label',
	Model_Job_Run::STATUS_RUNNING		=> 'label label-warning',
	Model_Job_Run::STATUS_DONE			=> 'label label-info',
	Model_Job_Run::STATUS_SUCCESS		=> 'label label-success',
);
$statusIconClasses	= array(
	Model_Job_Run::STATUS_TERMINATED	=> 'fa fa-fw fa-times',
	Model_Job_Run::STATUS_FAILED		=> 'fa fa-fw fa-exclamation-triangle',
	Model_Job_Run::STATUS_ABORTED		=> 'fa fa-fw fa-ban',
	Model_Job_Run::STATUS_PREPARED		=> 'fa fa-fw fa-asterisk',
	Model_Job_Run::STATUS_RUNNING		=> 'fa fa-fw fa-cog fa-spin',
	Model_Job_Run::STATUS_DONE			=> 'fa fa-fw fa-check',
	Model_Job_Run::STATUS_SUCCESS		=> 'fa fa-fw fa-',
);
$typeClasses		= array(
	Model_Job_Run::TYPE_MANUALLY		=> 'label label-info',
	Model_Job_Run::TYPE_SCHEDULED		=> 'label label-success',
);
$typeIconClasses	= array(
	Model_Job_Run::TYPE_MANUALLY		=> 'fa fa-fw fa-hand-paper-o',
	Model_Job_Run::TYPE_SCHEDULED		=> 'fa fa-fw fa-clock-o',
);
//print_m( $wordsGeneral );die;

$statusLabels	= $wordsGeneral['job-run-statuses'];
$typeLabels		= $wordsGeneral['job-run-types'];

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( $words['index']['timestampTemplate'] );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );
//$helperTime->setMode( View_Helper_TimePhraser::MODE_HINT );

$iconArchive	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-archive' ) );

$table		= UI_HTML_Tag::create( 'div', 'Keine Ausführungen gefunden.', array( 'class' => 'alert alert-warning' ) );
if( $runs ){
	$rows	= array();
	foreach( $runs as $item ){
		$definition	= $definitions[$item->jobDefinitionId];
		$output		= '';
		if( in_array( $item->status, array( Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS ) ) ){
			$message	= json_decode( $item->message );
			$output		= $message->type;
/*			switch( $message->type ){
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
			}*/
		}
		$statusIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => $statusIconClasses[$item->status] ) );
		$statusLabel	= $statusIcon.'&nbsp;'.$statusLabels[$item->status];
		$status			= UI_HTML_Tag::create( 'span', $statusLabel, array( 'class' => $statusClasses[$item->status] ) );

		$typeIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => $typeIconClasses[$item->type] ) );
		$typeLabel		= $typeIcon.'&nbsp;'.$typeLabels[$item->type];
		$type			= UI_HTML_Tag::create( 'span', $typeLabel, array( 'class' => $typeClasses[$item->type] ) );

		$title	= $definition->identifier;
		if( $item->title )
			$title	= UI_HTML_Tag::create( 'abbr', $item->title, array( 'title' => $title ) );
		$duration	= '-';
		if( $item->finishedAt ){
			$duration	= $item->finishedAt - $item->ranAt;
			$duration	= Alg_Time_Duration::render( $duration, ' ', TRUE );
		}
		$buttonArchive	= UI_HTML_Tag::create( 'a', $iconArchive, array(
			'href'	=> './manage/job/run/archive/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
			'class'	=> 'btn btn-mini btn-inverse',
			'title'	=> 'archivieren',
		) );
		$link		= UI_HTML_Tag::create( 'a', $title, array(
			'href'	=> './manage/job/run/view/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' )
		) );
		$buttons	= UI_HTML_Tag::create( 'div', array( $buttonArchive ), array( 'class' => 'btn-group' ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="muted">'.$item->jobRunId.'</small>' ),
//			UI_HTML_Tag::create( 'td', '<a href="./manage/job/definition/view/'.$definition->jobDefinitionId.'">'.$title.'</a>' ),
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $type ),
			UI_HTML_Tag::create( 'td', $status ),
//			UI_HTML_Tag::create( 'td', $output ),
			UI_HTML_Tag::create( 'td', $item->ranAt ? $helperTime->setTimestamp( $item->ranAt )->render() : '-' ),
//			UI_HTML_Tag::create( 'td', $item->finishedAt ? $helperTime->setTimestamp( $item->finishedAt )->render() : '-' ),
			UI_HTML_Tag::create( 'td', $duration ),
			UI_HTML_Tag::create( 'td', $buttons ),
		) );
	}

	$columns	= array(
		$words['index']['tableHeadId']				=> '60px',
		$words['index']['tableHeadJobId']			=> '*',
		$words['index']['tableHeadType']			=> '110px',
		$words['index']['tableHeadStatus']			=> '110px',
//		$words['index']['tableHeadResult']			=> '100px',
		$words['index']['tableHeadRanAt']			=> '120px',
//		$words['index']['tableHeadFinishedId']		=> '120px',
		$words['index']['tableHeadDuration']		=> '60px',
		$words['index']['tableHeadActions']			=> '60px',
	);

	$cols	= UI_HTML_Elements::ColumnGroup( array_values( $columns ) );

	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array_keys( $columns ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', array( $cols, $thead, $tbody ), array( 'class' => 'table table-striped table-condensed' ) );

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
