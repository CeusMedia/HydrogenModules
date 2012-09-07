<?php

$w	= (object) $words['index'];

$iconUp		= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_up.png', $words['filter-directions']['ASC'] );
$iconDown	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_down.png', $words['filter-directions']['DESC'] );
$iconRight	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_right.png', $words['list-actions']['moveRight'] );
$iconLeft	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_left.png', $words['list-actions']['moveLeft'] );
$iconEdit	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/pencil.png', $words['list-actions']['edit'] );
$iconRemove	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/bin_closed.png', $words['list-actions']['remove'] );

//  --  LIST  --  //
$list	= array(
	0 => array(),
	1 => array(),
	2 => array(),
	3 => array(),
	4 => array(),
	5 => array(),
	6 => array(),
	7 => array(),
);	
$indicator	= new UI_HTML_Indicator();
$disabled	= array();
$today		= strtotime( date( 'Y-m-d', time() ) );
foreach( $missions as $mission ){
	$label		= htmlentities( $mission->content, ENT_QUOTES, 'UTF-8' );
	$url		= './work/mission/edit/'.$mission->missionId;
	$class		= 'icon-label mission-type-'.$mission->type;
	$link		= UI_HTML_Elements::Link( $url, $label, array( 'class' => $class ) );
	$days		= ( strtotime( $mission->dayStart ) - $today ) / ( 24 * 60 * 60 );
	$daysBound	= max( min( $days , 6 ), 0 );
	$graph		= $indicator->build( $mission->status, 4 );
	$type		= $words['types'][$mission->type];
	$priority	= $words['priorities'][$mission->priority];
	$class		= 'row-priority priority-'.$mission->priority;
	$buttonEdit		= UI_HTML_Elements::LinkButton( './work/mission/edit/'.$mission->missionId, $iconEdit, 'tiny' );
	$buttonRemove	= UI_HTML_Elements::LinkButton( './work/mission/setStatus/'.$mission->missionId.'/'.urlencode( '-3' ), $iconRemove, 'tiny' );
	$buttonLeft		= UI_HTML_Elements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/?date='.urlencode( '-1' ), $iconLeft, 'tiny' );
	$buttonRight	= UI_HTML_Elements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/?date='.urlencode( '+1' ), $iconRight, 'tiny' );
	
	if( !$daysBound )
		$buttonLeft	= UI_HTML_Elements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/'.urlencode( '-1' ), $iconLeft, 'tiny', NULL, TRUE );

	$daysOverdue	= '';
	$days	= ( strtotime( max( $mission->dayStart, $mission->dayEnd ) ) - $today ) / ( 24 * 60 * 60);
	if( $days < 0 )
		$daysOverdue	= UI_HTML_Tag::create( 'div', abs( $days ), array( 'class' => "overdue" ) );
	
	$cells	= array(
		'<td><div style="padding: 4px 2px 2px 2px;">'.$graph.'</div></td>',
		'<td>'.$daysOverdue.$link.'</td>',
		'<td><small>'.$priority.'</small></td>',
		'<td class="actions">'.$buttonEdit.' | '.$buttonLeft.$buttonRight.'</td>',
	);
	$list[$daysBound][]	= UI_HTML_Tag::create( 'tr', join( $cells ), array( 'class' => $class ) );
}

function getFutureDate( $daysInFuture = 0, $words = NULL ){
	$then	= time() + $daysInFuture * 24 * 60 * 60;
	$day	= $words ? $words['days'][date( "w", $then )].', ' : '';
	return $day.date( "j.n.", $then );
}
function getCount( $list, $days ){
	$count	= count( $list[$days] );
	if( $count )
#		return ' <small>('.$count.')</small>';
		return ' <div class="mission-number">'.$count.'</div>';
}

$colgroup	= UI_HTML_Elements::ColumnGroup( "120px", "", "90px", "115px" );
$tableHeads	= UI_HTML_Elements::TableHeads( array(
	UI_HTML_Tag::create( 'div', 'Zustand', array( 'class' => 'sortable', 'data-column' => 'status' ) ),
	UI_HTML_Tag::create( 'div', 'Aufgabe', array( 'class' => 'sortable', 'data-column' => 'content' ) ),
	UI_HTML_Tag::create( 'div', 'Priorität', array( 'class' => 'sortable', 'data-column' => 'priority' ) ),
	UI_HTML_Tag::create( 'div', 'Aktion', array( 'class' => 'sortable', 'data-column' => NULL ) )
) );

$folders	= array();

if( count( $list[0] ) ){
	$heading	= UI_HTML_Tag::create( 'div', '<b>Heute</b>, '.getFutureDate( 0, $words ).getCount( $list, 0 ) );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[0] ) );
	$folders[]	= UI_HTML_CollapsePanel::create( 'day-0', $table, $heading, NULL );
}
if( count( $list[1] ) ){
	$heading	= UI_HTML_Tag::create( 'div', '<b>Morgen</b>, '.getFutureDate( 1, $words ).getCount( $list, 1 ) );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[1] ) );
	$folders[]	= UI_HTML_CollapsePanel::create( 'day-1', $table, $heading, NULL );
}
if( count( $list[2] ) ){
	$heading	= UI_HTML_Tag::create( 'div', '<b>Übermorgen</b>, '.getFutureDate( 2, $words ).getCount( $list, 2 ) );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[2] ) );
	$folders[]	= UI_HTML_CollapsePanel::create( 'day-2', $table, $heading, NULL );
}
if( count( $list[3] ) ){
	$heading	= UI_HTML_Tag::create( 'div', getFutureDate( 3, $words ).getCount( $list, 3 ) );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[3] ) );
	$folders[]	= UI_HTML_CollapsePanel::create( 'day-3', $table, $heading, NULL );
}
if( count( $list[4] ) ){
	$heading	= UI_HTML_Tag::create( 'div', getFutureDate( 4, $words ).getCount( $list, 4 ) );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[4] ) );
	$folders[]	= UI_HTML_CollapsePanel::create( 'day-4', $table, $heading, NULL );
}
if( count( $list[5] ) ){
	$heading	= UI_HTML_Tag::create( 'div', getFutureDate( 5, $words ).getCount( $list, 5 ) );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[5] ) );
	$folders[]	= UI_HTML_CollapsePanel::create( 'day-5', $table, $heading, NULL );
}
if( count( $list[6] ) ){
	$heading	= UI_HTML_Tag::create( 'div', 'Zukunft '.getCount( $list, 6 ) );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[6] ) );
	$folders[]	= UI_HTML_CollapsePanel::create( 'day-6', $table, $heading, NULL );
}

return join( $folders );
?>
