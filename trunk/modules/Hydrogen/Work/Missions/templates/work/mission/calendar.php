<?php

$buttonSets		= array(
	array(
		'<button type="button" id="work-mission-view-type-0" disabled="disabled" class="button icon list"><span>Listenansicht</span></button>',
		'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="button icon calendar"><span>Monatsübersicht</span></button>'
	),
	array(
		UI_HTML_Elements::LinkButton( './work/mission/add?type=0', 'Aufgabe', 'button add task-add' ),
		UI_HTML_Elements::LinkButton( './work/mission/add?type=1', 'Termin', 'button add event-add' )
	),
	array(
		UI_HTML_Elements::LinkButton( './work/mission/export/ical', 'iCal-Export', 'button icon export ical' )
	)
);

$helper		= new View_Helper_MissionCalendar( $env );
$label		= $helper->renderLabel( $year, $month );
$calendar	= $helper->render( $userId, $year, $month );
$content	= '<div>'.$calendar.'</div>';

$buttonSets[]	= array(
	'<button type="button" onclick="WorkMissionsCalendar.setMonth(-1)">&laquo;</button>',
	'<button type="button" onclick="WorkMissionsCalendar.setMonth(0)">&Omicron;</button>',
	'<button type="button" onclick="WorkMissionsCalendar.setMonth(1)">&raquo;</button>',
	$label
);

$script	= '
<script>
$(document).ready(function(){
	WorkMissionsCalendar.year = '.$year.';
	WorkMissionsCalendar.month = '.$month.';
	WorkMissionsCalendar.init();
	WorkMissions.init();
});
</script>';
$env->getPage()->addHead( $script );

$buttons	= array();
foreach( $buttonSets as $buttonSet )
	$buttons[]	= join( " ", $buttonSet );
$buttons	= '<div id="work-mission-buttons">'.join( " | ", $buttons ).'</div><br/>';

return $buttons.$content;
?>
