<?php
$w			= (object) $words['index'];

$h			= new View_Helper_MissionList( $env, $missions, $words );
$buttons	= $h->renderButtons();																	//  render day buttons
$lists		= $h->renderLists();																	//  render day lists

//if( !$h->countMissions( (int) $currentDay ) )
//	$currentDay	= $h->getNearestFallbackDay( (int) $currentDay );

return '
<div>
	<div id="day-controls">'.$buttons.'</div>
	<div id="day-lists">'.$lists.'</div>
</div>';
/*
<script type="text/javascript">
var missionShowDay = '.$currentDay.';
</script>
';*/
?>
