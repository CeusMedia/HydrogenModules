<?php

$w	= (object) $words['index'];
$panelFilter	= $view->loadTemplateFile( 'work/mission/index.filter.php' );

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
	$link		= UI_HTML_Elements::Link( './work/mission/edit/'.$mission->missionId, htmlentities( $mission->content, ENT_QUOTES, 'UTF-8' ) );
	$days		= ( strtotime( $mission->dayStart ) - $today ) / ( 24 * 60 * 60);
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
	if( $days < 0 ){
		$daysOverdue	= UI_HTML_Tag::create( 'div', abs( $days ), array( 'class' => "overdue" ) );
	}
	
	$line		= '<td><div style="padding: 2px;">'.$graph.'</div></td><td>'.$daysOverdue.$link.'</td><td>'.$priority.'</td><td class="actions">'.$buttonEdit.' | '.$buttonLeft.$buttonRight.'</td>';
	$list[$daysBound][]	= UI_HTML_Tag::create( 'tr', $line, array( 'class' => $class ) );
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

$colgroup	= UI_HTML_Elements::ColumnGroup( "13%", "60%", "14%", "13%" );
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

$panelList	= join( $folders );

$buttonAdd	= UI_HTML_Elements::LinkButton( './work/mission/add', $w->buttonAdd, 'button add' );

$panelAdd	= '<fieldset>
	<legend class="icon add">Neuer Eintrag</legend>
	'.UI_HTML_Elements::LinkButton( './work/mission/add?type=0', 'neue Aufgabe', 'button add task-add' ).'
	'.UI_HTML_Elements::LinkButton( './work/mission/add?type=1', 'neuer Termin', 'button add event-add' ).'
</fieldset>';

$panelExport	= '';
if( $filterStates != array( 4 ) ){
	$panelExport	= '<fieldset>
		<legend class="icon export">Export / Import</legend>
		<b>Export als:</b>&nbsp;
		'.UI_HTML_Elements::LinkButton( './work/mission/export/ical', 'ICS', 'button icon export ical' ).'
		'.UI_HTML_Elements::LinkButton( './work/mission/export', 'Archiv', 'button icon export archive' ).'
		<hr/>
		<form action="./work/mission/import" method="post" enctype="multipart/form-data">
			<b>Import aus:</b>&nbsp;
			<input type="text" name="import" id="input-import" class="m" readonly="readonly"/>
			<input type="file" name="serial" id="input-serial" accept="application/gzip"/>
		</form>
	</fieldset>';
}

$content	= '
<script>
function makeTableSortable(jq,options){
	var options = $.extend({order: null, direction: "ASC"},options);
	$("body").data("tablesort-options",options);
	jq.find("tr th div.sortable").each(function(){
		if($(this).data("column")){
			$(this).removeClass("sortable").parent().addClass("sortable");
			if($(this).data("column") == options.order){
				$(this).parent().addClass("ordered");
				$(this).parent().addClass("direction-"+options.direction.toLowerCase());
			}
			$(this).bind("click",function(){
				var head = $(this);
				var options = $("body").data("tablesort-options");
				var column = head.data("column");
				var direction = options.direction;
				if( options.order == column )
					direction = direction == "ASC" ? "DESC" : "ASC";
				var url = "./work/mission/filter/?order="+column+"&direction="+direction;
				document.location.href = url;
			});
		}
	});
}


$(document).ready(function(){
	makeTableSortable($("#layout-content table"),{
		url: "./work/mission/filter/",
		order: "'.$filterOrder.'",
		direction: "'.$filterDirection.'",
	});
});
</script>
<div class="column-left-20">
	'.$panelFilter.'
	'.$panelAdd.'
	'.$panelExport.'
	<a href="./work/mission/mail">MAIL</a>
</div>
<div class="column-left-80">
	<div id="mission-folders">
		'.$panelList.'
	</div>
<!--	'.$buttonAdd.'-->
</div>
<div class="column-clear"></div>';

return $content;
?>
