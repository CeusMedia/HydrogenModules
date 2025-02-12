<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\Alg\Time\Duration;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $wordsGeneral */
/** @var array $words */
/** @var object[] $definitions */
/** @var object[] $runs */
/** @var int $page */
/** @var int $limit */
/** @var int $total */
/** @var ?string $filterLimit */

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( $words['index']['timestampTemplate'] );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );
//$helperTime->setMode( View_Helper_TimePhraser::MODE_HINT );

$iconArchive	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-archive'] );
$iconAbort		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconTerminate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash'] );

$table		= HtmlTag::create( 'div', 'Keine Ausführungen gefunden.', ['class' => 'alert alert-warning'] );
if( $runs ){
	$rows	= [];
	foreach( $runs as $item ){
		$helperAttribute->setObject( $item );
		$definition	= $definitions[$item->jobDefinitionId];
		$output		= '';
		if( in_array( $item->status, [Model_Job_Run::STATUS_FAILED, Model_Job_Run::STATUS_DONE, Model_Job_Run::STATUS_SUCCESS] ) ){
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
			$title	= HtmlTag::create( 'abbr', $item->title, ['title' => $title] );
		$duration	= '-';
		if( $item->finishedAt ){
			$duration	= $item->finishedAt - $item->ranAt;
			$duration	= Duration::render( $duration, ' ', TRUE );
		}

		$buttonArchive	= '';
		if( in_array( (int) $item->status, Model_Job_Run::STATUSES_ARCHIVABLE ) && !$item->archived ){
			$buttonArchive	= HtmlTag::create( 'a', $iconArchive, [
				'href'	=> './manage/job/run/archive/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-inverse',
				'title'	=> 'archivieren',
			] );
		}
		$buttonAbort	= '';
		if( (int) $item->status === Model_Job_Run::STATUS_PREPARED ){
			$buttonAbort	= HtmlTag::create( 'a', $iconAbort, [
				'href'	=> './manage/job/run/abort/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'verhindern',
			] );
		}
		$buttonTerminate	= '';
		if( (int) $item->status === Model_Job_Run::STATUS_RUNNING ){
			$buttonTerminate	= HtmlTag::create( 'a', $iconTerminate, [
				'href'	=> './manage/job/run/terminate/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'abbrechen',
			] );
		}
		$buttonRemove	= '';
		if( in_array( (int) $item->status, Model_Job_Run::STATUSES_ARCHIVABLE ) ){
			$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
				'href'	=> './manage/job/run/remove/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' ),
				'class'	=> 'btn btn-mini btn-danger',
				'title'	=> 'entfernen',
			] );
		}


		$link		= HtmlTag::create( 'a', $title, [
			'href'	=> './manage/job/run/view/'.$item->jobRunId.( $page ? '?from=manage/job/run/'.$page : '' )
		] );
		$buttons	= HtmlTag::create( 'div', [$buttonAbort, $buttonTerminate, $buttonArchive, $buttonRemove], ['class' => 'btn-group'] );
		$rows[]	= HtmlTag::create( 'tr', [
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
		] );
	}

	$columns	= [
		$words['index']['tableHeadId']				=> '60px',
		$words['index']['tableHeadJobId']			=> '*',
		$words['index']['tableHeadType']			=> '120px',
		$words['index']['tableHeadStatus']			=> '120px',
//		$words['index']['tableHeadResult']			=> '100px',
		$words['index']['tableHeadRanAt']			=> '120px',
//		$words['index']['tableHeadFinishedId']		=> '120px',
		$words['index']['tableHeadDuration']		=> '60px',
		$words['index']['tableHeadActions']			=> '60px',
	];

	$cols	= HtmlElements::ColumnGroup( array_values( $columns ) );

	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array_keys( $columns ) ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', [$cols, $thead, $tbody], ['class' => 'table table-striped table-condensed'] );

	/*  --  PAGINATION  --  */
	$pagination	= new PageControl( './manage/job/run', $page, ceil( $total / $filterLimit ) );
	$table		.= HtmlTag::create( 'div', $pagination, ['class' => 'buttunbar'] );
}
$panelList	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $words['index']['heading'] ),
	HtmlTag::create( 'div', [
		$table,
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );

return $panelList;
