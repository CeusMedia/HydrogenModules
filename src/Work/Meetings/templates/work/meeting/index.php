<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var array $words */
/** @var ?Logic_Limiter $limiter */
/** @var Entity_Work_Meeting[] $meetings */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] ).'&nbsp;';
$iconInstall	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus-circle'] ).'&nbsp;';

$statusIcons	= [
	Model_Work_Meeting::STATUS_CANCELLED	=> "remove",
	Model_Work_Meeting::STATUS_NEW			=> "star",
	Model_Work_Meeting::STATUS_ACTIVE		=> "cog",
	Model_Work_Meeting::STATUS_OUTDATED		=> "check",
	Model_Work_Meeting::STATUS_DONE			=> "check",
];
$statusColors	= [
	Model_Work_Meeting::STATUS_CANCELLED	=> "error",
	Model_Work_Meeting::STATUS_NEW			=> "warning",
	Model_Work_Meeting::STATUS_ACTIVE		=> "success",
	Model_Work_Meeting::STATUS_OUTDATED		=> "info",
	Model_Work_Meeting::STATUS_DONE			=> "info",
];

$w			= (object) $words['index'];

$labelEmpty	= HtmlTag::create( 'em', $w->empty, ['class' => 'muted'] );
$list		= HtmlTag::create( 'div', $labelEmpty, ['class' => 'alert alert-info'] );

if( $meetings ){
	$list	= [];
	foreach( $meetings as $meeting ){
		$attributes	= ['href' => './work/meeting/edit/'.$meeting->meetingId];
		$link	= HtmlTag::create( 'a', $meeting->title, $attributes );
		$iconStatus		= HtmlTag::create( 'i', "", ['class' => 'fa fa-fw fa-'.$statusIcons[$meeting->status]] );
		$cellLink		= HtmlTag::create( 'td', $link, ['class' => 'autocut'] );
		$cellStatus		= HtmlTag::create( 'td', $iconStatus.' '.$words['statuses'][$meeting->status] );
		$cellCreated	= HtmlTag::create( 'td', date( 'd.m.Y', $meeting->createdAt ) );
		$cellStarts		= HtmlTag::create( 'td', date( 'd.m.Y H:i:s', strtotime( $meeting->dateStart ) ) );
		$cellEnds		= HtmlTag::create( 'td', date( 'd.m.Y H:i:s', strtotime( $meeting->dateEnd ) ) );
		$cellModified	= HtmlTag::create( 'td', $meeting->modifiedAt ? date( 'd.m.Y', $meeting->modifiedAt ) : '-' );
		$rowColor		= $statusColors[$meeting->status];
		$cells			= [
			$cellLink,
			$cellStatus,
			$cellStarts,
			$cellEnds,
			$cellCreated,
			$cellModified
		];
		$attributes		= ['class' => $rowColor];
		$list[]			= HtmlTag::create( 'tr', $cells, $attributes );
	}
	$tableRows		= join( $list );
	$tableColumns	= HtmlElements::ColumnGroup( ['', '120px', '150px', '150px', '100px', '100px'] );
	$tableHeads		= HtmlElements::TableHeads( [
		$w->columnTitle,
		$w->columnStatus,
		$w->columnDateStart,
		$w->columnDateEnd,
		$w->columnCreatedAt,
		$w->columnModifiedAt
	] );
	$tableHead		= HtmlTag::create( 'thead', $tableHeads );
	$tableBody		= HtmlTag::create( 'tbody', $tableRows );
	$list			= HtmlTag::create( 'table', $tableColumns.$tableHead.$tableBody, ['class' => 'table table-condensed table-hover table-striped table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.$w->buttonAdd, [
	'href'	=> './work/meeting/add',
	'class'	=> 'btn not-btn-small btn-success'
] );
if( $limiter && $limiter->denies( 'Work.Meeting:maxItems', count( $meetings ) + 1 ) ){
	$buttonAdd	= HtmlTag::create( 'button', $iconAdd.$w->buttonAdd, [
		'type'		=> 'button',
		'class'		=> 'btn not-btn-small btn-success disabled',
		'onclick'	=> 'alert("Weitere Meetings sind in dieser Demo-Installation nicht möglich.")',
	] );
}

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				$list,
			], ['class' => 'span12'] ),
		], ['class' => 'row-fluid'] ),
		HtmlTag::create( 'div', [
			$buttonAdd,
		], ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );






