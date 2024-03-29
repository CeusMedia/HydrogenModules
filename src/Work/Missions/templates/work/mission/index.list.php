<?php

use CeusMedia\Common\UI\HTML\CollapsePanel as HtmlCollapsePanel;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */
/** @var object[] $missions */

$w	= (object) $words['index'];

$iconUp		= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/silk/arrow_up.png', $words['filter-directions']['ASC'] );
$iconDown	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/silk/arrow_down.png', $words['filter-directions']['DESC'] );
$iconRight	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/silk/arrow_right.png', $words['list-actions']['moveRight'] );
$iconLeft	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/silk/arrow_left.png', $words['list-actions']['moveLeft'] );
$iconEdit	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/silk/pencil.png', $words['list-actions']['edit'] );
$iconRemove	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/silk/bin_closed.png', $words['list-actions']['remove'] );

//  --  LIST  --  //
$list	= [
	0 => [],
	1 => [],
	2 => [],
	3 => [],
	4 => [],
	5 => [],
	6 => [],
	7 => [],
];
$indicator	= new HtmlIndicator();
$disabled	= [];
$today		= strtotime( date( 'Y-m-d', time() ) );
foreach( $missions as $mission ){
	$label		= htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' );
	$url		= './work/mission/edit/'.$mission->missionId;
	$class		= 'icon-label mission-type-'.$mission->type;
	$link		= HtmlElements::Link( $url, $label, ['class' => $class] );
	$days		= ( strtotime( $mission->dayStart ) - $today ) / ( 24 * 60 * 60 );
	$daysBound	= max( min( $days , 6 ), 0 );
	$graph		= $indicator->build( $mission->status, 4 );
	$type		= $words['types'][$mission->type];
	$priority	= $words['priorities'][$mission->priority];
	$class		= 'row-priority priority-'.$mission->priority;
	$buttonEdit		= HtmlElements::LinkButton( './work/mission/edit/'.$mission->missionId, $iconEdit, 'tiny' );
	$buttonRemove	= HtmlElements::LinkButton( './work/mission/setStatus/'.$mission->missionId.'/'.urlencode( '-3' ), $iconRemove, 'tiny' );
	$buttonLeft		= HtmlElements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/?date='.urlencode( '-1' ), $iconLeft, 'tiny' );
	$buttonRight	= HtmlElements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/?date='.urlencode( '+1' ), $iconRight, 'tiny' );

	if( !$daysBound )
		$buttonLeft	= HtmlElements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/'.urlencode( '-1' ), $iconLeft, 'tiny', NULL, TRUE );

	$daysOverdue	= '';
	$days	= ( strtotime( max( $mission->dayStart, $mission->dayEnd ) ) - $today ) / ( 24 * 60 * 60);
	if( $days < 0 )
		$daysOverdue	= HtmlTag::create( 'div', abs( $days ), ['class' => "overdue"] );

	$cells	= [
		'<td><div style="padding: 4px 2px 2px 2px;">'.$graph.$daysOverdue.'</div></td>',
		'<td>'.$link.'</td>',
		'<td><small>'.$priority.'</small></td>',
		'<td class="actions">'.$buttonEdit.' | '.$buttonLeft.$buttonRight.'</td>',
	];
	$list[$daysBound][]	= HtmlTag::create( 'tr', join( $cells ), ['class' => $class] );
}

function getFutureDate( int $daysInFuture = 0, ?array $words = NULL ): string
{
	$then	= new DateTime();
	$then->modify( $daysInFuture );
	$day	= $words ? $words['days'][$then->format( "w" )].', ' : '';
	return $day.$then->format( "j.n." );
}

function getCount( $list, $days ): ?string
{
	$count	= count( $list[$days] );
	if( $count )
#		return ' <small>('.$count.')</small>';
		return ' <div class="mission-number">'.$count.'</div>';
	return '';
}

$colgroup	= HtmlElements::ColumnGroup( "120px", "", "90px", "115px" );
$tableHeads	= HtmlElements::TableHeads( [
	HtmlTag::create( 'div', 'Zustand', ['class' => 'sortable', 'data-column' => 'status'] ),
	HtmlTag::create( 'div', 'Aufgabe', ['class' => 'sortable', 'data-column' => 'title'] ),
	HtmlTag::create( 'div', 'Priorität', ['class' => 'sortable', 'data-column' => 'priority'] ),
	HtmlTag::create( 'div', 'Aktion', ['class' => 'sortable', 'data-column' => NULL] )
] );

$folders	= [];

if( count( $list[0] ) ){
	$heading	= HtmlTag::create( 'div', '<b>Heute</b>, '.getFutureDate( 0, $words ).getCount( $list, 0 ) );
	$heading	= HtmlTag::create( 'div', getFutureDate( 0, $words ).getCount( $list, 0 ) );
	$table		= HtmlTag::create( 'table', $colgroup.$tableHeads.join( $list[0] ) );
	$folders[]	= CeusMedia\Common\UI\HTML\CollapsePanel::create( 'day-0', $table, $heading, NULL );
}
if( count( $list[1] ) ){
	$heading	= HtmlTag::create( 'div', '<b>Morgen</b>, '.getFutureDate( 1, $words ).getCount( $list, 1 ) );
	$heading	= HtmlTag::create( 'div', getFutureDate( 1, $words ).getCount( $list, 1 ) );
	$table		= HtmlTag::create( 'table', $colgroup.$tableHeads.join( $list[1] ) );
	$folders[]	= HtmlCollapsePanel::create( 'day-1', $table, $heading, NULL );
}
if( count( $list[2] ) ){
	$heading	= HtmlTag::create( 'div', '<b>Übermorgen</b>, '.getFutureDate( 2, $words ).getCount( $list, 2 ) );
	$heading	= HtmlTag::create( 'div', getFutureDate( 2, $words ).getCount( $list, 2 ) );
	$table		= HtmlTag::create( 'table', $colgroup.$tableHeads.join( $list[2] ) );
	$folders[]	= HtmlCollapsePanel::create( 'day-2', $table, $heading, NULL );
}
if( count( $list[3] ) ){
	$heading	= HtmlTag::create( 'div', getFutureDate( 3, $words ).getCount( $list, 3 ) );
	$table		= HtmlTag::create( 'table', $colgroup.$tableHeads.join( $list[3] ) );
	$folders[]	= HtmlCollapsePanel::create( 'day-3', $table, $heading, NULL );
}
if( count( $list[4] ) ){
	$heading	= HtmlTag::create( 'div', getFutureDate( 4, $words ).getCount( $list, 4 ) );
	$table		= HtmlTag::create( 'table', $colgroup.$tableHeads.join( $list[4] ) );
	$folders[]	= HtmlCollapsePanel::create( 'day-4', $table, $heading, NULL );
}
if( count( $list[5] ) ){
	$heading	= HtmlTag::create( 'div', getFutureDate( 5, $words ).getCount( $list, 5 ) );
	$table		= HtmlTag::create( 'table', $colgroup.$tableHeads.join( $list[5] ) );
	$folders[]	= HtmlCollapsePanel::create( 'day-5', $table, $heading, NULL );
}
if( count( $list[6] ) ){
	$heading	= HtmlTag::create( 'div', 'Zukunft '.getCount( $list, 6 ) );
	$table		= HtmlTag::create( 'table', $colgroup.$tableHeads.join( $list[6] ) );
	$folders[]	= HtmlCollapsePanel::create( 'day-6', $table, $heading, NULL );
}

return join( $folders );
