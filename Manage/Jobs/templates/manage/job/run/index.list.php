<?php

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( $words['index']['timestampTemplate'] );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );
//$helperTime->setMode( View_Helper_TimePhraser::MODE_HINT );

$iconArchive	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-archive' ) );
$iconAbort		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconTerminate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-trash' ) );

$table		= UI_HTML_Tag::create( 'div', 'Keine Ausführungen gefunden.', array( 'class' => 'alert alert-warning' ) );
if( $runs ){
	$rows	= [];
	foreach( $runs as $item ){
		$helperAttribute->setObject( $item );
		$definition	= $definitions[$item->jobDefinitionId];
		$output		= '';
		if( in_array( $item->status, array( Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS ) ) ){
			$output		= '';
			if( $item->message ){
				$message	= json_decode( $item->message );
				$output		= $message->type;
			}
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

		$title	= $definition->identifier;
		if( $item->title )
			$title	= UI_HTML_Tag::create( 'abbr', $item->title, array( 'title' => $title ) );
		$duration	= '-';
		if( $item->finishedAt ){
			$duration	= $item->finishedAt - $item->ranAt;
			$duration	= Alg_Time_Duration::render( $duration, ' ', TRUE );
		}

		$buttonArchive	= '';
		if( in_array( (int) $item->status, Model_Job_Run::STATUSES_ARCHIVABLE ) && !$item->archived ){
			$buttonArchive	= UI_HTML_Tag::create( 'a', $iconArchive, array(
				'href'	=> './manage/job/run/archive/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-inverse',
				'title'	=> 'archivieren',
			) );
		}
		$buttonAbort	= '';
		if( (int) $item->status === Model_Job_Run::STATUS_PREPARED ){
			$buttonAbort	= UI_HTML_Tag::create( 'a', $iconAbort, array(
				'href'	=> './manage/job/run/abort/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'verhindern',
			) );
		}
		$buttonTerminate	= '';
		if( (int) $item->status === Model_Job_Run::STATUS_RUNNING ){
			$buttonTerminate	= UI_HTML_Tag::create( 'a', $iconTerminate, array(
				'href'	=> './manage/job/run/terminate/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'abbrechen',
			) );
		}
		$buttonRemove	= '';
		if( in_array( (int) $item->status, Model_Job_Run::STATUSES_ARCHIVABLE ) ){
			$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
				'href'	=> './manage/job/run/remove/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'entfernen',
			) );
		}


		$link		= UI_HTML_Tag::create( 'a', $title, array(
			'href'	=> './manage/job/run/view/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' )
		) );
		$buttons	= UI_HTML_Tag::create( 'div', array( $buttonAbort, $buttonTerminate, $buttonArchive, $buttonRemove ), array( 'class' => 'btn-group' ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="muted">'.$item->jobRunId.'</small>' ),
//			UI_HTML_Tag::create( 'td', '<a href="./manage/job/definition/view/'.$definition->jobDefinitionId.'">'.$title.'</a>' ),
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_TYPE )->render() ),
			UI_HTML_Tag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_STATUS )->render() ),
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
		$words['index']['tableHeadType']			=> '120px',
		$words['index']['tableHeadStatus']			=> '120px',
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
