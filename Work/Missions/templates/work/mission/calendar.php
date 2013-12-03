<?php

$buttonSets		= array(
	array(
//		'<button type="button" id="work-mission-view-type-0" disabled="disabled" class="button icon list"><span>Listenansicht</span></button>',
//		'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="button icon calendar"><span>Monatsübersicht</span></button>'
		'<button type="button" id="work-mission-view-type-0" disabled="disabled" class="btn"><i class="icon-tasks"></i> Liste</button>',
		'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="btn"><i class="icon-calendar"></i> Monat</button>'
	),
	array(
		UI_HTML_Elements::LinkButton( './work/mission/add?type=0', 'Aufgabe', 'button add task-add' ),
		UI_HTML_Elements::LinkButton( './work/mission/add?type=1', 'Termin', 'button add event-add' )
	)
);

$helper		= new View_Helper_Work_Mission_Calendar( $env );
$calendar	= $helper->render( $userId, $year, $month );
$content	= '<div>'.$calendar.'</div>';

$script	= '
<script>
$(document).ready(function(){
	WorkMissionsCalendar.year = '.$year.';
	WorkMissionsCalendar.month = '.$month.';
	WorkMissionsCalendar.init();
	WorkMissions.init(1);
	$(".cmContextMenu").cmContextMenu({"timeSlideDown":150,"timeSlideUp":100}).show();
});
</script>';
$env->getPage()->addHead( $script );

$buttons	= array();
foreach( $buttonSets as $buttonSet )
	$buttons[]	= '<div class="btn-group">'.join( " ", $buttonSet ).'</div>'; $buttons	= '<div id="work-mission-buttons">'.join( " | ", $buttons ).'</div>';
return $buttons.$content;
?>
