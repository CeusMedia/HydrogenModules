<?php
$days	= $session->get( 'filter_mission_days' );
if( !is_array( $days ) )
	$days	= array( 0 );

$day = $days[0];

class Helper{
	protected $day;
	protected $env;
	protected $list;
	protected $words;
	public function __construct( $env, $list, $words, $day ){
		$this->env		= $env;
		$this->list		= $list;
		$this->words	= $words;
		$this->day		= $day;
	}

	public function getFallbackDay(){
		foreach( $this->list as $day => $entries )
			if( $entries )
				return $day;
		return -1;
	}

	public function getNearestFallbackDay( $day ){
		$left	= $right	= $day;
		do{
			$left--;
			$right++;
			if( $left >= 0 && count( $this->list[$left] ) )
				return $left;
			if( $right < 7 && count( $this->list[$right] ) )
				return $right;
		}
		while( $left > 0 || $right < 6 );
		return -1;
	}

	public function renderDate( $daysInFuture = 0, $template = '%1$s, %2$s' ){
		$then	= time() + $daysInFuture * 24 * 60 * 60;
		$day	= isset( $this->words['days'] ) ? $this->words['days'][date( "w", $then )] : '';
		$date	= date( "j.n.", $then );
		return sprintf( $template, $day, $date );
	}

	public function renderDayButton( $day, $label ){
		$count		= isset(  $this->list[$day] ) ? count( $this->list[$day] ) : 0;
		$classes	= array( 'day' );
		if( $day < 3 )
			$classes[]	= 'important';
		if( !$count )
			$classes[]	= 'empty';
		$attributes	= array(
			'class'		=> join( ' ', $classes ),
			'disabled'	=> $count ? NULL : 'disabled',
			'type'		=> 'button',
			'onclick'	=> 'showDayTable('.$day.');',
		);
		return UI_HTML_Tag::create( 'button', $label, $attributes );
	}

	public function renderNumber( $list, $days ){
		$count	= count( $list[$days] );
		if( $count )
	#		return ' <small>('.$count.')</small>';
			return ' <div class="mission-number">'.$count.'</div>';
	}
}


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


$h	= new Helper( $env, $list, $words, $day );

if( !count( $list[$day] ) )
	$day	= $h->getNearestFallbackDay( $day );

$colgroup	= UI_HTML_Elements::ColumnGroup( "120px", "", "90px", "115px" );
$tableHeads	= UI_HTML_Elements::TableHeads( array(
	UI_HTML_Tag::create( 'div', 'Zustand', array( 'class' => 'sortable', 'data-column' => 'status' ) ),
	UI_HTML_Tag::create( 'div', 'Aufgabe', array( 'class' => 'sortable', 'data-column' => 'content' ) ),
	UI_HTML_Tag::create( 'div', 'Priorität', array( 'class' => 'sortable', 'data-column' => 'priority' ) ),
	UI_HTML_Tag::create( 'div', 'Aktion', array( 'class' => 'sortable', 'data-column' => NULL ) )
) );

$folders	= array();
$tables[-1]	= '<em>nix</em>';

if( 1 || count( $list[0] ) ){
	$label		= $h->renderNumber( $list, 0 ).'<b>Heute</b><br/>'.$h->renderDate( 0 );
	$buttons[]	= $h->renderDayButton( 0, $label );
	$tables[]	= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[0] ) );
}
if( 1 || count( $list[1] ) ){
	$label		= $h->renderNumber( $list, 1 ).'<b>Morgen</b><br/>'.$h->renderDate( 1 );
	$buttons[]	= $h->renderDayButton( 1, $label );
	$tables[]	= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[1] ) );
}
if( 1 || count( $list[2] ) ){
	$label		= $h->renderNumber( $list, 2 ).'<b>Übermorgen</b><br/>'.$h->renderDate( 2 );
	$buttons[]	= $h->renderDayButton( 2, $label );
	$tables[]	= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[2] ) );
}
if( 1 || count( $list[3] ) ){
	$label		= $h->renderNumber( $list, 3 ).$h->renderDate( 3, '%2$s<br/>%1$s' );
	$buttons[]	= $h->renderDayButton( 3, $label, count( $list[3] ), NULL );
	$tables[]	= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[3] ) );
}
if( 1 || count( $list[4] ) ){
	$label		= $h->renderNumber( $list, 4 ).$h->renderDate( 4, '%2$s<br/>%1$s' );
	$buttons[]	= $h->renderDayButton( 4, $label );
	$tables[]	= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[4] ) );
}
if( 1 || count( $list[5] ) ){
	$label		= $h->renderNumber( $list, 5 ).$h->renderDate( 5, '%2$s<br/>%1$s' );
	$buttons[]	= $h->renderDayButton( 5, $label );
	$tables[]	= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[5] ) );
}
if( 1 || count( $list[6] ) ){
	$label		= $h->renderNumber( $list, 6 ).'<span>Zukunft</span><br/>&nbsp;';
	$buttons[]	= $h->renderDayButton( 6, $label );
	$tables[]	= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[6] ) );
}

$panelList	= join( $folders );

foreach( $tables as $nr => $table )
	$tables[$nr]	= '<div class="table-day" id="table-'.$nr.'">'.$table.'</div>';

return '
<div>
	<div id="day-controls">'.join( $buttons ).'</div>
	<div id="day-lists">'.join( $tables ).'</div>
</div>
<script>
function showDayTable(day){
	$.ajax({
		url: "./work/mission/ajaxSelectDay/"+day+"/focus/only",
		dataType: "json",
		success: function(json){}
	});
	$("div.table-day").hide().filter("#table-"+day).show();
	$("#day-controls button").removeClass("active").eq(day).addClass("active");
}
$(document).ready(function(){
	showDayTable('.$day.');
});
</script>
';
return $panelList;

?>
