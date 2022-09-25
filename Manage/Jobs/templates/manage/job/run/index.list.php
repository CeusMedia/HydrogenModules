<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( $words['index']['timestampTemplate'] );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );
//$helperTime->setMode( View_Helper_TimePhraser::MODE_HINT );

$iconArchive	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-archive' ) );
$iconAbort		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconTerminate	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-trash' ) );

$table		= HtmlTag::create( 'div', 'Keine Ausführungen gefunden.', array( 'class' => 'alert alert-warning' ) );
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
			$title	= HtmlTag::create( 'abbr', $item->title, array( 'title' => $title ) );
		$duration	= '-';
		if( $item->finishedAt ){
			$duration	= $item->finishedAt - $item->ranAt;
			$duration	= Alg_Time_Duration::render( $duration, ' ', TRUE );
		}

		$buttonArchive	= '';
		if( in_array( (int) $item->status, Model_Job_Run::STATUSES_ARCHIVABLE ) && !$item->archived ){
			$buttonArchive	= HtmlTag::create( 'a', $iconArchive, array(
				'href'	=> './manage/job/run/archive/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-inverse',
				'title'	=> 'archivieren',
			) );
		}
		$buttonAbort	= '';
		if( (int) $item->status === Model_Job_Run::STATUS_PREPARED ){
			$buttonAbort	= HtmlTag::create( 'a', $iconAbort, array(
				'href'	=> './manage/job/run/abort/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'verhindern',
			) );
		}
		$buttonTerminate	= '';
		if( (int) $item->status === Model_Job_Run::STATUS_RUNNING ){
			$buttonTerminate	= HtmlTag::create( 'a', $iconTerminate, array(
				'href'	=> './manage/job/run/terminate/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'abbrechen',
			) );
		}
		$buttonRemove	= '';
		if( in_array( (int) $item->status, Model_Job_Run::STATUSES_ARCHIVABLE ) ){
			$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
				'href'	=> './manage/job/run/remove/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'entfernen',
			) );
		}


		$link		= HtmlTag::create( 'a', $title, array(
			'href'	=> './manage/job/run/view/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' )
		) );
		$buttons	= HtmlTag::create( 'div', array( $buttonAbort, $buttonTerminate, $buttonArchive, $buttonRemove ), array( 'class' => 'btn-group' ) );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', '<small class="muted">'.$item->jobRunId.'</small>' ),
//			HtmlTag::create( 'td', '<a href="./manage/job/definition/view/'.$definition->jobDefinitionId.'">'.$title.'</a>' ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_TYPE )->render() ),
			HtmlTag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_RUN_STATUS )->render() ),
//			HtmlTag::create( 'td', $output ),
			HtmlTag::create( 'td', $item->ranAt ? $helperTime->setTimestamp( $item->ranAt )->render() : '-' ),
//			HtmlTag::create( 'td', $item->finishedAt ? $helperTime->setTimestamp( $item->finishedAt )->render() : '-' ),
			HtmlTag::create( 'td', $duration ),
			HtmlTag::create( 'td', $buttons ),
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

	$cols	= HtmlElements::ColumnGroup( array_values( $columns ) );

	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array_keys( $columns ) ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', array( $cols, $thead, $tbody ), array( 'class' => 'table table-striped table-condensed' ) );

	/*  --  PAGINATION  --  */
	$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './manage/job/run', $page, ceil( $total / $filterLimit ) );
	$table		.= HtmlTag::create( 'div', $pagination, array( 'class' => 'buttunbar' ) );
}
$panelList	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $words['index']['heading'] ),
	HtmlTag::create( 'div', array(
		$table,
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

return $panelList;
