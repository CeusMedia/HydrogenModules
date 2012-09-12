<?php
$w	= (object) $words['index'];
$h	= new View_Helper_MissionList( $env, $missions, $words );

//  --  DAY BUTTONS  --  //
$labels	= array(
	'<b>Heute</b><br/>'.$h->renderDate( 0 ),
	'<b>Morgen</b><br/>'.$h->renderDate( 1 ),
	'<b>Übermorgen</b><br/>'.$h->renderDate( 2 ),
	$h->renderDate( 3, '%2$s<br/>%1$s' ),
	$h->renderDate( 4, '%2$s<br/>%1$s' ),
	$h->renderDate( 5, '%2$s<br/>%1$s' ),
	'<span>Zukunft</span><br/>&nbsp;',
);

//  --  DAY TABLES  --  //
$colgroup	= UI_HTML_Elements::ColumnGroup( "120px", "", "90px", "115px" );
$tableHeads	= UI_HTML_Elements::TableHeads( array(
	UI_HTML_Tag::create( 'div', 'Zustand', array( 'class' => 'sortable', 'data-column' => 'status' ) ),
	UI_HTML_Tag::create( 'div', 'Aufgabe', array( 'class' => 'sortable', 'data-column' => 'content' ) ),
	UI_HTML_Tag::create( 'div', 'Priorität', array( 'class' => 'sortable', 'data-column' => 'priority' ) ),
	UI_HTML_Tag::create( 'div', 'Aktion', array( 'class' => 'sortable right', 'data-column' => NULL ) )
) );

$list	= array();
for( $i=0; $i<7; $i++ ){
	$rows		= UI_HTML_Tag::create( 'tbody', $h->renderRows( $i, TRUE, TRUE ) );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.$rows );
	$list[]	= '<div class="table-day" id="table-'.$i.'">'.$table.'</div>';
	$buttons[]	= $h->renderDayButton( $i, $h->renderNumber( $i ).$labels[$i] );
}

if( !$h->countMissions( (int) $currentDay ) )
	$currentDay	= $h->getNearestFallbackDay( (int) $currentDay );

return '
<div>
	<div id="day-controls">'.join( $buttons ).'</div>
	<div id="day-lists">'.join( $list ).'</div>
</div>
<script type="text/javascript">
var missionShowDay = '.$currentDay.';
</script>
';
?>